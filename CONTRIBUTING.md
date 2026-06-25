# 🤝 Guía de Contribución - Dinastía

**Versión:** 1.0  
**Última actualización:** 2026-06-24  

Gracias por considerar contribuir a Dinastía. Este documento te guía en cómo configurar tu entorno, trabajar en features, y hacer que tu código esté listo para merge.

---

## 📋 Tabla de Contenidos

1. [Setup Local](#setup-local)
2. [Flujo de Desarrollo](#flujo-de-desarrollo)
3. [Git Workflow](#git-workflow)
4. [Estándares de Código](#estándares-de-código)
5. [Testing](#testing)
6. [Deployment](#deployment)

---

## 🚀 Setup Local

### Requisitos Previos

```bash
# Verificar versiones requeridas
php -v          # PHP 8.3+
composer -v     # Composer 2.0+
node -v         # Node 18+
npm -v          # npm 9+
mysql --version # MySQL 8.0+
```

### Instalación Inicial

```bash
# 1. Clonar repositorio
git clone https://github.com/EDALFO1/dinastia.git
cd dinastia

# 2. Instalar dependencias y configurar
composer setup

# 3. Generar key (si no fue generado en setup)
php artisan key:generate

# 4. Verificar setup
php artisan tinker
>>> Afiliado::count()  # Debe retornar 0 (sin datos de test aún)
```

### Verificar que Todo Funciona

```bash
# Terminal 1: Dev server + assets + logs
composer dev

# Terminal 2 (nueva ventana): Ejecutar tests
composer test

# Abrir navegador
http://localhost:8000
```

---

## 🔄 Flujo de Desarrollo

### Día de Desarrollo Típico

```bash
# 1. Actualizar main
git checkout main
git pull origin main

# 2. Crear rama feature
git checkout -b feature/mi-feature
# Convención: feature/*, bugfix/*, refactor/*, docs/*

# 3. Desarrollar (en ciclos de ~2 horas)
# - Escribir feature + tests
# - Ejecutar: composer test
# - Ejecutar: php artisan pint
# - Commit con mensaje descriptivo

# 4. Hacer push
git push origin feature/mi-feature

# 5. Abrir Pull Request en GitHub
# - Descripción clara de cambios
# - Link a issue (si aplica)
# - Tests pasando: ✅

# 6. Code review + merge
# Después de aprobación → Merge to main
```

### Micro-Sprint de 2 Horas

```
30 min - PLANIFICACIÓN
  ├─ ¿Qué voy a implementar?
  ├─ ¿Dónde va el código?
  └─ ¿Qué tests necesito?

90 min - DESARROLLO
  ├─ Escribir código
  ├─ Escribir tests
  ├─ Ejecutar: composer test
  └─ Commit

30 min - REVIEW
  ├─ Ejecutar: php artisan pint
  ├─ Revisar código (Clean Code)
  ├─ Documentar cambios
  └─ Descansar 10 min
```

---

## 📦 Git Workflow

### Convención de Ramas

```
main                    # Rama principal, siempre deployable
├── feature/auth-api    # Nueva feature
├── bugfix/n1-queries   # Bug fix
├── refactor/models     # Refactor de código
└── docs/api            # Documentación
```

**Regla:** Nunca commitear directamente a `main`. Todo por Pull Request.

### Convención de Commits

```
# Formato
<tipo>: <descripción corta>

<descripción detallada (opcional)>

<footer opcional>

# Tipos
feat:      Nueva feature
fix:       Bug fix
refactor:  Refactor sin cambio de funcionalidad
test:      Agregar o modificar tests
docs:      Cambios de documentación
style:     Cambios de formatting (pint, etc)
chore:     Cambios de configuración

# Ejemplos
✅ feat: agregar validación de DIAN en invoices
✅ fix: corregir cálculo de arl en liquidación
❌ fix: arreglar cosa
❌ Updated file.php
```

### Creando un Pull Request

**Titulo:**
```
[FASE 0] feat: agregar type hints a controllers
```

**Descripción:**
```markdown
## Descripción
Agrega type hints a todos los métodos en controllers para mejorar calidad de código.

## Cambios
- ReciboController: 10 métodos con return types
- AfiliadoController: 8 métodos con return types
- EmpresaController: 6 métodos con return types

## Testing
- [x] Tests pasando
- [x] Coverage > 80%
- [x] Sin warnings de Pint

## Relacionado
Cierra #123
```

---

## 💻 Estándares de Código

### PHP Coding Style

Seguimos **PSR-12** y convenciones de Laravel. Usar `php artisan pint` para auto-formatear:

```bash
# Verificar solo (no cambiar)
php artisan pint --test

# Formatear automáticamente
php artisan pint
```

**Estilo de Código:**

```php
<?php
// ✅ BUENO - Clear namespace y imports
namespace App\Http\Controllers;

use App\Models\Afiliado;
use Illuminate\Http\Request;

class AfiliadoController extends Controller
{
    // ✅ BUENO - Return types explícitos
    public function index(): View
    {
        $afiliados = Afiliado::with('empresa')
            ->paginate();
        
        return view('afiliados.index', compact('afiliados'));
    }

    // ✅ BUENO - Method documentation only when WHY is non-obvious
    public function store(StoreAfiliadoRequest $request): RedirectResponse
    {
        $afiliado = Afiliado::create($request->validated());
        
        return redirect()
            ->route('afiliados.show', $afiliado)
            ->with('success', 'Afiliado creado exitosamente.');
    }
}
```

**Reglas de Estilo:**

| Aspecto | Regla | Ejemplo |
|---------|-------|---------|
| **Indentación** | 4 espacios (no tabs) | - |
| **Línea máxima** | 120 caracteres | Quebrar si > 120 |
| **Imports** | Al inicio, alphabético | `use App\Models\Afiliado;` |
| **Type hints** | Obligatorio en parámetros y retorno | `public function foo(string $name): bool` |
| **Constantes** | UPPER_SNAKE_CASE | `const MAX_RETRIES = 3;` |
| **Propiedades privadas** | snake_case | `private $maxRetries;` |
| **Métodos** | camelCase | `public function calcularARL()` |
| **Clases** | PascalCase | `class AfiliadoController` |

### Blade Templates

```blade
<!-- ✅ BUENO - Indentation, semantic HTML -->
<div class="space-y-4">
    @foreach ($afiliados as $afiliado)
        <div class="card">
            <h3 class="font-bold">{{ $afiliado->nombre_completo }}</h3>
            <p class="text-gray-600">{{ $afiliado->numero_documento }}</p>
        </div>
    @endforeach
</div>

<!-- ❌ MALO - Sin indentación, lógica compleja -->
@foreach($afiliados as $a)
<div>{{ $a->primer_nombre }} {{ $a->primer_apellido }} ({{ $a->numero_documento }})</div>
@endforeach
```

### Nombres Significativos

```php
// ❌ MALO - Nombres no descriptivos
$d = Afiliado::count();
$f = $request->fecha;
foreach ($data as $x) { ... }

// ✅ BUENO - Nombres claros
$totalAfiliadosActivos = Afiliado::active()->count();
$fechaRecibo = $request->fecha;
foreach ($detalles as $detalle) { ... }
```

---

## ✅ Testing

### Ejecutar Tests

```bash
# Todos los tests
composer test

# Tests específicos
php artisan test tests/Unit
php artisan test tests/Feature

# Test file específico
php artisan test tests/Feature/AfiliadoTest.php

# Tests que coincidan con patrón
php artisan test --filter AfiliadoTest

# Con cobertura de código
php artisan test --coverage

# Verbose output
php artisan test -v
```

### Escribir Tests

**Estructura:**

```php
// tests/Feature/AfiliadoTest.php
namespace Tests\Feature;

use App\Models\Afiliado;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AfiliadoTest extends TestCase
{
    use RefreshDatabase;  // Reset DB antes de cada test
    
    // ✅ Nombre descriptivo del behavior a testear
    test('puede crear afiliado en empresa actual', function () {
        // Arrange
        $empresa = Empresa::factory()->create();
        $this->actingAs($empresa->usuarios->first());
        session(['empresa_id' => $empresa->id]);
        
        // Act
        $response = $this->post('/afiliados', [
            'numero_documento' => '123456789',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
        ]);
        
        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('afiliados', [
            'numero_documento' => '123456789',
            'empresa_id' => $empresa->id,
        ]);
    });
    
    test('usuario no puede acceder a afiliados de otra empresa', function () {
        // Arrange
        $empresa1 = Empresa::factory()->create();
        $empresa2 = Empresa::factory()->create();
        $afiliado = Afiliado::factory()->create(['empresa_id' => $empresa2->id]);
        
        $this->actingAs($empresa1->usuarios->first());
        session(['empresa_id' => $empresa1->id]);
        
        // Act
        $response = $this->get('/afiliados/' . $afiliado->id);
        
        // Assert
        $response->assertNotFound();
    });
}
```

### Coverage Target

- **Mínimo Phase 0:** 85%
- **Target Phase 1:** 90%
- **Crítico:** Lógica de negocio (Services, Actions) debe tener 100%
- **Controllers:** Mínimo 80% (happy path + error case)

**Ver cobertura:**

```bash
php artisan test --coverage --min=85
```

---

## 🚀 Deployment

### Pre-Deploy Checklist

```bash
# 1. Verificar todo pasa localmente
composer test

# 2. Verificar estilo
php artisan pint --test

# 3. Actualizar DB (si hay migrations)
php artisan migrate --pretend

# 4. Ejecutar static analysis (opcional)
./vendor/bin/phpstan analyze

# 5. Build assets
npm run build
```

### Deployment Process

```bash
# 1. En servidor de producción
git pull origin main

# 2. Instalar/actualizar dependencias
composer install --no-dev

# 3. Ejecutar migrations
php artisan migrate --force

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 5. Build assets (si no estén commiteadas)
npm ci && npm run build

# 6. Restart workers
php artisan queue:restart
php artisan horizon:terminate  # Si usa Horizon
```

### Rollback en Caso de Error

```bash
# 1. Revertir al commit anterior
git revert HEAD

# 2. Rollback last migration
php artisan migrate:rollback

# 3. Deployar versión anterior
git pull && composer install --no-dev && php artisan migrate
```

---

## 🚫 Reglas Importantes

### NO hacer estos cambios sin coordinación:

- ❌ Cambiar estructura de BaseModel o EmpresaScope
- ❌ Modificar tabla empresas o usuarios sin migrations
- ❌ Cambiar campos de validación existentes sin update migration
- ❌ Remover métodos públicos sin deprecation warning
- ❌ Cambiar rutas principales sin actualizar tests y docs

### SIEMPRE:

- ✅ Escribir tests para features nuevas
- ✅ Ejecutar `composer test` antes de push
- ✅ Ejecutar `php artisan pint` antes de commit
- ✅ Actualizar documentación (CLAUDE.md, etc)
- ✅ Hacer commits atómicos y pequeños
- ✅ Escribir commit messages descriptivos

---

## 📚 Recursos Útiles

- **CLAUDE.md** - Setup y arquitectura
- **ARCHITECTURE.md** - Diseño detallado
- **AUDIT_CODIGO.md** - Deuda técnica y mejoras
- **Laravel Docs** - https://laravel.com/docs
- **PSR-12** - https://www.php-fig.org/psr/psr-12/

---

## ❓ Dudas o Preguntas?

1. Revisar **CLAUDE.md** y **ARCHITECTURE.md**
2. Consultar issues abiertos en GitHub
3. Contactar al team lead

---

**Happy coding!** 🚀
