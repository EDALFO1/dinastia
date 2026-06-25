# 📝 Convenciones de Código - Dinastía

**Versión:** 1.0  
**Última actualización:** 2026-06-24  
**Basado en:** PSR-12 + Laravel standards  

Este documento define los estándares de código para mantener consistencia y calidad en Dinastía.

---

## 📋 Tabla de Contenidos

1. [Estructura de Archivos](#estructura-de-archivos)
2. [PHP General](#php-general)
3. [Modelos Eloquent](#modelos-eloquent)
4. [Controllers](#controllers)
5. [Services y Actions](#services-y-actions)
6. [Validación](#validación)
7. [Blade Templates](#blade-templates)
8. [Tests](#tests)

---

## 📁 Estructura de Archivos

### Convención de Carpetas

```
app/
├── Models/                  # Modelos Eloquent
├── Http/
│   ├── Controllers/         # Controllers (web + admin)
│   ├── Requests/            # Form Request validation
│   ├── Resources/           # API Resources (Phase 1)
│   ├── Middleware/          # Middleware
│   └── Responses/           # Response classes (opcional)
├── Services/                # Lógica de negocio
├── Actions/                 # Actions workflow (Phase 0+)
├── Helpers/                 # Helper functions
├── Scopes/                  # Custom query scopes
├── Traits/                  # Reusable traits
├── Events/                  # Event classes (opcional)
├── Listeners/               # Event listeners (opcional)
├── Jobs/                    # Queue jobs (opcional)
├── Mail/                    # Mailables (opcional)
├── Exports/                 # Excel exports
├── Imports/                 # Excel imports
├── View/
│   └── Composers/           # View composers
└── Providers/               # Service providers

database/
├── migrations/              # Schema migrations
├── factories/               # Model factories
└── seeders/                 # Database seeders

resources/
├── views/                   # Blade templates
│   └── modules/             # Feature modules
│       ├── afiliados/
│       ├── recibos/
│       └── ...
├── css/                     # Tailwind CSS
└── js/                      # Alpine.js scripts

tests/
├── Feature/                 # Feature tests
├── Unit/                    # Unit tests
└── TestCase.php             # Base test class

routes/
├── web.php                  # Web routes (default)
└── api.php                  # API routes (Phase 1)
```

### Convención de Nombres de Archivo

```
Models/         Afiliado.php, Recibo.php             (PascalCase singular)
Controllers/    AfiliadoController.php                (PascalCase + Controller)
Requests/       StoreAfiliadoRequest.php             (PascalCase + Request)
Services/       LiquidacionService.php               (PascalCase + Service)
Actions/        CrearReciboAction.php                (PascalCase + Action)
Tests/          AfiliadoTest.php                     (PascalCase + Test)
Traits/         HasCompanyScope.php                  (PascalCase + Trait)
Exports/        AfiliadosExport.php                  (PascalCase + Export)
Imports/        AfiliadosImport.php                  (PascalCase + Import)
```

---

## 🐍 PHP General

### Namespace y Imports

```php
<?php

namespace App\Http\Controllers;

// Agrupar por categoría
use App\Models\Afiliado;
use App\Models\Empresa;
use App\Services\LiquidacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Order: alphabetical dentro de cada grupo
```

### Type Hints

```php
// ✅ OBLIGATORIO - Parámetros y return types
public function index(Request $request): View
{
    // ...
}

public function calcular(Afiliado $afiliado, int $dias = 30): array
{
    // ...
}

// ✅ Nullable types
public function findByDocument(?string $document): ?Afiliado
{
    // ...
}

// ✅ Union types (PHP 8+)
public function process(Afiliado | EmpresaLaboral $entity): bool
{
    // ...
}

// ❌ NO dejar sin type hints
public function index()  // ❌ Mal - sin return type
public function store($request)  // ❌ Mal - sin type hints
```

### Métodos Estáticos vs. Instancia

```php
// ✅ USO CORRECTO DE ESTÁTICOS
// Para operaciones que no requieren estado
public static function crearDesdeImport(array $data): Afiliado
{
    return self::create($data);
}

// ✅ Métodos de instancia para lógica que requiere estado
public function calcularPromedio(): float
{
    return $this->salarios->avg('monto');
}

// ❌ NO abusar de estáticos
class Utils {  // ❌ Anti-pattern
    public static function foo() { }
    public static function bar() { }
}
```

### Excepciones y Errores

```php
// ✅ BUENO - Usar excepciones específicas
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

public function getOrFail(int $id): Afiliado
{
    return Afiliado::findOrFail($id);  // Throws ModelNotFoundException
}

public function validateDate(string $date): Carbon
{
    if (!Carbon::canBeCreatedFromFormat($date, 'Y-m-d')) {
        throw new InvalidArgumentException("Invalid date format: $date");
    }
    
    return Carbon::createFromFormat('Y-m-d', $date);
}

// ❌ MALO - Exceptions genéricas
throw new Exception('Error');

// ❌ MALO - No manejar excepciones
try {
    $recibo = Recibo::findOrFail($id);
} catch (Exception $e) {
    // Silencio
}
```

### Variables y Constantes

```php
// ✅ BUENOS NOMBRES - Descriptivos
private const TAX_RATE_EPS = 0.04;
private const MAX_RETRIES = 3;
private const RETRY_DELAY_SECONDS = 5;

private $totalAfiliadosActivos = 0;
private $empresaId = null;

foreach ($afiliados as $afiliado) {
    // ...
}

// ❌ MALOS NOMBRES - Ambiguos
const TAX = 0.04;  // ¿Qué impuesto?
const MAX = 3;  // ¿Máximo de qué?

$d = 0;  // ¿Qué es d?
$temp = $empresa->id;  // ¿Temporal para qué?

foreach ($items as $item) {
    $item = $item + 1;  // Reasignar variable de loop
}
```

---

## 📊 Modelos Eloquent

### Estructura Base

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Afiliado extends BaseModel  // Extender BaseModel para multi-tenant
{
    protected $table = 'afiliados';
    
    // 1. Fillable (permitidos para mass assignment)
    protected $fillable = [
        'empresa_id',
        'numero_documento',
        'primer_nombre',
        'primer_apellido',
        'estado',
    ];
    
    // 2. Casts
    protected $casts = [
        'fecha_nacimiento' => 'date',
        'estado' => 'boolean',
        'created_at' => 'datetime',
    ];
    
    // 3. Accesores (transformar al leer)
    protected $appends = ['nombre_completo'];
    
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->primer_nombre} {$this->primer_apellido}";
    }
    
    // 4. Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
    
    public function recibos()
    {
        return $this->hasMany(Recibo::class);
    }
    
    // 5. Scopes
    public function scopeActive($query)
    {
        return $query->where('estado', true);
    }
    
    public function scopeInactivo($query)
    {
        return $query->where('estado', false);
    }
    
    // 6. Métodos custom
    public function estaActivo(): bool
    {
        return (bool) $this->estado;
    }
}
```

### Relaciones

```php
// ✅ BUENO - Nombres descriptivos en relaciones
public function empresa()  // singular: belongsTo
{
    return $this->belongsTo(Empresa::class);
}

public function recibos()  // plural: hasMany
{
    return $this->hasMany(Recibo::class);
}

public function servicios()  // plural: belongsToMany
{
    return $this->belongsToMany(Servicio::class)
        ->withPivot('estado')
        ->withTimestamps();
}

// ❌ MALO
public function empresa_relacion()
public function getRecibos()  // No es relación Eloquent
public function Recibos()  // PascalCase en relación
```

### Scopes

```php
// ✅ BUENO - Reutilizable, chainable
class Recibo extends BaseModel
{
    public function scopeDelMes($query, Carbon $fecha)
    {
        return $query->whereMonth('fecha', $fecha->month)
            ->whereYear('fecha', $fecha->year);
    }
    
    public function scopePendienteExportar($query)
    {
        return $query->whereNull('export_batch_id');
    }
    
    public function scopeConDetalles($query)
    {
        return $query->with('detalles');
    }
}

// Uso:
Recibo::delMes(now())
    ->pendienteExportar()
    ->conDetalles()
    ->get();

// ❌ MALO - Scope sin retorno, sin chainable
public function scopeActive($query)
{
    $query->where('estado', true);  // Sin return
}
```

### Validación en Modelos

```php
// ✅ Validation rules en modelo (opcional, para reutilizar)
class Afiliado extends BaseModel
{
    public static function rules(?int $id = null): array
    {
        return [
            'numero_documento' => [
                'required',
                'string',
                'unique:afiliados,numero_documento,' . $id,
            ],
            'primer_nombre' => 'required|string|max:100',
            'estado' => 'nullable|boolean',
        ];
    }
}

// En Request:
public function rules(): array
{
    return Afiliado::rules($this->afiliado?->id);
}
```

---

## 🎮 Controllers

### Estructura RESTful

```php
<?php

namespace App\Http\Controllers;

use App\Models\Afiliado;
use App\Http\Requests\StoreAfiliadoRequest;
use App\Http\Requests\UpdateAfiliadoRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AfiliadoController extends Controller
{
    // index: mostrar lista paginada
    public function index(): View
    {
        $afiliados = Afiliado::active()
            ->with('empresa')
            ->paginate(15);
        
        return view('afiliados.index', compact('afiliados'));
    }
    
    // create: mostrar formulario crear
    public function create(): View
    {
        return view('afiliados.create');
    }
    
    // store: procesar formulario crear
    public function store(StoreAfiliadoRequest $request): RedirectResponse
    {
        $afiliado = Afiliado::create($request->validated());
        
        return redirect()
            ->route('afiliados.show', $afiliado)
            ->with('success', 'Afiliado creado exitosamente.');
    }
    
    // show: mostrar detalle
    public function show(Afiliado $afiliado): View
    {
        return view('afiliados.show', compact('afiliado'));
    }
    
    // edit: mostrar formulario editar
    public function edit(Afiliado $afiliado): View
    {
        return view('afiliados.edit', compact('afiliado'));
    }
    
    // update: procesar formulario editar
    public function update(UpdateAfiliadoRequest $request, Afiliado $afiliado): RedirectResponse
    {
        $afiliado->update($request->validated());
        
        return redirect()
            ->route('afiliados.show', $afiliado)
            ->with('success', 'Afiliado actualizado exitosamente.');
    }
    
    // destroy: eliminar
    public function destroy(Afiliado $afiliado): RedirectResponse
    {
        $afiliado->delete();
        
        return redirect()
            ->route('afiliados.index')
            ->with('success', 'Afiliado eliminado exitosamente.');
    }
}
```

### Inyección de Dependencias

```php
// ✅ BUENO - Inyectar en constructor para servicios
class ReciboController extends Controller
{
    public function __construct(
        private LiquidacionService $liquidacionService,
    ) {}
    
    public function store(StoreReciboRequest $request): RedirectResponse
    {
        $recibo = ($this->liquidacionService)
            ->calcular($afiliado, $dias);
        
        // ...
    }
}

// ✅ Route model binding para modelos
public function show(Afiliado $afiliado): View  // Automático por route
{
    return view('afiliados.show', compact('afiliado'));
}
```

---

## ⚙️ Services y Actions

### Services (Lógica reutilizable)

```php
<?php

namespace App\Services;

use App\Models\Afiliado;
use App\Models\ParametroAnual;

class LiquidacionService
{
    public function calcular(Afiliado $afiliado, int $dias = 30): array
    {
        $ibc = $this->obtenerIBC($afiliado);
        
        return [
            'ibc' => $ibc,
            'eps' => $this->calcularEPS($ibc, $dias),
            'arl' => $this->calcularARL($afiliado, $ibc, $dias),
            'pension' => $this->calcularPension($ibc, $dias),
            'caja' => $this->calcularCaja($ibc, $dias),
        ];
    }
    
    private function obtenerIBC(Afiliado $afiliado): float
    {
        return (float) $afiliado->ibc;
    }
    
    private function calcularEPS(float $ibc, int $dias): float
    {
        $parametro = $this->obtenerParametro('eps_porcentaje');
        $porcentaje = $parametro / 100;
        
        return round(($ibc * $porcentaje / 30) * $dias, 2);
    }
    
    private function obtenerParametro(string $tipo): float
    {
        return ParametroAnual::where('tipo', $tipo)
            ->latest('vigencia')
            ->value('valor') ?? 0;
    }
}
```

### Actions (Workflows complejos)

```php
<?php

namespace App\Actions;

use App\Models\Afiliado;
use App\Models\Recibo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CrearReciboAction
{
    public function __invoke(
        Afiliado $afiliado,
        Carbon $fecha,
        array $cargos = [],
    ): Recibo {
        return DB::transaction(function () use ($afiliado, $fecha, $cargos) {
            // Validar
            $this->validar($afiliado, $fecha);
            
            // Calcular
            $datos = app(LiquidacionService::class)
                ->calcular($afiliado, 30);
            
            // Crear recibo
            $recibo = $afiliado->recibos()->create([
                'fecha' => $fecha,
                'ibc' => $datos['ibc'],
                'valor_eps' => $datos['eps'],
                // ... más campos
            ]);
            
            // Crear detalles
            foreach ($datos['detalles'] as $detalle) {
                $recibo->detalles()->create($detalle);
            }
            
            return $recibo;
        });
    }
    
    private function validar(Afiliado $afiliado, Carbon $fecha): void
    {
        // Lógica de validación
    }
}

// Uso en Controller:
$recibo = app(CrearReciboAction::class)($afiliado, $fecha);
```

---

## ✔️ Validación

### Form Requests

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAfiliadoRequest extends FormRequest
{
    // Autorización: ¿puede el usuario hacer esta acción?
    public function authorize(): bool
    {
        return auth()->user()
            ->empresas()
            ->where('id', session('empresa_id'))
            ->exists();
    }
    
    // Reglas de validación
    public function rules(): array
    {
        return [
            'numero_documento' => [
                'required',
                'string',
                'unique:afiliados,numero_documento',
                'regex:/^\d{6,12}$/',  // 6-12 dígitos
            ],
            'primer_nombre' => 'required|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date|before:today',
            'email' => 'nullable|email|unique:afiliados,email',
            'estado' => 'nullable|boolean',
        ];
    }
    
    // Mensajes personalizados (opcional)
    public function messages(): array
    {
        return [
            'numero_documento.regex' => 'El documento debe tener 6-12 dígitos.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento no puede ser futura.',
        ];
    }
}
```

---

## 🎨 Blade Templates

### Estructura y Convenciones

```blade
<!-- ✅ BUENO - Semantics, indentation, comments -->
<div class="space-y-6">
    {{-- Encabezado --}}
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold">Afiliados</h1>
        <a href="{{ route('afiliados.create') }}" class="btn btn-primary">
            Nuevo Afiliado
        </a>
    </div>

    {{-- Listado --}}
    @if ($afiliados->count())
        <div class="grid grid-cols-1 gap-4">
            @foreach ($afiliados as $afiliado)
                <div class="card">
                    <h3 class="font-bold">{{ $afiliado->nombre_completo }}</h3>
                    <p class="text-gray-600">{{ $afiliado->numero_documento }}</p>
                    <a href="{{ route('afiliados.edit', $afiliado) }}" class="link">Editar</a>
                </div>
            @endforeach
        </div>
        
        {{-- Paginación --}}
        {{ $afiliados->links() }}
    @else
        <p class="text-gray-500">No hay afiliados registrados.</p>
    @endif
</div>
```

### Helpers Útiles

```blade
{{-- Formatear valores --}}
{{ $recibo->total }}  {{-- Sin formato --}}
{{ number_format($recibo->total, 2) }}  {{-- Con 2 decimales --}}
$ {{ number_format($recibo->total, 0, ',', '.') }}  {{-- Moneda colombiana --}}

{{-- Fechas --}}
{{ $afiliado->created_at->format('d/m/Y') }}
{{ $afiliado->created_at->diffForHumans() }}  {{-- "hace 2 horas" --}}

{{-- Verificar estado --}}
@if ($afiliado->estaActivo())
    <span class="badge badge-success">Activo</span>
@else
    <span class="badge badge-danger">Inactivo</span>
@endif

{{-- Componentes personalizados --}}
<x-alerts.success :message="$message" />
<x-buttons.primary href="{{ route('afiliados.create') }}">Crear</x-buttons.primary>
```

---

## 🧪 Tests

### Estructura de Tests

```php
<?php

namespace Tests\Feature;

use App\Models\Afiliado;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AfiliadoTest extends TestCase
{
    use RefreshDatabase;  // Reset BD entre tests
    
    // ✅ Nombres descriptivos: test de qué behavior
    test('puede crear afiliado con datos válidos', function () {
        // Arrange: preparar datos
        $empresa = Empresa::factory()->create();
        $this->actingAs($empresa->usuarios->first());
        session(['empresa_id' => $empresa->id]);
        
        // Act: ejecutar acción
        $response = $this->post('/afiliados', [
            'numero_documento' => '123456789',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
        ]);
        
        // Assert: verificar resultado
        $response->assertRedirect();
        $this->assertDatabaseHas('afiliados', [
            'numero_documento' => '123456789',
            'empresa_id' => $empresa->id,
        ]);
    });
    
    test('no permite documento duplicado en misma empresa', function () {
        $empresa = Empresa::factory()->create();
        Afiliado::factory()->create([
            'empresa_id' => $empresa->id,
            'numero_documento' => '123456789',
        ]);
        
        $this->actingAs($empresa->usuarios->first());
        session(['empresa_id' => $empresa->id]);
        
        $response = $this->post('/afiliados', [
            'numero_documento' => '123456789',
            'primer_nombre' => 'Juan',
        ]);
        
        $response->assertSessionHasErrors('numero_documento');
    });
}
```

---

## 🔒 Seguridad

### Validación de Input

```php
// ✅ SIEMPRE validar entrada de usuario
$request->validate([
    'nombre' => 'required|string|max:255',
    'email' => 'required|email|unique:users',
    'monto' => 'required|numeric|min:0|max:999999999',
]);

// ❌ NUNCA confiar en input del usuario
$id = $request->id;  // ¿Es número? ¿Existe?
$sql = "SELECT * FROM afiliados WHERE id = " . $request->id;  // SQL injection!

// ✅ Usar Eloquent y validación
$afiliado = Afiliado::findOrFail($request->afiliado_id);
```

### Autorización

```php
// ✅ Verificar en FormRequest::authorize()
public function authorize(): bool
{
    return auth()->user()
        ->empresas()
        ->where('id', session('empresa_id'))
        ->exists();
}

// ✅ Model binding automático + scope
public function show(Afiliado $afiliado)  // Automáticamente scoped por empresa
{
    return view('afiliados.show', compact('afiliado'));
}

// ❌ NO confiar en IDs de URL sin verificar
$afiliado = Afiliado::find($request->afiliado_id);  // ¿Qué pasa si no es de esta empresa?
```

---

## 📚 Checklist Final

Al escribir código nuevo:

- [ ] Nombres descriptivos (variables, funciones, clases)
- [ ] Type hints en parámetros y return
- [ ] No dejar código comentado
- [ ] Tests para feature nueva
- [ ] Ejecutar `composer test`
- [ ] Ejecutar `php artisan pint`
- [ ] Documentación actualizada (CLAUDE.md, etc)
- [ ] Commit message descriptivo
- [ ] Sin magic numbers (usar constantes)
- [ ] Sin duplicación de código (usar scopes, helpers)

---

**Última revisión:** 2026-06-24  
**Responsable:** Team Dinastía
