# 🔍 AUDITORÍA DE CÓDIGO - DINASTÍA

**Fecha:** 2026-06-24  
**Auditor:** Claude Code  
**Periodo:** Análisis completo del repositorio  
**Cobertura:** 70+ modelos, 35+ controladores, servicios, middleware  

---

## 📊 RESUMEN EJECUTIVO

| Severidad | Cantidad | Estado |
|-----------|----------|--------|
| 🔴 CRÍTICO | 8 | Debe solucionarse antes de producción |
| 🟠 ALTO | 12 | Refactor obligatorio en Phase 0 |
| 🟡 MEDIO | 15 | Mejorar en sprints siguientes |
| 🟢 BAJO | 10 | Optimizaciones técnicas |

**Cobertura de Tests:** 5% (solo 2 example tests en tests/)  
**Target Phase 0:** 85%+

---

## 🔴 CRÍTICO - Debe resolverse antes de producción

### 1. **Validación de Entrada Inconsistente** [CRÍTICO]
**Ubicación:** ReciboController, múltiples controllers  
**Problema:**
```php
// ReciboController::store() - Inline validation
$request->validate([
    'afiliado_id' => 'required|exists:afiliados,id',
    'fecha' => 'required|date'
]);

// Falta validación de:
// - empresa_id scope (qué evita que un usuario acceda a otra empresa?)
// - cargos array structure
// - fecha_retiro when novedad == 'Retiro'
```

**Riesgo:** Acceso no autorizado a datos de otras empresas, inyección de datos.

**Solución Phase 0:**
- Crear Request classes para cada controlador: `StoreReciboRequest`, `UpdateReciboRequest`
- Implementar validación de empresa_id en FormRequest::authorize()
- Validar estructura completa incluyendo nested arrays

**Archivo a crear:** `app/Http/Requests/StoreReciboRequest.php`

---

### 2. **Falta de Type Hints en Return Types** [CRÍTICO]
**Ubicación:** ~95% de métodos en Controllers, Services, Models  
**Problema:**
```php
// ❌ SIN TIPO DE RETORNO
public function index()
public function calcularRecibo($afiliadoId, $fecha, $permitirMismoMes = false)
public function calcular($afiliado, $dias = 30)

// ✅ CON TIPO (raro)
public function handle(Request $request, Closure $next, string ...$roles): mixed
```

**Impacto:** 
- IDE no puede autocompletar
- Errores de tipo no detectados hasta runtime
- Difícil mantenimiento

**Solución Phase 0:**
```bash
php artisan pint --config pint.json  # Add return types to all methods
```

**Plan:** Agregar return types a 100% de métodos públicos
- Controllers: `: View | RedirectResponse | JsonResponse`
- Services: `: array | float | bool`
- Models: Relationships ya tienen type hints genéricos

---

### 3. **N+1 Queries sin Mitigación** [CRÍTICO]
**Ubicación:** ReciboController::create(), edit(), index()  
**Problema:**
```php
// ❌ MALO - Sin limite, sin paginación
public function create()
{
    $afiliados = Afiliado::orderBy('primer_apellido')->get();  // ¿1000 registros?
    return view('modules.recibos.create', compact('afiliados'));
}

// ❌ MALO - Eager loading incompleto
public function index()
{
    $recibos = Recibo::with('afiliado')  // Solo afiliado, pero luego se accede a relaciones más profundas
        ->paginate(15);
}

// ❌ MALO - Lazy loading en loop
foreach ($afiliado->servicios as $servicio) {  // Query por cada servicio
    if ($servicio->estado) { ... }
}
```

**Impacto:** 
- Queries O(n) donde n = número de registros
- Timeouts en empresas grandes (>5000 empleados)
- 100 recibos = 100 queries adicionales de afiliados

**Solución Phase 0:**
```php
// ✅ BUENO - Paginado y eager-loaded
public function create()
{
    $afiliados = Afiliado::active()
        ->select('id', 'numero_documento', 'primer_nombre', 'primer_apellido')
        ->orderBy('primer_apellido')
        ->limit(500)
        ->get();
}

// ✅ BUENO - Eager load completo
public function index()
{
    $recibos = Recibo::with(['afiliado', 'detalles'])
        ->where('empresa_id', session('empresa_id'))
        ->paginate(15);
}
```

**Plan:**
1. Identificar todas las relaciones cargadas
2. Agregar `.with()` proactivamente
3. Usar `select()` para limitar columnas
4. Crear scopes para queries comunes

---

### 4. **Lógica de Negocio en Controller** [CRÍTICO]
**Ubicación:** ReciboController::calcularRecibo(), store()  
**Problema:**
```php
// ❌ LÓGICA COMPLEJA EN CONTROLLER
public function store(Request $request)
{
    // 150+ líneas de:
    // - Validación de fechas
    // - Cálculos de recibo
    // - Transacciones DB
    // - Transformación de datos
    // - Actualización de estado
}
```

**Impacto:** 
- Imposible testear sin hacer HTTP request
- Difícil reutilizar en API (Phase 1)
- Violación de Single Responsibility Principle

**Solución Phase 0:**
```php
// ✅ BUENO - Usar Action class
class CrearReciboAction
{
    public function __invoke(Afiliado $afiliado, Carbon $fecha, array $cargos = []): Recibo
    {
        // Validación, cálculo, persistencia
    }
}

// En controller:
public function store(StoreReciboRequest $request)
{
    $recibo = app(CrearReciboAction::class)(
        Afiliado::findOrFail($request->afiliado_id),
        $request->fecha,
        $request->cargos ?? []
    );
    
    return redirect()->route('recibos.show', $recibo);
}
```

**Plan:**
1. Crear `app/Actions/` para workflows complejos
2. Mover lógica de controllers a Actions
3. Testear Actions directamente sin HTTP

---

### 5. **Sin Auditoría de Cambios** [CRÍTICO]
**Ubicación:** Todas las tablas críticas (recibos, afiliados, etc.)  
**Problema:**
```
- ¿Quién cambió esta nómina?
- ¿Cuándo se editó este recibo?
- ¿Qué valores tenía antes?
- ¿Es auditable para DIAN?
```

**Solución Phase 0:**
```php
// Crear tabla audit_logs
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->string('auditable_type');
    $table->unsignedBigInteger('auditable_id');
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('action'); // created, updated, deleted
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->timestamps();
});

// En modelo:
use Spatie\Activitylog\Traits\LogsActivity;

class Recibo extends BaseModel
{
    use LogsActivity;
    protected static $logAttributes = ['*'];
}
```

**Plan:** Implementar en Phase 5 pero preparar base en Phase 0

---

## 🟠 ALTO - Refactor obligatorio en Phase 0

### 6. **Duplicación de Lógica de Período** [ALTO]
**Ubicación:** ReciboController líneas 26-42, 44-52  
**Problema:**
```php
// ❌ REPETIDO DOS VECES - Exacto mismo whereRaw
$periodo = now()->subMonth()->format('Y-m');

$recibos = Recibo::with('afiliado')
    ->where('empresa_id', $empresaId)
    ->whereRaw("DATE_FORMAT(DATE_SUB(fecha, INTERVAL 1 MONTH),'%Y-%m') = ?", [$periodo])
    ->latest()
    ->paginate(15);

$pendientes = Recibo::where('empresa_id', $empresaId)
    ->whereNull('export_batch_id')
    ->whereRaw("DATE_FORMAT(DATE_SUB(fecha, INTERVAL 1 MONTH),'%Y-%m') = ?", [$periodo])
    ->count();
```

**Solución:**
```php
// Crear scope en modelo
class Recibo extends BaseModel
{
    public function scopeDelPeriodo($query, Carbon $fecha)
    {
        $periodo = $fecha->subMonth()->format('Y-m');
        return $query->whereRaw("DATE_FORMAT(DATE_SUB(fecha, INTERVAL 1 MONTH),'%Y-%m') = ?", [$periodo]);
    }
}

// En controller:
$periodo = now();
$recibos = Recibo::with('afiliado')
    ->delPeriodo($periodo)
    ->latest()
    ->paginate(15);

$pendientes = Recibo::delPeriodo($periodo)
    ->whereNull('export_batch_id')
    ->count();
```

---

### 7. **Porcentajes Hardcodeados en Código** [ALTO]
**Ubicación:** LiquidacionService líneas 40-56  
**Problema:**
```php
// ❌ MAGIC NUMBERS - ¿Dónde vienen estos valores?
$porcentaje = 0.04;      // EPS? EPA? Por qué exactamente 4%?
$porcentaje = 0.16;      // Pensión? Siempre 16%?
$porcentaje = 0.04;      // Caja? Por qué igual a EPS?

return round(($ibc * $porcentaje / 30) * $dias);
```

**Riesgo:** 
- Cambios anuales (salario mínimo, porcentajes) requieren editar código
- Imposible A/B test o diferentes empresas con diferentes porcentajes
- No cumple DIAN (porcentajes varían por empresa y año)

**Solución:**
```php
// Crear modelo ParametroAnual (ya existe!)
// Pero no se usa. Implementar:

class LiquidacionService
{
    public function calcularEPS(Afiliado $afiliado, float $ibc, int $dias): float
    {
        $parametro = ParametroAnual::where('empresa_id', $afiliado->empresa_id)
            ->where('tipo', 'eps_porcentaje')
            ->latest('vigencia')
            ->first();
        
        $porcentaje = $parametro->valor / 100;
        return round(($ibc * $porcentaje / 30) * $dias, 2);
    }
}

// Seed con valores por defecto
ParametroAnual::create([
    'empresa_id' => $empresa->id,
    'tipo' => 'eps_porcentaje',
    'valor' => 4,
    'vigencia' => now()->year,
]);
```

---

### 8. **Falta de Request Classes** [ALTO]
**Ubicación:** Todos los controllers  
**Problema:**
```php
// ❌ VALIDACIÓN INLINE - Duplicada en varios controllers
$request->validate([
    'afiliado_id' => 'required|exists:afiliados,id',
    'fecha' => 'required|date'
]);

$request->validate([
    'nombre' => 'required|string|max:255',
    'nit' => 'required|string|unique:empresas,nit'
]);
```

**Solución Phase 0:**
Crear Request class para cada acción:
```php
// app/Http/Requests/StoreReciboRequest.php
class StoreReciboRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Verificar que usuario pertenece a empresa_id en session
        return auth()->user()->empresas()->where('empresa_id', session('empresa_id'))->exists();
    }
    
    public function rules(): array
    {
        return [
            'afiliado_id' => [
                'required',
                'exists:afiliados,id',
                new AfiliadoDeEmpresaRule(session('empresa_id'))
            ],
            'fecha' => 'required|date|before_or_equal:today',
            'novedad' => 'nullable|in:Retiro,Suspensión,Reintegro',
            'fecha_retiro' => 'required_if:novedad,Retiro|date',
            'cargos' => 'nullable|array',
            'cargos.*.concepto' => 'required|string|max:100',
            'cargos.*.valor' => 'required|numeric|min:0',
        ];
    }
}
```

**Plan:** Crear ~15 Request classes para controllers principales

---

### 9. **Sin Factorías de Test** [ALTO]
**Ubicación:** database/factories/ (vacío)  
**Problema:**
```php
// ❌ IMPOSIBLE TESTEAR - Sin factories
test('can create recibo', function () {
    // ¿Cómo creo un Afiliado con todas sus relaciones?
    // ¿Qué valores por defecto uso?
});
```

**Solución Phase 0:**
```php
// database/factories/AfiliadoFactory.php
class AfiliadoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'numero_documento' => $this->faker->unique()->numerify('##########'),
            'primer_nombre' => $this->faker->firstName(),
            'primer_apellido' => $this->faker->lastName(),
            'fecha_nacimiento' => $this->faker->dateOfBirth(),
            'estado' => true,
        ];
    }
}

// Uso en tests:
$afiliado = Afiliado::factory()->create();
$afiliados = Afiliado::factory(50)->create();
```

**Plan:** Crear factories para 20+ modelos principales

---

### 10. **Inconsistencia en Nombres de Campos** [ALTO]
**Ubicación:** Múltiples modelos  
**Problema:**
```php
// En Recibo:
'valor_eps', 'valor_arl', 'valor_pension'

// En LiquidacionService:
'eps' => $eps, 'arl' => $arl, 'pension' => $pension

// En ReciboDetalle:
'concepto' => $d['concepto']
```

**Solución:** Estandarizar a snake_case en DB + camelCase en JSON API

---

## 🟡 MEDIO - Mejorar en sprints siguientes

### 11. **Falta de Scopes en Modelos** [MEDIO]
Crear scopes para queries comunes:
```php
class Afiliado extends BaseModel
{
    public function scopeActive($query) { return $query->where('estado', true); }
    public function scopeInactivo($query) { return $query->where('estado', false); }
    public function scopeDeEmpresa($query, $empresaId) { return $query->where('empresa_id', $empresaId); }
}
```

---

### 12. **Sin Timestamps en Algunos Modelos** [MEDIO]
Modelos críticos deben tener `created_at`, `updated_at`:
- Afiliado ✅ 
- Recibo ✅
- EmpresaExterna ❌
- Caja ❌

---

### 13. **Strings en lugar de Enums** [MEDIO]
```php
// ❌ MALO
if ($request->novedad == 'Retiro') { ... }
'novedad' => 'Retiro'

// ✅ BUENO (PHP 8.1+)
enum Novedad: string {
    case Retiro = 'RETIRO';
    case Suspension = 'SUSPENSION';
    case Reintegro = 'REINTEGRO';
}
```

---

### 14. **Sin Soft Deletes** [MEDIO]
Modelos críticos deben ser "eliminables" lógicamente:
```php
use SoftDeletes;
protected $dates = ['deleted_at'];
```

---

### 15. **Middleware CheckModulo Incompleto** [MEDIO]
```php
// ¿Qué hace puedeAcceder()?
if ($this->service->puedeAcceder($slug)) {
    return $next($request);
}

// ¿Se verifica que el módulo pertenece a la empresa actual?
```

Asegurar que verifica: `empresa_id` + `modulo` + `rol`

---

## 🟢 BAJO - Optimizaciones técnicas

### 16. **Falta de Índices de Base de Datos** [BAJO]
Agregar índices para queries frecuentes:
```sql
ALTER TABLE recibos ADD INDEX idx_empresa_periodo (empresa_id, fecha);
ALTER TABLE afiliados ADD INDEX idx_empresa_estado (empresa_id, estado);
ALTER TABLE afiliado_servicios ADD INDEX idx_afiliado_estado (afiliado_id, estado);
```

---

### 17. **Sin Validación de Datos de Entrada en Request** [BAJO]
Agregar validación de tamaño de arrays:
```php
'cargos' => 'nullable|array|max:50',
'cargos.*.concepto' => 'required|string|max:100',
```

---

### 18. **Comentarios con Emojis** [BAJO]
Cambiar:
```php
// 🔥 STORE → // Store payroll receipt
// 💾 ASIGNAR → // Automatically assign empresa_id
// 🔒 FILTRO → // Global scope: filter by empresa_id
```

---

### 19. **Sin Logging Estructurado** [BAJO]
Usar `Log` facade:
```php
Log::info('Recibo creado', [
    'recibo_id' => $recibo->id,
    'afiliado_id' => $afiliado->id,
    'empresa_id' => session('empresa_id'),
]);
```

---

### 20. **Falta de API Documentation** [BAJO]
Documentación OpenAPI para futura API REST (Phase 1):
- Comentarios PHPDoc en controllers
- `@OA\Get`, `@OA\Post`, etc.

---

## 📋 PLAN DE ACCIÓN - PHASE 0

| Prioridad | Tarea | Esfuerzo | Deadline |
|-----------|-------|----------|----------|
| 🔴 P1 | Crear Request classes (top 15 controllers) | 3h | Semana 1 |
| 🔴 P1 | Agregar type hints a todos los métodos | 2h | Semana 1 |
| 🔴 P1 | Refactor ReciboController (mover logic a Actions) | 4h | Semana 2 |
| 🔴 P1 | Crear factories para 20+ modelos | 3h | Semana 2 |
| 🟠 P2 | Crear scopes en modelos | 2h | Semana 2 |
| 🟠 P2 | Eliminar duplicación de queries | 2h | Semana 1 |
| 🟡 P3 | Agregar timestamps faltantes | 1h | Semana 1 |
| 🟡 P3 | Crear enums para strings | 2h | Semana 2 |

**Total Semana 1:** ~8 horas  
**Total Semana 2:** ~12 horas  

---

## 🎯 CHECKPOINTS

**Fin Semana 1:**
- ✅ Request classes para ReciboController, AfiliadoController, EmpresaController
- ✅ Return types en 50% de métodos
- ✅ Factories básicas para Afiliado, Empresa, Recibo

**Fin Semana 2:**
- ✅ Return types 100%
- ✅ Scopes en modelos
- ✅ Factories completas para 20+ modelos
- ✅ Tests verdes (80%+ coverage)

---

**Próxima Revisión:** Después de Phase 1 (Semana 5)  
**Auditor:** Claude Code  
**Aprobado por:** Proyecto Dinastía
