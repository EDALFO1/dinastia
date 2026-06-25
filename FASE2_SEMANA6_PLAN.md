# FASE 2 - Semana 6: Facturación Electrónica - Modelos y Validaciones

## 🎯 Objetivo
Implementar la capa de modelos, validaciones DIAN y configuraciones base para facturación electrónica en Colombia.

**Timeline:** 10 horas (2 horas/día)  
**Deadline:** EOW 2026-07-02  
**Status:** 🟡 PENDIENTE

---

## 📋 Tareas Desglosadas

### Tarea 1: Crear Domain para Invoicing (2 horas)
**Objetivo:** Estructurar la arquitectura para facturación

```bash
Crear estructura de carpetas:
app/Domains/Invoicing/
├── Models/
│   ├── Invoice.php
│   ├── InvoiceLineItem.php
│   ├── InvoiceTax.php
│   └── InvoiceSequence.php (para resoluciones)
├── Controllers/
│   └── Api/
│       └── InvoiceApiController.php
├── Services/
│   ├── InvoiceValidator.php
│   ├── DianValidator.php
│   └── InvoiceNumberGenerator.php
├── Enums/
│   ├── InvoiceType.php
│   ├── DocumentType.php
│   ├── TaxType.php
│   └── UnitType.php
├── Requests/
│   └── StoreInvoiceRequest.php
└── Resources/
    └── InvoiceResource.php
```

**Entregables:**
- ✅ Estructura de carpetas creada
- ✅ BaseModel extendido para Invoice (multi-tenant)
- ✅ Relaciones iniciales (Empresa → Invoice)

**Tests:** 3 tests de estructura y relaciones

---

### Tarea 2: Crear Modelo Invoice (2.5 horas)
**Objetivo:** Implementar tabla y modelo para facturas

**Schema:**
```sql
invoices
├── id (PK)
├── empresa_id (FK, multi-tenant)
├── numero (unique per empresa+resolucion)
├── resolucion_id (FK, referencia resolución DIAN)
├── tipo_documento (enum: FACTURA, NC, ND)
├── cliente_nit (string, validar NIT)
├── cliente_nombre (string)
├── fecha_emision (date)
├── fecha_vencimiento (date)
├── subtotal (decimal 14,2)
├── descuento (decimal 14,2, default 0)
├── total_impuestos (decimal 14,2)
├── total (decimal 14,2)
├── observaciones (text, nullable)
├── estado (enum: BORRADOR, ENVIADA, ACEPTADA, RECHAZADA, ANULADA)
├── xml_factura (longtext, nullable)
├── firma_digital (text, nullable)
├── uuid_dian (string, unique, nullable)
├── timestamps (created_at, updated_at)
```

**Relaciones:**
- belongsTo: Empresa (multi-tenant)
- belongsTo: InvoiceSequence (para número secuencial)
- hasMany: InvoiceLineItem
- hasMany: InvoiceTax

**Enums a crear:**
- `InvoiceType`: FACTURA, NOTA_CREDITO, NOTA_DEBITO
- `DocumentType`: CEDULA, NIT, PASAPORTE, DOCUMENTO_EXTRANJERO
- `InvoiceStatus`: BORRADOR, ENVIADA, ACEPTADA, RECHAZADA, ANULADA

**Tests:** 5 tests (modelo, relaciones, casts, fillable)

---

### Tarea 3: Crear InvoiceLineItem (2 horas)
**Objetivo:** Implementar líneas de factura

**Schema:**
```sql
invoice_line_items
├── id (PK)
├── empresa_id (FK, multi-tenant)
├── invoice_id (FK)
├── linea_numero (int)
├── descripcion (string)
├── cantidad (decimal 10,4)
├── unidad (enum: UNIDAD, KILOGRAMO, METRO, etc)
├── valor_unitario (decimal 12,2)
├── descuento (decimal 12,2, default 0)
├── valor_linea (decimal 14,2) -- calculado
├── timestamps
```

**Validaciones:**
- cantidad > 0
- valor_unitario >= 0
- descuento <= valor_bruto
- unidad en lista DIAN

**Tests:** 4 tests (cálculo de valor_linea, validaciones)

---

### Tarea 4: Crear InvoiceTax (1.5 horas)
**Objetivo:** Implementar impuestos por línea

**Schema:**
```sql
invoice_taxes
├── id (PK)
├── empresa_id (FK, multi-tenant)
├── invoice_id (FK)
├── invoice_line_item_id (FK, nullable)
├── tipo_impuesto (enum: IVA, IMPUESTO_CONSUMO, IMPUESTO_NACIONAL)
├── porcentaje (decimal 5,2)
├── base (decimal 14,2)
├── valor (decimal 14,2)
├── timestamps
```

**Validaciones:**
- porcentaje en rango legal DIAN (0-100)
- base > 0
- valor = base * porcentaje / 100

**Tests:** 3 tests (cálculos, validaciones)

---

### Tarea 5: Crear InvoiceSequence (1 hora)
**Objetivo:** Gestionar secuencias de números DIAN

**Schema:**
```sql
invoice_sequences
├── id (PK)
├── empresa_id (FK, multi-tenant)
├── numero_resolucion (string, unique)
├── tipo_factura (enum)
├── rango_inicio (bigint)
├── rango_fin (bigint)
├── proximo_numero (bigint)
├── fecha_vigencia_inicio (date)
├── fecha_vigencia_fin (date)
├── estado (enum: ACTIVA, VENCIDA, SUSPENDIDA)
├── timestamps
```

**Métodos:**
- `getNextNumber()`: Obtiene próximo número con lock pessimista
- `isActive()`: Valida si la resolución está vigente
- `getRangeStatus()`: Retorna % de rango usado

**Tests:** 4 tests (concurrencia, validaciones de rango)

---

### Tarea 6: DianValidator Service (2 horas)
**Objetivo:** Validaciones específicas de regulaciones colombianas

**Validaciones DIAN:**
```php
- validarNit($nit): Algoritmo módulo 11
- validarCedula($cedula): Rangos y formato
- validarReferenciaTributaria($ref): Formato DIAN
- validarCodigoProducto($codigo): Catálogo de productos
- validarUnidadMedida($unidad): Lista oficial DIAN
- validarTipoImpuesto($tipo): IVA, Consumo, Nacional
- validarPorcentajeIVA($pct): 0%, 5%, 19% (actual en CO)
```

**Tests:** 12 tests (validadores DIAN)

---

### Tarea 7: InvoiceValidator Service (1 hora)
**Objetivo:** Validaciones de negocio antes de envío a DIAN

```php
- validarIntegridad(): Sumas coinciden, líneas > 0
- validarDocumento(): NIT cliente válido
- validarFechas(): Vencimiento >= emisión
- validarResolucion(): Dentro de rango y vigencia
- validarDetalles(): Cada línea + impuestos válidos
```

**Tests:** 8 tests (flujos de validación)

---

### Tarea 8: Factory y Seeders (1 hora)
**Objetivo:** Test data y demo

**Factories:**
- `InvoiceFactory.php` - Facturas con líneas y impuestos
- `InvoiceLineItemFactory.php`
- `InvoiceTaxFactory.php`
- `InvoiceSequenceFactory.php`

**Tests:** 3 tests (factories generan datos válidos)

---

## 📊 Resumen Tareas

| Tarea | Componente | Horas | Tests | Estado |
|-------|-----------|-------|-------|--------|
| 1 | Dominio Invoicing | 2.0 | 3 | ⏳ |
| 2 | Modelo Invoice | 2.5 | 5 | ⏳ |
| 3 | InvoiceLineItem | 2.0 | 4 | ⏳ |
| 4 | InvoiceTax | 1.5 | 3 | ⏳ |
| 5 | InvoiceSequence | 1.0 | 4 | ⏳ |
| 6 | DianValidator | 2.0 | 12 | ⏳ |
| 7 | InvoiceValidator | 1.0 | 8 | ⏳ |
| 8 | Factories/Seeders | 1.0 | 3 | ⏳ |
| **TOTAL** | | **13.0** | **42** | |

---

## ✅ Criterios de Aceptación

**Código:**
- ✅ 8 modelos + migrations
- ✅ 2 services con 20+ métodos validadores
- ✅ 3 enums DIAN
- ✅ 4 factories funcionales
- ✅ 42 tests pasando (>90% cobertura)
- ✅ Sin warnings de Pint/PHPStan

**Documentación:**
- ✅ README.md de facturación con ejemplos
- ✅ Especificación de APIs (estándar DIAN)

**Demo:**
- ✅ Poder crear factura con líneas e impuestos
- ✅ Validaciones DIAN funcionando
- ✅ Seeders con datos reales

---

## 🚀 Siguientes Pasos (Semana 7)

Semana 7: Generación XML y Firma Digital
- Implementar UBL 2.1 XML generator
- Integrar firma XmlDSig
- Tests de validación XSD vs DIAN

---

**Creado:** 2026-06-25  
**Responsable:** EDALFO1  
**Status:** Inicio Semana 6 🟡
