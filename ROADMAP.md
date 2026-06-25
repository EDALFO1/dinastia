# DINASTÍA - Roadmap de Escalamiento a Plataforma Empresarial
**Objetivo:** Convertir Dinastía en una plataforma profesional tipo Alegra/SIIGO con Facturación Electrónica, Nómina Electrónica y Contabilidad integradas con DIAN.

**Timeline:** 20 semanas | 20 horas/semana | Calidad profesional desde día 1

---

## 📋 SECUENCIA MAESTRO - FASES Y SEMANAS

### **FASE 0: PREPARACIÓN Y ARQUITECTURA (Semanas 1-2) - 20 horas**
**Objetivo:** Refactor arquitectónico, setup profesional, cimientos sólidos

#### Semana 1: Documentación y Análisis Profundo (10 horas)
- [ ] Crear CLAUDE.md con documentación del proyecto completa
- [ ] Auditoría de código: migración de código legacy
- [ ] Crear diagrama de arquitectura actual
- [ ] Documentar decisiones de diseño
- [ ] Crear guía de contribución
- [ ] **CHECKPOINT:** Revisión completa de documentación y arquitectura

#### Semana 2: Refactor Modular y Setup (10 horas)
- [ ] Reorganizar estructura en `app/Domains/`
- [ ] Crear Domains: Payroll, Invoicing, Accounting, DIAN, Shared
- [ ] Refactor BaseModel y scopes
- [ ] Setup PHPUnit + Pest para testing
- [ ] Crear factories y seeders de prueba
- [ ] Configurar GitHub Actions CI/CD básico
- [ ] **CHECKPOINT:** Todos los tests pasando, código pasando análisis estático

---

### **FASE 1: API REST MODERNA (Semanas 3-5) - 30 horas**
**Objetivo:** Desacoplar backend del frontend, permitir integraciones futuras

#### Semana 3: Autenticación y Estructura API (10 horas)
- [ ] Implementar Laravel Sanctum (autenticación API)
- [ ] Crear estructura de Controllers/Api
- [ ] Desarrollar endpoints básicos de autenticación
- [ ] Implementar rate limiting
- [ ] Crear middleware de autorización por módulo
- [ ] Tests para endpoints de auth
- [ ] **CHECKPOINT:** Autenticación funcionando, tests al 100%

#### Semana 4: API Resources y Documentación (10 horas)
- [ ] Crear API Resources para cada modelo principal
- [ ] Implementar paginación, filtros, búsqueda
- [ ] Documentación OpenAPI (Swagger)
- [ ] CRUD endpoints para módulos existentes
- [ ] Tests de endpoints CRUD
- [ ] **CHECKPOINT:** API documentada y testeable

#### Semana 5: Refinamiento y Performance (10 horas)
- [ ] Query optimization (eager loading, índices)
- [ ] Caching con Redis
- [ ] Versionado de API (/api/v1/)
- [ ] Error handling standardizado
- [ ] Logs estructurados
- [ ] Load testing básico
- [ ] **CHECKPOINT:** API stable, performance < 200ms por endpoint

---

### **FASE 2: FACTURACIÓN ELECTRÓNICA (Semanas 6-10) - 50 horas**
**Objetivo:** Emisión de facturas electrónicas válidas para DIAN

#### Semana 6: Modelos y Validaciones (10 horas)
- [ ] Modelo Invoice (factura) con todos los campos DIAN
- [ ] Modelo InvoiceLineItem (detalles de líneas)
- [ ] Modelo InvoiceTax (impuestos: IVA, INC, etc)
- [ ] Validaciones según DIAN (RUC, NIT, códigos)
- [ ] Modelos para Resolución de facturación
- [ ] Tests de validaciones (300+ casos)
- [ ] **CHECKPOINT:** Todos los modelos con validaciones pasando tests

#### Semana 7: Generación XML y Firma (10 horas)
- [ ] Generador de XML UBL 2.1 según estándar DIAN
- [ ] Integración con certificado digital
- [ ] Firma XmlDSig
- [ ] Validación XSD del XML generado
- [ ] Tests de generación XML (ejemplos DIAN)
- [ ] **CHECKPOINT:** XML válido según XSD DIAN, firmado correctamente

#### Semana 8: Integración DIAN API (10 horas)
- [ ] Cliente HTTP para DIAN Web Services
- [ ] Envío de factura a DIAN
- [ ] Manejo de acuse de recibo
- [ ] Reintentos y rollback
- [ ] Logging de transacciones
- [ ] Tests con ejemplos DIAN (sandbox)
- [ ] **CHECKPOINT:** Factura enviada a DIAN y recibida correctamente

#### Semana 9: Reportes y Documentos (10 horas)
- [ ] Generación PDF de factura
- [ ] Generar comprobante de entrega (Remisión)
- [ ] Nota de crédito y débito
- [ ] Libro de ventas (reporte DIAN)
- [ ] Bitácora de facturación
- [ ] Tests de reportes
- [ ] **CHECKPOINT:** Reportes completos generando y descargables

#### Semana 10: Pruebas Exhaustivas (10 horas)
- [ ] Casos de prueba: facturas simples, complejas, con descuentos, impuestos
- [ ] Testing de errores y excepciones
- [ ] Performance de generación XML
- [ ] Documentación de API de facturación
- [ ] Manual de usuario para facturación
- [ ] **CHECKPOINT:** 95%+ cobertura de tests, documentación completa

---

### **FASE 3: NÓMINA ELECTRÓNICA (Semanas 11-13) - 30 horas**
**Objetivo:** Completar y mejorar módulo de nómina con integración DIAN

#### Semana 11: Mejora de Modelos de Nómina (10 horas)
- [ ] Completar Payroll domain (refactor Recibo existente)
- [ ] Modelos: Concepto, Novedad, Deducción, Aporte
- [ ] Cálculos: salario, aportes, retenciones, neto
- [ ] Validaciones colombianas (salario mínimo, etc)
- [ ] Tests de cálculos (casos reales colombianos)
- [ ] **CHECKPOINT:** Cálculos correctos verificados contra ejemplos reales

#### Semana 12: Nómina Electrónica XML (10 horas)
- [ ] Generador XML nómina electrónica (formato DIAN)
- [ ] Validación de estructura
- [ ] Firma electrónica de nómina
- [ ] Envío a DIAN
- [ ] Manejo de respuestas
- [ ] Tests
- [ ] **CHECKPOINT:** Nómina XML válida en DIAN

#### Semana 13: Reportes y Procesos (10 horas)
- [ ] PDF de recibo de nómina
- [ ] Reporte 347 (aportes)
- [ ] Certificados de aporte (PILA)
- [ ] Procesos: cierre de nómina, liquidación
- [ ] Tests
- [ ] **CHECKPOINT:** Ciclo completo de nómina funcionando

---

### **FASE 4: MÓDULO DE CONTABILIDAD (Semanas 14-18) - 50 horas**
**Objetivo:** Sistema contable integrado con facturación y nómina

#### Semana 14: Plan de Cuentas y Configuración (10 horas)
- [ ] Modelo ChartOfAccounts (Plan de cuentas)
- [ ] Catálogo estándar PUC (Plan Único de Cuentas Colombia)
- [ ] Configuración de cuenta por empresa
- [ ] Jerarquía de cuentas (activo, pasivo, patrimonio, etc)
- [ ] Seed con PUC completo
- [ ] Tests
- [ ] **CHECKPOINT:** Plan de cuentas cargado y validado

#### Semana 15: Asientos Contables (10 horas)
- [ ] Modelo JournalEntry (asiento)
- [ ] Modelo JournalLine (línea de asiento)
- [ ] Lógica de débito/crédito
- [ ] Validaciones (debe = haber)
- [ ] Reversión de asientos
- [ ] Tests exhaustivos
- [ ] **CHECKPOINT:** Asientos creándose correctamente, balanceados

#### Semana 16: Mayor y Reportes (10 horas)
- [ ] Modelo Ledger (mayor)
- [ ] Reporte: Balance de prueba
- [ ] Reporte: Balance General
- [ ] Reporte: Estado de Resultados
- [ ] Reporte: Flujo de Caja
- [ ] Análisis: Ratios financieros
- [ ] Tests
- [ ] **CHECKPOINT:** Reportes financieros correctos

#### Semana 17: Integración Automática (10 horas)
- [ ] Asientos automáticos desde facturación (venta = cuentas por cobrar + ingreso)
- [ ] Asientos automáticos desde nómina (gasto de nómina)
- [ ] Asientos automáticos de pagos
- [ ] Conciliación bancaria
- [ ] Tests de integración
- [ ] **CHECKPOINT:** Asientos generándose automáticamente sin errores

#### Semana 18: Reportes Avanzados (10 horas)
- [ ] Análisis horizontal y vertical
- [ ] Presupuestos vs Actual
- [ ] Proyecciones
- [ ] Auditoría de asientos (quién, cuándo, qué cambió)
- [ ] Exportación a Excel/PDF
- [ ] Tests
- [ ] **CHECKPOINT:** Suite completa de reportes funcional

---

### **FASE 5: AUDITORÍA Y SEGURIDAD (Semanas 19-20) - 20 horas**
**Objetivo:** Garantizar trazabilidad e integridad de datos financieros

#### Semana 19: Sistema de Auditoría (10 horas)
- [ ] Tabla audit_logs (quién, qué, cuándo, por qué)
- [ ] Triggers en tablas críticas (facturas, nóminas, asientos)
- [ ] Visualización de historial de cambios
- [ ] Reporte de cambios por usuario/fecha
- [ ] Inmutabilidad de logs (append-only)
- [ ] Tests
- [ ] **CHECKPOINT:** Auditoría funcionando en todas las tablas críticas

#### Semana 20: Security, Performance y Go-Live (10 horas)
- [ ] Audit de seguridad OWASP
- [ ] Testing de permisos y roles
- [ ] Validación de datos de entrada en todos lados
- [ ] Rate limiting en endpoints críticos
- [ ] Backup y recovery procedures
- [ ] Documentación de deployment
- [ ] Tests finales
- [ ] **CHECKPOINT:** Listo para producción

---

## 🔍 CHECKPOINTS DE REVISIÓN SEMANAL

**Cada viernes:**
1. ✅ Todos los tests pasando
2. ✅ Cobertura de código > 85%
3. ✅ Código sin warnings de Pint/PHPStan
4. ✅ Documentación actualizada
5. ✅ Changeset documentado
6. ✅ Demo de feature funcionando

**Cada 2 semanas:**
- Review de arquitectura y decisiones
- Performance testing
- Security review
- Refactor si es necesario

---

## 🛠️ STACK TÉCNICO FINAL

```
Backend:
- Laravel 12 (PHP 8.3+)
- PostgreSQL (upgraded de MySQL)
- Redis (caching y queue)
- Elasticsearch (búsqueda avanzada - opcional)

Frontend:
- Blade templates + Tailwind 4 (mantener)
- Alpine.js para interactividad
- Vite bundler

API:
- REST con Sanctum
- OpenAPI/Swagger docs
- Versionado (/api/v1/)

Testing:
- PHPUnit + Pest
- Factory + Seeder
- Load testing (Apache JMeter)

DevOps:
- Docker + Compose
- GitHub Actions (CI/CD)
- Deployment: VPS o AWS
```

---

## 📊 ESTIMACIONES FINALES

| Fase | Semanas | Horas | Complejidad |
|------|---------|-------|------------|
| 0: Preparación | 2 | 20 | ⭐⭐ |
| 1: API REST | 3 | 30 | ⭐⭐⭐ |
| 2: Facturación | 5 | 50 | ⭐⭐⭐⭐⭐ |
| 3: Nómina | 3 | 30 | ⭐⭐⭐⭐ |
| 4: Contabilidad | 5 | 50 | ⭐⭐⭐⭐⭐ |
| 5: Auditoría | 2 | 20 | ⭐⭐⭐ |
| **TOTAL** | **20** | **200** | |

---

## 🚀 PRÓXIMOS PASOS INMEDIATOS

**Esta semana (Semanas 1-2):**
1. Crear CLAUDE.md con documentación
2. Hacer auditoría de código
3. Crear diagrama de arquitectura
4. Comenzar refactor a estructura modular

**Repositorio:** https://github.com/EDALFO1/dinastia

---

**Última actualización:** 2026-06-23
**Responsable:** Claude + Usuario
**Estado:** 🟢 INICIANDO

