# FASE 2 - Semana 7: Facturación Electrónica - Generación XML y Firma Digital

## 🎯 Objetivo
Implementar generación de XML UBL 2.1 y firma digital XmlDSig para facturas electrónicas colombianas.

**Timeline:** 10 horas (2 horas/día)  
**Deadline:** EOW 2026-07-09  
**Status:** 🟡 PENDIENTE

---

## 📋 Tareas Desglosadas

### Tarea 1: Crear Servicios Base de XML Generator (2 horas)
**Objetivo:** Estructura base para generación de XML UBL 2.1

**Archivos:**
- `app/Domains/Invoicing/Services/XmlBuilder.php` - Clase base
- `app/Domains/Invoicing/Services/UblNamespaces.php` - Constantes de namespaces

**Responsabilidades:**
- Crear DOM Document con estructura UBL 2.1
- Métodos para agregar elementos comunes (Invoice, AccountingSupplier, etc)
- Soporte para namespaces SOAP, CAC, CBC, DS

**Tests:** 3 tests (documento base, namespaces, estructura)

---

### Tarea 2: Implementar Generador de XML para Invoice (2.5 horas)
**Objetivo:** Convertir modelo Invoice a XML UBL 2.1 válido

**Métodos:**
- `generate(Invoice $invoice): string` - XML completo
- `addInvoiceHeader()` - Encabezado UBL
- `addSupplier()` - Información del proveedor (empresa)
- `addCustomer()` - Información del cliente
- `addLineItems()` - Líneas de factura
- `addTaxes()` - Información de impuestos
- `addMonetaryTotals()` - Totales

**Estructura UBL esperada:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="...UBL..." xmlns:cac="...CAC..." xmlns:cbc="...CBC...">
  <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
  <cbc:CustomizationID>05</cbc:CustomizationID>
  <cbc:ProfileID>DIAN 2.1</cbc:ProfileID>
  <!-- Header info -->
  <cac:AccountingSupplierParty>...</cac:AccountingSupplierParty>
  <cac:AccountingCustomerParty>...</cac:AccountingCustomerParty>
  <!-- Line items -->
  <cac:InvoiceLine>...</cac:InvoiceLine>
  <!-- Monetary totals -->
  <cac:LegalMonetaryTotal>...</cac:LegalMonetaryTotal>
</Invoice>
```

**Tests:** 5 tests (header, supplier, customer, lines, taxes)

---

### Tarea 3: Implementar Firma Digital XmlDSig (2 horas)
**Objetivo:** Firmar XML con certificado digital

**Servicios:**
- `app/Domains/Invoicing/Services/XmlSigner.php`

**Métodos:**
- `sign(string $xml, string $certificatePath, string $privateKeyPassword): string`
- `validate(string $signedXml): bool`
- `getSignatureInfo(string $signedXml): array` - Información de la firma

**Flujo:**
1. Cargar certificado X.509
2. Crear firma con RSA-SHA256
3. Agregar KeyInfo (referencia al certificado)
4. Insertar firma antes de cierre del elemento raíz

**Tests:** 4 tests (firmar, validar, info firma, error sin certificado)

---

### Tarea 4: Validación XSD contra Esquema DIAN (1.5 horas)
**Objetivo:** Validar XML generado contra esquema XSD oficial

**Servicio:**
- `app/Domains/Invoicing/Services/XmlValidator.php`

**Métodos:**
- `validateAgainstSchema(string $xml, string $schemaPath): bool`
- `getValidationErrors(): array`

**Schemas a soportar:**
- UBL 2.1 Invoice XSD (desde DIAN)
- Namespaces validation

**Tests:** 3 tests (validación correcta, error esquema, error estructura)

---

### Tarea 5: Servicio de Certificados (1 hora)
**Objetivo:** Gestión de certificados digitales

**Servicio:**
- `app/Domains/Invoicing/Services/CertificateManager.php`

**Métodos:**
- `loadCertificate(string $path, string $password): array`
- `validateCertificate(array $cert): bool`
- `getCertificateInfo(array $cert): array` - Emisor, sujeto, fechas válidas

**Tests:** 2 tests (cargar certificado, validar fechas)

---

### Tarea 6: Actualizar Invoice Model (1 hora)
**Objetivo:** Agregar métodos helper para XML generation

**Métodos en Invoice:**
- `toXml(): string` - Genera XML usando XmlBuilder
- `toSignedXml(string $certPath, string $password): string`
- `getXmlStatus()` - Retorna estado: 'pending', 'generated', 'signed'

**Relaciones:**
- Agregar columnas en migration: `xml_factura`, `firma_digital`, `xml_status`

**Tests:** 2 tests (toXml, toSignedXml)

---

### Tarea 7: API Resources y Partial Controller (1 hora)
**Objetivo:** Preparar endpoints para XML/firma

**Archivos:**
- `app/Http/Resources/InvoiceXmlResource.php` - JSON con XML embedded
- `app/Domains/Invoicing/Controllers/Api/InvoiceXmlController.php` - Endpoints XML

**Endpoints:**
- `GET /api/v1/invoices/{id}/xml` - Retorna XML
- `GET /api/v1/invoices/{id}/xml/signed` - Retorna XML firmado
- `POST /api/v1/invoices/{id}/sign` - Firma factura (si tiene certificado)

**Tests:** 2 tests (get xml, sign endpoint)

---

### Tarea 8: Tests Integrados y Ejemplos DIAN (1.5 horas)
**Objetivo:** Tests realistas con datos de ejemplo DIAN

**Test Suites:**
- `InvoiceXmlGenerationTest` (5 tests)
  - Generate valid XML for factura
  - Generate NC (nota crédito)
  - Generate ND (nota débito)
  - Validates against XSD
  - Signature verification

- `XmlSigningIntegrationTest` (3 tests)
  - End-to-end: create → generate XML → sign
  - Verify signed XML is valid
  - Extract signature info

**Ejemplos DIAN:**
- Fixture con XML de ejemplo real de DIAN
- Test data con factura realista (líneas, impuestos, totales)

**Tests:** 8 tests

---

## 📊 Resumen Tareas

| Tarea | Componente | Horas | Tests | Estado |
|-------|-----------|-------|-------|--------|
| 1 | XML Builder Base | 2.0 | 3 | ⏳ |
| 2 | Invoice XML Generator | 2.5 | 5 | ⏳ |
| 3 | XmlDSig Signer | 2.0 | 4 | ⏳ |
| 4 | XSD Validator | 1.5 | 3 | ⏳ |
| 5 | Certificate Manager | 1.0 | 2 | ⏳ |
| 6 | Invoice Model Updates | 1.0 | 2 | ⏳ |
| 7 | API Resources/Controller | 1.0 | 2 | ⏳ |
| 8 | Integration Tests | 1.5 | 8 | ⏳ |
| **TOTAL** | | **13.5** | **29** | |

---

## ✅ Criterios de Aceptación

**Código:**
- ✅ 5 servicios implementados (XmlBuilder, XmlSigner, XmlValidator, CertificateManager, InvoiceXmlController)
- ✅ XML válido según UBL 2.1 (estructura completa)
- ✅ Firma digital funcional (RSA-SHA256)
- ✅ Validación XSD contra esquema DIAN
- ✅ 29 tests pasando (90%+ coverage)
- ✅ Sin warnings de Pint/PHPStan

**Documentación:**
- ✅ README.md de XML generation con ejemplos
- ✅ Guía de certificados digitales
- ✅ Especificación UBL 2.1 customizado

**Demo:**
- ✅ Poder generar XML válido desde Invoice modelo
- ✅ Poder firmar XML con certificado de prueba
- ✅ Validar firma en XML firmado
- ✅ Endpoint para obtener XML/XML firmado

---

## 🚀 Siguientes Pasos (Semana 8)

Semana 8: Integración DIAN API
- Implementar cliente HTTP para DIAN
- Envío de factura XML a DIAN
- Manejo de respuestas y acuses de recibo
- Reintentos y logging

---

## 📦 Dependencias Externas

Necesarias:
```
composer require "sabre/xml:^3.2"
composer require "samuelcouch/xml-dsa:^0.2"  # o ext-soap
```

Test Certificates:
- Generaré certificados auto-firmados para testing
- Path: `storage/app/certificates/test/` 

---

**Creado:** 2026-06-25  
**Responsable:** EDALFO1  
**Status:** Inicio Semana 7 🟡
