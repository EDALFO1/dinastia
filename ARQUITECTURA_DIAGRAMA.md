# 🏗️ DIAGRAMA DE ARQUITECTURA - DINASTÍA

**Fecha:** 2026-06-25  
**Estado:** Arquitectura actual + objetivos  
**Audiencia:** Developers, architects

---

## 📐 VISTA GENERAL: CURRENT STATE (PROBLEMA)

```
┌─────────────────────────────────────────────────────────────────┐
│                      USUARIO (BROWSER)                          │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                    LARAVEL ROUTING                              │
│  routes/web.php → imports from app/Http/Controllers/ ❌         │
└────────────────────────────┬────────────────────────────────────┘
                             │
              ┌──────────────┴──────────────┐
              ▼                             ▼
    ┌─────────────────────┐    ┌─────────────────────┐
    │  app/Controllers/   │    │  app/Domains/*/     │
    │  (34 files - VACÍO) │    │  Controllers/ (REAL)│
    │  ❌ DEAD CODE       │    │  ✅ FUNCIONANDO    │
    │                     │    │                     │
    │ - ReciboController  │    │ PERO IMPORTAN DE   │
    │ - RemisionCtrler    │    │ app/Models/ legacy │
    │ - (etc)             │    │                     │
    └─────────────────────┘    └────────────┬────────┘
                                            │
                                            ▼
                            ┌───────────────────────┐
                            │  app/Models/          │
                            │  (BaseModel, etc)     │
                            │  ✅ LEGACY - FUNCIONA │
                            │                       │
                            │  app/Domains/Shared/  │
                            │  Models/ (DUPLICADO)  │
                            │  ❌ CONFUSIÓN         │
                            └───────────────────────┘
```

### ⚠️ **PROBLEMA: Dual Structure**

| Aspecto | Status | Problema |
|---------|--------|----------|
| Controllers | 2 ubicaciones | ¿Cuál es la real? |
| Models | 2 ubicaciones | Scopes duplicados |
| Services | 2 ubicaciones | ¿Dónde está la lógica? |
| Traits | 2 ubicaciones | Code duplication |
| **Result:** | 🔴 | **CONFUSIÓN TOTAL** |

---

## ✅ ARQUITECTURA OBJETIVO (FASE 0 - WEEK 2)

```
┌─────────────────────────────────────────────────────────────────┐
│                      USUARIO (BROWSER)                          │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│  routes/web.php & routes/api.php                               │
└────────────────────────────┬────────────────────────────────────┘
                             │
              ┌──────────────┴──────────────┐
              ▼                             ▼
      ┌─────────────────┐         ┌─────────────────┐
      │   WEB ROUTES    │         │  API ROUTES     │
      │  (Blade views)  │         │  (JSON + JWT)   │
      └────────┬────────┘         └────────┬────────┘
               │                           │
        ┌──────▼──────┐            ┌──────▼──────┐
        │             │            │             │
        ▼             ▼            ▼             ▼
    ┌─────────────────────┐   ┌──────────────────────┐
    │  app/Domains/       │   │  app/Domains/        │
    │  */Controllers/     │   │  */Controllers/Api/  │
    │  (Web)              │   │  (API - Phase 1)     │
    │                     │   │                      │
    │ • Payroll/          │   │ • Api/AfiliadoApi   │
    │   Controllers/      │   │ • Api/ReciboApi     │
    │   - AfiliadoCtrl    │   │ • Api/RemisionApi   │
    │   - ReciboCtrl      │   │                      │
    │                     │   │ ↓ Returns Resources  │
    │ • Invoicing/        │   │   (JSON with types) │
    │ • Accounting/ (soon)│   │                      │
    └──────────┬──────────┘   └──────────┬───────────┘
               │                         │
               └──────────────┬──────────┘
                              │
                    ┌─────────▼──────────┐
                    │  app/Domains/      │
                    │  */Services/       │
                    │  (BUSINESS LOGIC)  │
                    │                    │
                    │ • Payroll/         │
                    │   Services/        │
                    │   - Liquidacion    │
                    │   - Remision       │
                    │   - PilaValidator  │
                    │                    │
                    │ • Shared/          │
                    │   - DianValidator  │
                    │   - ExportService  │
                    └─────────┬──────────┘
                              │
                    ┌─────────▼──────────┐
                    │  app/Domains/      │
                    │  Shared/Models/    │
                    │  (SINGLE SOURCE)   │
                    │                    │
                    │ • BaseModel        │
                    │ • EmpresaScope     │
                    │ • MultiTenant      │
                    │   Trait            │
                    │                    │
                    │ Payroll/Models/    │
                    │ • Afiliado         │
                    │ • Recibo           │
                    │ • Remision         │
                    │ • Incapacidad      │
                    │                    │
                    │ Invoicing/Models/  │
                    │ • Invoice (soon)   │
                    │ • InvoiceLine      │
                    │ • InvoiceTax       │
                    └─────────┬──────────┘
                              │
                    ┌─────────▼──────────┐
                    │  DATABASE          │
                    │  (MySQL 8.0)       │
                    │                    │
                    │ Multi-tenant:      │
                    │ ALL queries filtered│
                    │ by empresa_id      │
                    └────────────────────┘
```

---

## 🔄 MULTI-TENANT QUERY FLOW

```
┌──────────────────────────────────────────────────────────────────┐
│ REQUEST ARRIVES WITH SESSION                                     │
│ session(['empresa_id' => 5])                                     │
└────────────────────────┬─────────────────────────────────────────┘
                         │
                         ▼
           ┌─────────────────────────┐
           │ Middleware:             │
           │ • EmpresaActiva         │
           │ • CheckModulo           │
           │ • CheckRol              │
           │ (Verify user has access)│
           └────────────┬────────────┘
                        │
                        ▼
           ┌──────────────────────────────────┐
           │ Controller calls Service         │
           │ $service->calcular($afiliado)   │
           └────────────┬─────────────────────┘
                        │
                        ▼
           ┌──────────────────────────────────┐
           │ Service queries via Model        │
           │ $afiliado->recibos()->get()     │
           └────────────┬─────────────────────┘
                        │
                        ▼
        ┌──────────────────────────────────────┐
        │ Model extends BaseModel              │
        │ ✅ Global Scope AUTOMATICALLY applied│
        │                                      │
        │ $query->where('empresa_id', 5)      │
        │ (Invisible to developer)             │
        └────────────┬──────────────────────────┘
                     │
                     ▼
        ┌──────────────────────────────────────┐
        │ DATABASE QUERY EXECUTED              │
        │ SELECT * FROM recibos                │
        │ WHERE empresa_id = 5                 │ ← SEGURO
        │ AND (other conditions)               │
        │                                      │
        │ ❌ Empresa 6 NUNCA ve data empresa 5 │
        └──────────────────────────────────────┘
```

**Código real:**
```php
// En Service
$afiliados = Afiliado::where('estado', 'activo')->get();
// 🔍 Bajo el capó:
// → WHERE empresa_id = session('empresa_id') + estado = activo

// En Controller (NO PERMITIDO)
Afiliado::withoutGlobalScope('empresa')->get();  // ⚠️ Dangerous!
// Solo admins pueden usar esto, debe ser logged
```

---

## 🎯 LAYER RESPONSIBILITIES

```
┌──────────────────────────────────────────────────────────────────┐
│ PRESENTATION LAYER (HTTP)                                        │
│ • Controllers (app/Domains/*/Controllers/)                       │
│ • Request validation (app/Http/Requests/)                        │
│ • Resources/Views (app/Domains/*/Resources/, resources/views/)  │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│ BUSINESS LOGIC LAYER (SERVICE)                                   │
│ • Services (app/Domains/*/Services/)                             │
│ • Validators (e.g., DianValidator, PilaValidator)               │
│ • Exporters (e.g., PilaExporter, InvoiceXmlExporter)           │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│ DATA ACCESS LAYER (ELOQUENT)                                     │
│ • Models (app/Domains/*/Models/)                                 │
│ • Relationships (defined on models)                              │
│ • Global scopes (BaseModel + EmpresaScope)                      │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│ DATABASE LAYER                                                   │
│ • MySQL 8.0 (multi-tenant tenant_id column)                      │
│ • Migrations (database/migrations/)                              │
│ • Indexes (composite on company_id + field)                     │
└──────────────────────────────────────────────────────────────────┘

CROSS-CUTTING CONCERNS:
┌──────────────────────────────────────────────────────────────────┐
│ • Authentication/Authorization (Middleware)                       │
│ • Logging (app/Domains/Shared/Logging/)                         │
│ • Caching (Redis via app/Domains/*/Services/)                   │
│ • Error Handling (app/Exceptions/)                               │
│ • Audit (app/Domains/Shared/Audit/ - Phase 5)                   │
└──────────────────────────────────────────────────────────────────┘
```

---

## 📦 DOMAIN STRUCTURE (PHASE 0 COMPLETION)

```
app/Domains/
│
├── Shared/                          ← Código reutilizable
│   ├── Models/
│   │   ├── BaseModel.php            ✅ Multi-tenant base
│   │   └── User.php                 ✅ Cross-tenant user
│   │
│   ├── Scopes/
│   │   └── EmpresaScope.php         ✅ Global multi-tenant scope
│   │
│   ├── Traits/
│   │   ├── MultiTenantTrait.php
│   │   └── TimestampTrait.php
│   │
│   ├── Services/
│   │   ├── DianValidator.php
│   │   ├── PilaValidator.php
│   │   └── ExportService.php
│   │
│   ├── Exports/
│   │   ├── PilaExporter.php
│   │   └── ArlExporter.php
│   │
│   └── Requests/                    ← Shared form requests
│       └── LoginRequest.php
│
├── Payroll/                         ← Nómina y Recibos
│   ├── Models/
│   │   ├── Afiliado.php            ✅ Employee
│   │   ├── Recibo.php              ✅ Payslip
│   │   ├── ReciboDetalle.php
│   │   ├── Remision.php            ✅ Remittance (PILA/ARL)
│   │   ├── RemisionDetalle.php
│   │   ├── Incapacidad.php         ✅ Disability/Sick leave
│   │   ├── Servicio.php            ✅ Service (ARL/EPS/Pension)
│   │   └── Plan.php                ✅ Service plan/tier
│   │
│   ├── Services/
│   │   ├── LiquidacionService.php   ← Salary calculations
│   │   ├── RemisionService.php      ← PILA/ARL generation
│   │   └── ReciboCalculationService.php (REFACTORED from Controller)
│   │
│   ├── Controllers/
│   │   ├── AfiliadoController.php   ✅ Web (Blade)
│   │   ├── ReciboController.php     ✅ Web (REFACTORED)
│   │   └── RemisionController.php   ✅ Web (REFACTORED)
│   │
│   ├── Controllers/Api/             ← Phase 1
│   │   ├── AfiliadoController.php   (JSON API)
│   │   ├── ReciboController.php     (JSON API)
│   │   └── RemisionController.php   (JSON API)
│   │
│   ├── Resources/                   ← Phase 1 (JSON serialization)
│   │   ├── AfiliadoResource.php
│   │   ├── ReciboResource.php
│   │   └── RemisionResource.php
│   │
│   ├── Requests/
│   │   ├── StoreAfiliadoRequest.php
│   │   ├── StoreReciboRequest.php
│   │   └── StoreRemisionRequest.php
│   │
│   ├── Database/
│   │   ├── migrations/
│   │   ├── factories/
│   │   └── seeders/
│   │
│   └── Tests/
│       ├── Unit/
│       │   ├── LiquidacionServiceTest.php
│       │   └── RemisionServiceTest.php
│       └── Feature/
│           ├── AfiliadoTest.php
│           ├── ReciboTest.php
│           └── RemisionTest.php
│
├── Invoicing/                       ← Facturación electrónica (Phase 2)
│   ├── Models/
│   │   ├── Invoice.php
│   │   ├── InvoiceLineItem.php
│   │   └── InvoiceTax.php
│   ├── Services/
│   │   ├── InvoiceGenerationService.php
│   │   ├── DianXmlService.php
│   │   └── InvoiceSigningService.php
│   └── ...
│
├── Accounting/                      ← Contabilidad (Phase 4)
│   ├── Models/
│   │   ├── ChartOfAccounts.php
│   │   ├── JournalEntry.php
│   │   └── JournalLine.php
│   ├── Services/
│   │   ├── AutomatedJournalService.php
│   │   └── FinancialReportService.php
│   └── ...
│
└── Audit/                           ← Auditoría (Phase 5)
    ├── Models/
    │   └── AuditLog.php
    ├── Services/
    │   └── AuditService.php
    └── ...
```

---

## 🔌 ROUTING STRUCTURE

```
routes/
│
├── web.php                          ← Web routes (HTML responses)
│   │
│   ├── GET    /afiliados            → Payroll\AfiliadoController@index
│   ├── POST   /afiliados            → Payroll\AfiliadoController@store
│   ├── GET    /afiliados/{id}/edit  → Payroll\AfiliadoController@edit
│   ├── PUT    /afiliados/{id}       → Payroll\AfiliadoController@update
│   │
│   ├── GET    /recibos              → Payroll\ReciboController@index
│   ├── POST   /recibos              → Payroll\ReciboController@store
│   ├── GET    /recibos/{id}         → Payroll\ReciboController@show
│   ├── POST   /recibos/{id}/export  → Payroll\ReciboController@exportPila
│   │
│   └── (etc)
│
├── api.php                          ← API routes (JSON responses) [Phase 1+]
│   │
│   ├── POST   /api/auth/login       → Shared\Api\AuthController@login
│   ├── GET    /api/afiliados        → Payroll\Api\AfiliadoController@index
│   ├── POST   /api/afiliados        → Payroll\Api\AfiliadoController@store
│   ├── GET    /api/afiliados/{id}   → Payroll\Api\AfiliadoController@show
│   │
│   ├── GET    /api/recibos          → Payroll\Api\ReciboController@index
│   ├── POST   /api/recibos          → Payroll\Api\ReciboController@store
│   ├── GET    /api/recibos/{id}     → Payroll\Api\ReciboController@show
│   │
│   └── (etc)
│
└── console.php                      ← Artisan commands
```

---

## 📈 PHASE PROGRESSION

```
PHASE 0: Preparación (Semanas 1-2)
┌────────────────────────────────────┐
│ • Consolidate to app/Domains/ only │
│ • 5+ feature tests running         │
│ • Type hints on all public methods │
│ • Refactor Recibo/Remision logic   │
│ • CI/CD basic pipeline             │
└────────────────────────────────────┘
                  ↓
PHASE 1: API REST (Semanas 3-5)
┌────────────────────────────────────┐
│ • Laravel Sanctum auth             │
│ • Api/Controllers for all domains  │
│ • Resources for JSON serialization │
│ • OpenAPI documentation           │
│ • 50+ API tests                    │
└────────────────────────────────────┘
                  ↓
PHASE 2: Invoicing (Semanas 6-10)
┌────────────────────────────────────┐
│ • Invoice models & validations     │
│ • XML UBL 2.1 generation          │
│ • DIAN integration                 │
│ • PDF invoices + reports          │
└────────────────────────────────────┘
                  ↓
PHASE 3: Electronic Payroll (Semanas 11-13)
PHASE 4: Accounting (Semanas 14-18)
PHASE 5: Audit & Go-Live (Semanas 19-20)
```

---

## 🔐 MULTI-TENANT SECURITY CHECKS

```
REQUEST FLOW WITH SECURITY GATES:
┌────────────────────────────────────┐
│ User logs in (empresa_id = 5)      │
│ session(['empresa_id' => 5])       │
└────────────────────────────────────┘
           ↓
   🔒 GATE 1: EmpresaActiva Middleware
   ├─ Check: Is empresa_id still valid?
   ├─ Check: Is user subscribed?
   └─ Result: ❌ 403 if invalid
           ↓
   🔒 GATE 2: CheckModulo Middleware
   ├─ Check: Does empresa have "Payroll" module?
   ├─ Check: Does user's role have access?
   └─ Result: ❌ 403 if no access
           ↓
   🔒 GATE 3: Global Scope (BaseModel)
   ├─ Check: Auto-filter all queries by empresa_id
   ├─ Scope: WHERE empresa_id = session('empresa_id')
   └─ Result: ❌ Empty result if not your empresa
           ↓
✅ Response: User sees only THEIR empresa's data
```

**Example - Afiliado retrieval:**
```php
// Phase 0 - Current
Afiliado::find($id);                    // Gate 1, 2, 3 applied
// Returns: Afiliado from empresaid=5 ONLY

// ❌ DANGEROUS (requires authentication):
Afiliado::withoutGlobalScope('empresa')->find($id);  // Bypasses Gate 3!
// Only admins should ever do this (must log!)

// ✅ CORRECT if you need cross-empresa (rare):
Log::warning("Cross-empresa access", ['user_id' => auth()->id(), 'target_empresa' => $id]);
Afiliado::withoutGlobalScope('empresa')->where('id', $id)->firstOrFail();
```

---

## 🛠️ DEVELOPMENT WORKFLOW

```
Start: composer dev
└─ Starts 4 concurrent processes:

    1. Laravel Dev Server (port 8000)
    2. Queue Listener (background jobs)
    3. Pail (live log streaming)
    4. Vite Dev Server (asset watch)

During Development:
├─ Modify app/Domains/*/Controllers/ → reload (hot)
├─ Modify app/Domains/*/Models/ → reload (hot)
├─ Modify app/Domains/*/Services/ → reload (hot)
├─ Modify resources/views/ → hot reload (Vite)
└─ Modify database/migrations/ → php artisan migrate

Before Commit:
├─ php artisan pint (code formatting)
├─ php artisan test (run tests)
├─ Verify: All tests pass + coverage > X%
└─ git commit -m "message"

On Push:
└─ GitHub Actions (Phase 0 Week 2)
   ├─ Run tests
   ├─ Check code style
   └─ Report status
```

---

## 📊 KEY METRICS

**Phase 0 Targets:**
| Metric | Target | Current | Status |
|--------|--------|---------|--------|
| Test Coverage | 85%+ | <5% | 🔴 |
| Controllers avg lines | <100 | 400-1100 | 🔴 |
| Type hints | 100% | 20% | 🔴 |
| Linter warnings | 0 | ? | ⚠️ |
| Code duplication | <5% | 15% (Domains vs app) | 🔴 |
| API endpoints | 30+ | 0 | ⏳ (Phase 1) |

---

## 🚀 SUCCESS CRITERIA

**Phase 0 Complete = :**
- [ ] Single source of truth for code (app/Domains/ only)
- [ ] 5+ feature tests passing
- [ ] 0 linter warnings
- [ ] All public methods have return types
- [ ] ReciboController < 400 lines (refactored)
- [ ] RemisionController < 250 lines (refactored)
- [ ] GitHub Actions CI/CD running
- [ ] Multi-tenant scoping verified (test suite)
- [ ] New developer can clone + `composer setup && composer dev` in 10 minutes
- [ ] CLAUDE.md + ARCHITECTURE + CONTRIBUTING fully integrated

---

**Documento creado por:** Claude Code  
**Fecha:** 2026-06-25  
**Próxima revisión:** Fin Semana 2 (después de refactor)
