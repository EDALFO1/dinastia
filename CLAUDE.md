# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Dinastía** is an enterprise HR/Payroll platform being transformed into a professional ERP system (comparable to Alegra/SIIGO). The platform manages employees (afiliados), payroll (recibos), remittances (remisiones), incidents, and will eventually include electronic invoicing, electronic payroll, and accounting integrations with Colombia's DIAN.

**Repository:** https://github.com/EDALFO1/dinastia  
**Timeline:** 20 weeks, 20 hrs/week to complete full ERP transformation (see PLAN_EJECUCION.md)  
**Status:** FASE 0 (Preparación y Arquitectura) - Initial architecture cleanup and documentation

## Tech Stack

- **Backend:** Laravel 12, PHP 8.3+
- **Database:** MySQL 8.0 (planned migration to PostgreSQL)
- **Frontend:** Blade templates, Tailwind CSS 4, Alpine.js, Vite
- **API:** REST with Laravel Sanctum (planned in Phase 1)
- **Testing:** PHPUnit + Pest (minimal tests exist; Phase 0 goal: 85%+ coverage)
- **DevOps:** Docker, GitHub Actions CI/CD (minimal setup; Phase 0 goal: establish CI/CD)
- **Queues:** Redis (config ready; async processing in place)

## Multi-Tenant Architecture

This is a **multi-tenant system scoped by `empresa_id`** (company). Every model that extends `BaseModel` automatically filters results by the session's `empresa_id`.

**Key architectural components:**

- **BaseModel** (`app/Models/BaseModel.php`): Adds a global scope and auto-assigns `empresa_id` on creation.
- **EmpresaScope** (`app/Scopes/EmpresaScope.php`): Implements the multi-tenant filtering scope.
- **Middleware** (`CheckRol`, `CheckModulo`, `EmpresaActiva`): Enforce role-based and module-based access control.
- **Session**: The current empresa is stored in the session as `empresa_id`.

**When creating new models:**
- Extend `BaseModel` (not Eloquent's `Model`) to inherit multi-tenant filtering automatically.
- Include `empresa_id` in the migration and fillable array.

## Directory Structure

```
app/
├── Models/                # Eloquent models (BaseModel enforces multi-tenant)
├── Http/
│   ├── Controllers/       # Request handlers (no namespacing yet for API/Web)
│   ├── Requests/          # Form request validation
│   └── Middleware/        # Auth, role, module, empresa checks
├── Services/              # Business logic layer (LiquidacionService, PilaValidator)
├── Helpers/               # Global helper functions (app/Helpers/helpers.php)
├── Scopes/                # Custom query scopes (EmpresaScope)
├── Exports/               # Excel exports (Maatwebsite\Excel)
├── Imports/               # Excel imports
├── View/Composers/        # View composers (SidebarComposer)
└── Providers/             # Service providers

database/
├── migrations/            # Schema migrations (apply with: php artisan migrate)
├── factories/             # Model factories for testing (not yet used)
└── seeders/               # Database seeders (populate test data)

tests/
├── Unit/                  # Unit tests (minimal; ExampleTest.php only)
└── Feature/               # Feature tests (minimal; ExampleTest.php only)

resources/
├── views/                 # Blade templates
├── css/                   # Tailwind CSS
└── js/                    # Alpine.js, Vite entry points
```

## Core Entities (Current State)

- **Empresa:** Multi-tenant tenant; a company using the platform.
- **User:** Platform user; can belong to multiple empresas.
- **Afiliado:** Employee registered with a payroll service (payroll member).
- **Recibo:** Payroll receipt (salary slip).
- **Remisión:** Remittance document sent to authorities (PILA, ARL, EPS).
- **Incapacidad:** Sick leave/disability record.
- **Servicio:** Service associated with an employee (ARL, EPS, Pension).
- **Plan:** Plan/tier available to companies.
- **Modulo:** Feature module (e.g., Payroll, Invoicing) controlled per empresa and rol.
- **Rol:** User role; controls module access and permissions.

## Development Workflow

### Setup

```bash
composer setup        # Install deps, generate key, migrate, install npm, build assets
```

### Daily Development

```bash
composer dev         # Start dev server, queue listener, logs, and Vite watch concurrently
```

This runs:
- `php artisan serve` (Laravel dev server on http://localhost:8000)
- `php artisan queue:listen` (background job worker)
- `php artisan pail` (live log streaming)
- `npm run dev` (Vite dev server for assets)

Press Ctrl+C to stop all.

### Running Tests

```bash
composer test        # Clear config cache, then run all tests with coverage
php artisan test tests/Unit                          # Unit tests only
php artisan test tests/Feature                       # Feature tests only
php artisan test tests/Unit/ModelTest.php            # Single test file
php artisan test --filter FeatureName                # Tests matching filter
```

**Important:** Tests use SQLite in-memory database (see `phpunit.xml`). DB is fresh per test run.

### Code Quality & Linting

```bash
php artisan pint               # Format code (PSR-12, Laravel conventions)
php artisan pint --test        # Check formatting without changes
./vendor/bin/phpstan analyze   # Static analysis (if installed)
```

### Database

```bash
php artisan migrate                    # Run migrations
php artisan migrate:rollback           # Undo last batch
php artisan migrate:fresh              # Drop all, run all migrations
php artisan db:seed                    # Run seeders
php artisan tinker                     # Interactive shell for testing code
```

## Code Conventions & Patterns

### Naming & Structure

- **Models:** Singular, PascalCase (e.g., `Afiliado`, `ReciboDetalle`).
- **Controllers:** PascalCase + "Controller" (e.g., `AfiliadoController`).
- **Services:** PascalCase + "Service" (e.g., `LiquidacionService`).
- **Migrations:** Snake_case with timestamp prefix.
- **Blade files:** Kebab-case (e.g., `afiliado-form.blade.php`).

### Model Patterns

- Extend `BaseModel` for multi-tenant filtering; use `Model` only for non-tenant entities (e.g., Modulo, Documento types).
- Define validation rules in a static `rules()` method (not in Request classes in many cases).
- Use relationships fluently: `belongsTo()`, `hasMany()`, `belongsToMany()`.
- Eager-load relationships in queries when needed: `Afiliado::with('empresa')->get()`.

### Controllers

- Keep controllers thin; move business logic to Services.
- Use `Request` classes for validation.
- Return views or JSON (no API routes yet; Phase 1 will introduce REST endpoints).
- Use middleware to check empresa, role, and module access.

### Services

- Encapsulate business logic (e.g., payroll calculations in `LiquidacionService`).
- Inject repositories or models; use dependency injection.
- Example: `LiquidacionService::calcularRecibo($afiliado, $periodo)`.

### Multi-Tenant Checks

Always assume queries are already filtered by the global scope. When needed, explicitly access `empresa_id` from the session:
```php
$empresaId = session('empresa_id');
```

## Key Flows & Integrations

### Payroll Workflow
1. **Afiliado registration:** Create afiliado + link services (ARL, EPS, Pension).
2. **Recibo generation:** `LiquidacionService` calculates salary, deductions, contributions.
3. **Remisión creation:** Generate PILA, ARL, EPS remittances for government submission.
4. **Export:** Excel exports for audit and compliance (PILA reports, ARL lists).

### Module & Role System
- **Modulos** define features (e.g., "Payroll", "Invoicing").
- **Roles** control module access per empresa (`empresa_modulo`, `rol_modulo` tables).
- **Middleware** enforces access: `CheckModulo`, `CheckRol`.

### Imports & Exports
- **Maatwebsite/Excel:** Used for bulk employee imports and PILA/ARL exports.
- **Validators:** `PilaValidator` checks imported data against Colombian compliance rules.

## Testing Strategy

**Current state:** Minimal tests (only example test cases).  
**Phase 0 goal:** 85%+ coverage across all layers.

**Test patterns to follow:**

- **Unit tests:** Test services, helpers, validation logic in isolation.
- **Feature tests:** Test full workflows (e.g., create afiliado → generate recibo → export PILA).
- **Factories:** Use model factories to generate test data (currently minimal; establish in Phase 0).
- **Database reset:** Each test runs on a fresh SQLite in-memory database.

**Example:**
```php
// tests/Feature/AfiliadoTest.php
test('can create afiliado', function () {
    $empresa = Empresa::factory()->create();
    session(['empresa_id' => $empresa->id]);
    
    $response = $this->post('/afiliados', [
        'nombre' => 'John Doe',
        'documento' => '123456789',
        // ...
    ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('afiliados', ['documento' => '123456789']);
});
```

## Common Tasks

### Adding a New Feature

1. **Create the model** (extend `BaseModel` for multi-tenant).
2. **Create migration** (include `empresa_id` for tenant filtering).
3. **Create controller** (handle requests, use services for logic).
4. **Create request class** (validation rules).
5. **Create service** (if business logic is complex).
6. **Add tests** (unit for service, feature for workflow).
7. **Add routes** (in `routes/web.php` or `routes/api.php` later).
8. **Create views** (Blade templates with Tailwind styling).

### Modifying Multi-Tenant Queries

Models extending `BaseModel` are auto-scoped. To bypass the scope (rare):
```php
Afiliado::withoutGlobalScope('empresa')->where(...)->get();
```

### Checking User Permissions

```php
// In controller or middleware
if (!auth()->user()->can('modulo.invoicing', $empresa)) {
    // Access denied
}
```

## Performance Considerations

- **N+1 queries:** Always eager-load relationships. Use `->with()`.
- **Indexes:** Multi-tenant queries benefit from composite indexes on `(empresa_id, id)`.
- **Caching:** Redis is configured; use `Cache::put()` for expensive operations (e.g., PILA calculations).
- **Pagination:** Use `->paginate()` for large result sets; default is 15 per page.

## Known Limitations & Tech Debt

- **No API yet:** Controllers return HTML/Blade; Phase 1 introduces REST + Sanctum.
- **Minimal tests:** Only example test cases exist; Phase 0 target: 85%+ coverage.
- **No CI/CD:** GitHub Actions not yet configured; Phase 0 goal: basic CI/CD pipeline.
- **Legacy code:** Older code mixes validation in models and controllers; moving to Request classes.
- **No domain structure:** Currently flat app structure; Phase 0 refactor to `app/Domains/` (Payroll, Invoicing, Accounting, Shared).
- **Manual dependency injection:** No service container autowiring yet; explicit injection in progress.

## Important Files & References

- **PLAN_EJECUCION.md:** Detailed 20-week roadmap with all phases and checkpoints.
- **ROADMAP.md:** High-level strategic roadmap.
- **.env.example:** Template for environment configuration.
- **config/app.php, config/database.php:** Framework configuration.

## Next Phase (Phase 1)

After Phase 0 (Preparation):
- Extract API controllers under `app/Http/Controllers/Api/`.
- Implement Laravel Sanctum for token-based authentication.
- Generate OpenAPI/Swagger documentation.
- Setup query optimization and Redis caching.

## Quick Reference: Common Commands

| Task | Command |
|------|---------|
| Start dev environment | `composer dev` |
| Run all tests | `composer test` |
| Run single test file | `php artisan test tests/Feature/AfiliadoTest.php` |
| Format code | `php artisan pint` |
| Create model + migration | `php artisan make:model ModelName -m` |
| Create controller | `php artisan make:controller ControllerName` |
| Create request class | `php artisan make:request StoreModelRequest` |
| Create service | (manually in `app/Services/`) |
| Fresh database | `php artisan migrate:fresh --seed` |
| Database console | `php artisan tinker` |
| View logs | `php artisan pail` |

---

**Last Updated:** 2026-06-24  
**Next Review:** After Phase 0 completion (2 weeks)
