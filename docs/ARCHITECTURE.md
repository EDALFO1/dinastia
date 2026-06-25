# 🏗️ ARQUITECTURA - DINASTÍA

**Versión:** 1.0  
**Última actualización:** 2026-06-24  
**Responsable:** Phase 0 Arquitectura  

---

## 📐 VISIÓN GENERAL

Dinastía es una plataforma **multi-tenant** (multi-empresa) para gestión de nómina y RRHH en Colombia. La arquitectura actual es **monolítica basada en Laravel** con separación clara entre capas:

```
┌─────────────────────────────────────────┐
│      PRESENTACIÓN (Blade + Alpine)      │
├─────────────────────────────────────────┤
│     ENRUTAMIENTO Y CONTROLADORES        │
├─────────────────────────────────────────┤
│      SERVICIOS Y LÓGICA DE NEGOCIO      │
├─────────────────────────────────────────┤
│   MODELOS (BaseModel + Multi-tenant)    │
├─────────────────────────────────────────┤
│         BASE DE DATOS (MySQL 8.0)       │
└─────────────────────────────────────────┘
```

---

## 🔑 CARACTERÍSTICAS ARQUITECTÓNICAS

### 1. **Multi-Tenant por Default**

Todos los datos están filtrados automáticamente por `empresa_id` usando un **Global Scope**:

```
┌─────────────────────────────────────────┐
│  Global Scope: EmpresaScope             │
│  Aplicado automáticamente a:            │
│  - Todas las queries SELECT             │
│  - Todas las creaciones de registros    │
│  - Filtrado transparente por session    │
└─────────────────────────────────────────┘
```

**Cómo funciona:**

1. Usuario inicia sesión
2. Selecciona una empresa: `session(['empresa_id' => $empresa->id])`
3. Todas las queries automáticamente adicionan `WHERE empresa_id = X`
4. Imposible acceder a datos de otra empresa sin cambiar sesión

**Modelos afectados:**
- ✅ Afiliado, Recibo, Remisión, Incapacidad, Plan, etc.
- ❌ Empresa, User, Rol, Modulo (datos "globales")

---

### 2. **Capas de Arquitectura**

#### **Capa 1: Presentación (Vista)**
```
resources/views/
├── modules/
│   ├── afiliados/
│   │   ├── index.blade.php      (Lista paginada)
│   │   ├── create.blade.php     (Formulario crear)
│   │   └── edit.blade.php       (Formulario editar)
│   ├── recibos/
│   ├── remisiones/
│   └── ...
├── components/                   (Componentes reutilizables)
└── layouts/                       (Layouts base)
```

**Stack:**
- **Blade:** Templating engine de Laravel
- **Alpine.js:** Interactividad ligera (sin Build step)
- **Tailwind CSS 4:** Estilos utilidad-first
- **Vite:** Bundler para assets (CSS, JS)

---

#### **Capa 2: Enrutamiento y Control**

```
routes/web.php
    ├── Auth routes (login, logout, password reset)
    ├── Dashboard
    ├── Resource routes (RESTful para modelos)
    │   ├── GET    /afiliados           → index()
    │   ├── POST   /afiliados           → store()
    │   ├── GET    /afiliados/{id}      → show()
    │   ├── GET    /afiliados/{id}/edit → edit()
    │   ├── PUT    /afiliados/{id}      → update()
    │   └── DELETE /afiliados/{id}      → destroy()
    └── Custom routes (acciones específicas)
        └── POST /recibos/calcular      → calcularRecibo()
```

**Controladores Principales:**

| Controlador | Responsabilidad | Modelos |
|-------------|-----------------|---------|
| ReciboController | Crear, editar, listar recibos (nómina) | Recibo, ReciboDetalle |
| AfiliadoController | Gestionar empleados | Afiliado, Afiliacion |
| RemisionController | Generar remisiones (PILA, ARL, EPS) | Remision, RemisionDetalle |
| EmpresaController | Configuración de empresa | Empresa, Plan |
| IncapacidadController | Licencias y incapacidades | Incapacidad |
| ExportBatchController | Exportar en lotes a Excel | ExportBatch |

**Middleware Stack:**

```
Request
  ↓
CheckAuthenticated
  ↓
SetEmpresaIdFromSession  (Establece empresa_id en sesión)
  ↓
CheckEmpresaActiva       (Verifica que empresa existe y está activa)
  ↓
CheckRol                 (Verifica rol del usuario)
  ↓
CheckModulo              (Verifica acceso a módulo específico)
  ↓
Controller → View
```

---

#### **Capa 3: Servicios y Lógica de Negocio**

**Ubicación:** `app/Services/`

```
LiquidacionService
├── calcular($afiliado, $dias)        → Calcula nómina
├── calcularIBC()                     → Ingreso Base de Cotización
├── calcularEPS()                     → Aporte EPS (4%)
├── calcularARL()                     → Aporte ARL (variable)
├── calcularPension()                 → Aporte pensión (16%)
└── calcularCaja()                    → Aporte caja (4%)

ModuloService
├── puedeAcceder($slug)               → ¿Tiene acceso a módulo?
└── obtenerModulosDeUsuario()         → Listado de módulos disponibles

PilaValidator
└── validar($remision)                → Valida estructura PILA
```

**Flujos de Negocio:**

```
CREAR RECIBO
├── Validar afiliado existe
├── Validar no existe recibo del período
├── LiquidacionService::calcular()
│   ├── Obtener IBC (Ingreso Base de Cotización)
│   ├── Calcular aportes (EPS, ARL, Pensión, Caja)
│   └── Retornar array con desglose
├── Guardar Recibo (cabecera)
├── Guardar ReciboDetalle (líneas de concepto)
└── Retornar Recibo creado

GENERAR REMISIÓN (PILA)
├── Obtener recibos no exportados
├── Agrupar por empresa laboral
├── Calcular totales por concepto
├── Generar XML según formato DIAN
├── Exportar a Excel
└── Marcar como exportado
```

---

#### **Capa 4: Modelos y Persistencia**

**Ubicación:** `app/Models/`

**Patrón Base:**

Todos los modelos "operacionales" extienden `BaseModel`:

```php
class Afiliado extends BaseModel  // Multi-tenant
{
    // Heredita global scope automático
    // Tiene empresa_id asignado automáticamente
}

class Empresa extends Model  // Global (no multi-tenant)
{
    // No extiende BaseModel
    // Accesible para todos los usuarios con permiso
}
```

**Modelos Principales:**

```
MODELOS DE NÓMINA Y RRHH
├── Afiliado              (Empleado)
│   ├── numero_documento
│   ├── nombres, apellidos
│   ├── fecha_nacimiento, sexo
│   └── estado (activo/inactivo)
│
├── Recibo                (Nómina - Payslip)
│   ├── afiliado_id
│   ├── fecha
│   ├── ibc (Ingreso Base de Cotización)
│   ├── valor_eps, valor_arl, valor_pension, valor_caja
│   ├── total
│   └── detalles (líneas de concepto)
│
├── ReciboDetalle         (Línea de concepto en nómina)
│   ├── recibo_id
│   ├── concepto (ej: "EPS", "Bonificación")
│   └── valor
│
├── Remision              (Remesa - Envío a autoridades)
│   ├── fecha
│   ├── tipo (PILA, ARL, EPS)
│   ├── total_registros
│   └── export_batch_id (para tracking)
│
├── RemisionDetalle       (Línea de remesa)
│   └── Datos por afiliado para transmisión

MODELOS DE CONFIGURACIÓN
├── Empresa               (Empresa/Tenant)
│   ├── nit
│   ├── nombre
│   ├── plan_id
│   └── estado
│
├── EmpresaLaboral        (Razón social/sede operativa)
│   ├── empresa_id
│   ├── nombre
│   ├── nit
│   └── direccion
│
├── Plan                  (Plan/tier de servicio)
│   ├── nombre (Básico, Premium, etc)
│   └── características
│
├── Modulo                (Feature flags)
│   ├── nombre (Payroll, Invoicing, Accounting)
│   └── descripción
│
├── Rol                   (Role-based access)
│   ├── nombre
│   └── descripción

MODELOS DE PARÁMETROS
├── ParametroAnual        (Configuración anual)
│   ├── tipo (salario_minimo, upc, eps_porcentaje)
│   ├── valor
│   └── vigencia (año)
│
├── Eps, Arl, Pension, Caja  (Intermediarios)
│   ├── nombre
│   ├── codigo (para DIAN)
│   └── porcentaje (variable por EPS/ARL)

MODELOS DE SOPORTE
├── Incapacidad           (Licencia/Incapacidad)
│   ├── afiliado_id
│   ├── tipo (Enfermedad, Maternidad, etc)
│   └── fechas
│
├── Nota                  (Notas internas)
│   └── Para auditoría
│
├── ExportBatch           (Control de lotes exportados)
│   ├── fecha_inicio, fecha_fin
│   ├── cantidad_registros
│   └── archivo (path a Excel)
```

**Relaciones Principales:**

```
Empresa (1) ─────────────── (N) Afiliado
   │
   └──────────────── (N) EmpresaLaboral
                         │
                         └──────────── (N) Afiliado

Afiliado (1) ─────────────── (N) Recibo
   │
   └──────────────── (N) Incapacidad
   │
   └──────────────── (N) AfiliadoServicio
                         │
                         └──────────── (1) Servicio (EPS, ARL, Pension)

Recibo (1) ─────────────── (N) ReciboDetalle

Remision (1) ─────────────── (N) RemisionDetalle
```

---

#### **Capa 5: Base de Datos**

**Motor:** MySQL 8.0 (con índices para multi-tenant)

**Esquema Clave:**

```sql
-- Multi-tenant: Todas las tablas tienen empresa_id
CREATE TABLE empresas (
    id BIGINT PRIMARY KEY,
    nit VARCHAR(20) UNIQUE,
    nombre VARCHAR(255),
    plan_id BIGINT,
    estado BOOLEAN DEFAULT TRUE,
    timestamps
);

CREATE TABLE afiliados (
    id BIGINT PRIMARY KEY,
    empresa_id BIGINT,                           -- TENANT
    numero_documento VARCHAR(20),
    primer_nombre VARCHAR(100),
    primer_apellido VARCHAR(100),
    estado BOOLEAN DEFAULT TRUE,
    timestamps,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id),
    INDEX idx_empresa_estado (empresa_id, estado)  -- Índice multi-tenant
);

CREATE TABLE recibos (
    id BIGINT PRIMARY KEY,
    empresa_id BIGINT,                           -- TENANT
    afiliado_id BIGINT,
    fecha DATE,
    ibc DECIMAL(15,2),
    valor_eps DECIMAL(15,2),
    valor_arl DECIMAL(15,2),
    valor_pension DECIMAL(15,2),
    valor_caja DECIMAL(15,2),
    total DECIMAL(15,2),
    export_batch_id BIGINT NULLABLE,
    timestamps,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id),
    FOREIGN KEY (afiliado_id) REFERENCES afiliados(id),
    INDEX idx_empresa_periodo (empresa_id, fecha),
    INDEX idx_empresa_afiliado (empresa_id, afiliado_id)
);
```

---

## 🌐 DOMINIOS Y MÓDULOS (Roadmap Phase 0)

**Objetivo Phase 0:** Refactor actual `app/` a estructura modular `app/Domains/`

```
app/Domains/
├── Payroll/                    (Nómina y RRHH)
│   ├── Models/
│   │   ├── Afiliado.php
│   │   ├── Recibo.php
│   │   └── ReciboDetalle.php
│   ├── Services/
│   │   └── LiquidacionService.php
│   ├── Actions/
│   │   └── CrearReciboAction.php
│   ├── Controllers/
│   │   ├── ReciboController.php
│   │   └── AfiliadoController.php
│   ├── Requests/
│   │   ├── StoreReciboRequest.php
│   │   └── StoreAfiliadoRequest.php
│   ├── Resources/
│   │   └── ReciboResource.php
│   ├── Exports/
│   │   └── PilaExport.php
│   └── Events/
│       └── ReciboCreated.php
│
├── Invoicing/                  (Facturación Electrónica - Phase 2)
│   ├── Models/
│   │   ├── Invoice.php
│   │   └── InvoiceLineItem.php
│   └── Services/
│       └── InvoiceXmlGeneratorService.php
│
├── Accounting/                 (Contabilidad - Phase 4)
│   ├── Models/
│   │   ├── ChartOfAccounts.php
│   │   └── JournalEntry.php
│   └── Services/
│       └── JournalService.php
│
├── DIAN/                       (Integración DIAN)
│   └── Services/
│       └── DIANClientService.php
│
└── Shared/                     (Código compartido)
    ├── Models/
    │   ├── Empresa.php
    │   ├── User.php
    │   └── Modulo.php
    ├── Traits/
    │   └── MultiTenant.php
    ├── Scopes/
    │   └── EmpresaScope.php
    ├── Middleware/
    │   └── CheckModulo.php
    └── Helpers/
        └── helpers.php
```

---

## 🔄 FLUJOS DE DATOS PRINCIPALES

### Flujo 1: Crear Recibo de Nómina

```
1. Usuario accede a /recibos/create
   └─> ReciboController::create()
       └─> Carga formulario con lista de afiliados

2. Usuario completa formulario y hace POST /recibos
   └─> ReciboController::store(StoreReciboRequest $request)
       ├─> Validar datos (en FormRequest)
       ├─> LiquidacionService::calcular($afiliado, $dias)
       │   ├─> Obtener IBC desde afiliado
       │   ├─> Calcular aportes (EPS 4%, Pensión 16%, ARL variable)
       │   └─> Retornar array con desglose
       ├─> Crear registro Recibo
       ├─> Crear registros ReciboDetalle (uno por concepto)
       ├─> Si novedad == 'Retiro', inactivar afiliado
       └─> Redirigir a /recibos con mensaje éxito

3. Usuario ve el recibo creado
   └─> Puede editar o exportar (si no está en lote)
```

### Flujo 2: Generar Remisión (PILA)

```
1. Usuario accede a /remisiones/crear
   └─> RemisionController::create()

2. Usuario selecciona período y hace POST /remisiones
   └─> RemisionController::store()
       ├─> Obtener recibos no exportados del período
       ├─> Agrupar por empresa laboral
       ├─> PilaValidator::validar()  (Valida estructura)
       ├─> Generar XML según estándar DIAN
       ├─> Exportar a Excel
       ├─> Crear registro Remision
       ├─> Crear RemisionDetalle (una fila por afiliado)
       └─> Enlazar recibos con export_batch_id

3. Usuario descarga archivo Excel
   └─> Excel::download()
```

### Flujo 3: Control de Acceso Multi-Tenant

```
1. Usuario inicia sesión
   └─> Auth::login($user)

2. Usuario selecciona empresa (en dashboard)
   └─> session(['empresa_id' => $empresa->id])

3. Usuario accede a /afiliados
   └─> Middleware CheckEmpresaActiva
       └─> Verifica que empresa_id esté en sesión
   
4. Controller ejecuta:
   └─> Afiliado::get()  (inherently scoped by empresa_id)
       └─> SELECT * FROM afiliados 
           WHERE empresa_id = session('empresa_id')

5. Resultado:
   └─> Usuario solo ve datos de su empresa
```

---

## 🔐 Seguridad y Control de Acceso

```
NIVELES DE CONTROL:

1. AUTENTICACIÓN
   ├─> Laravel Auth (sessions, remember-me)
   └─> Middleware: auth

2. AUTORIZACIÓN - EMPRESA
   ├─> Verificar que usuario pertenece a empresa
   ├─> Implementación: Middleware CheckEmpresaActiva
   └─> Verifica: Empresa existe, está activa, usuario tiene acceso

3. AUTORIZACIÓN - MÓDULO
   ├─> Verificar que empresa tiene acceso a módulo
   ├─> Implementación: Middleware CheckModulo
   └─> Verifica: modulo_empresa.modulo_id exists

4. AUTORIZACIÓN - ROLES
   ├─> Verificar que usuario tiene rol específico
   ├─> Implementación: Middleware CheckRol
   └─> Verifica: user.rol_id == $requiredRolId

5. FILTRADO DE DATOS
   ├─> Global Scope: EmpresaScope
   ├─> Implementación: BaseModel::booted()
   └─> Todas las queries WHERE empresa_id = session('empresa_id')

FLUJO COMBINADO:
Request
  ├─ ¿Usuario autenticado? (Auth middleware)
  ├─ ¿Empresa existe? (CheckEmpresaActiva)
  ├─ ¿Usuario tiene acceso a empresa? (CheckEmpresaActiva)
  ├─ ¿Usuario tiene rol? (CheckRol - si aplica)
  ├─ ¿Empresa tiene módulo? (CheckModulo - si aplica)
  └─ ¿Datos pertenecen a empresa_id? (EmpresaScope en queries)
```

---

## 📈 Métricas de Rendimiento

**Objetivos (Target Phase 1):**
- Tiempo promedio de request: < 200ms
- Cobertura de tests: 85%+
- Query efficiency: Máximo 5 queries por página
- Cache hit rate: 70%+

**Monitoreo Actual:**
- Log streaming: `php artisan pail`
- Query debugging: `DB::enableQueryLog()` en Tinker

---

## 🛤️ Roadmap de Evolución

```
ACTUAL (Phase 0)         → PHASE 1 (API)           → PHASE 2+ (Features)
├─ Monolítico            ├─ REST API               ├─ Facturación Electrónica
├─ Blade only            ├─ API Resources          ├─ Nómina Electrónica
├─ Fat controllers       ├─ Sanctum auth           ├─ Contabilidad
└─ Minimal tests         └─ OpenAPI docs           └─ Auditoría

ARQUITECTURA EVOLUCIÓN:
Controllers (fat)        → Actions + Services (thin)
Models (fat)             → Models (lean) + Value Objects
No layers                → Clear Domain layers
Blade views              → Blade + REST API + Future: Vue/React
No tests                 → 85%+ coverage with Pest
```

---

## 📚 Referencias

- **CLAUDE.md:** Guía de desarrollo y setup
- **AUDIT_CODIGO.md:** Detalles de deuda técnica
- **PLAN_EJECUCION.md:** Roadmap de 20 semanas
- **ARCHITECTURE.puml:** Diagrama visual (PlantUML)

---

**Próxima Actualización:** Después de Phase 1 refactor  
**Última revisión:** 2026-06-24  
**Responsable:** Equipo Dinastía
