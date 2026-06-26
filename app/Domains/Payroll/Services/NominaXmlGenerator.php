<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Models\NominaElectronica;
use DOMDocument;
use DOMElement;

class NominaXmlGenerator
{
    protected DOMDocument $doc;
    protected NominaElectronica $nomina;

    public function __construct()
    {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
    }

    /**
     * Generar XML de nómina electrónica (DIAN UBL 2.1)
     */
    public function generate(NominaElectronica $nomina): string
    {
        $this->nomina = $nomina;

        // Elemento raíz
        $payroll = $this->doc->createElement('Payroll');
        $payroll->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Payroll-2');
        $payroll->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $payroll->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $payroll->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');

        $this->doc->appendChild($payroll);

        // Información general
        $this->addPayrollInfo($payroll);

        // Información del empleador
        $this->addPayerInfo($payroll);

        // Información del empleado
        $this->addEmployeeInfo($payroll);

        // Período de pago
        $this->addPaymentPeriodInfo($payroll);

        // Detalles de pago
        $this->addPaymentDetails($payroll);

        // Totales
        $this->addTotals($payroll);

        return $this->doc->saveXML();
    }

    /**
     * Información general de la nómina
     */
    private function addPayrollInfo(DOMElement $parent): void
    {
        $cbc = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';

        $id = $this->doc->createElementNS($cbc, 'cbc:ID', $this->nomina->numero_nomina);
        $parent->appendChild($id);

        $issueDate = $this->doc->createElementNS($cbc, 'cbc:IssueDate', $this->nomina->fecha_emision->format('Y-m-d'));
        $parent->appendChild($issueDate);

        $issueTime = $this->doc->createElementNS($cbc, 'cbc:IssueTime', now()->format('H:i:s'));
        $parent->appendChild($issueTime);

        $note = $this->doc->createElementNS($cbc, 'cbc:Note', 'Nómina Electrónica');
        $parent->appendChild($note);
    }

    /**
     * Información del empleador (DIAN)
     */
    private function addPayerInfo(DOMElement $parent): void
    {
        $cac = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
        $cbc = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';

        $payer = $this->doc->createElementNS($cac, 'cac:PayerParty');

        // NIT del empleador
        $partyID = $this->doc->createElementNS($cbc, 'cbc:PartyID', $this->nomina->empresa->nit);
        $payer->appendChild($partyID);

        // Nombre del empleador
        $partyName = $this->doc->createElementNS($cbc, 'cbc:PartyName', $this->nomina->empresa->nombre);
        $payer->appendChild($partyName);

        // Dirección
        $address = $this->doc->createElementNS($cbc, 'cbc:Address', $this->nomina->empresa->direccion);
        $payer->appendChild($address);

        $parent->appendChild($payer);
    }

    /**
     * Información del empleado
     */
    private function addEmployeeInfo(DOMElement $parent): void
    {
        $cac = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
        $cbc = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';

        $employee = $this->doc->createElementNS($cac, 'cac:Employee');

        $afiliado = $this->nomina->recibo->afiliado;

        // Cédula del empleado
        $id = $this->doc->createElementNS($cbc, 'cbc:ID', $afiliado->documento);
        $employee->appendChild($id);

        // Nombre del empleado
        $name = $this->doc->createElementNS($cbc, 'cbc:Name', $afiliado->nombre);
        $employee->appendChild($name);

        $parent->appendChild($employee);
    }

    /**
     * Período de pago
     */
    private function addPaymentPeriodInfo(DOMElement $parent): void
    {
        $cac = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
        $cbc = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';

        $period = $this->doc->createElementNS($cac, 'cac:PaymentPeriod');

        $startDate = $this->doc->createElementNS($cbc, 'cbc:StartDate', $this->nomina->periodo_pago_inicio->format('Y-m-d'));
        $period->appendChild($startDate);

        $endDate = $this->doc->createElementNS($cbc, 'cbc:EndDate', $this->nomina->periodo_pago_final->format('Y-m-d'));
        $period->appendChild($endDate);

        $parent->appendChild($period);
    }

    /**
     * Detalles de pago
     */
    private function addPaymentDetails(DOMElement $parent): void
    {
        $cac = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
        $cbc = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';

        // Devengado
        $devengado = $this->doc->createElementNS($cac, 'cac:PaymentDetails');
        $label = $this->doc->createElementNS($cbc, 'cbc:Label', 'Devengado');
        $amount = $this->doc->createElementNS($cbc, 'cbc:Amount', number_format($this->nomina->total_devengado, 2, '.', ''));
        $devengado->appendChild($label);
        $devengado->appendChild($amount);
        $parent->appendChild($devengado);

        // Descuentos
        $descuentos = $this->doc->createElementNS($cac, 'cac:PaymentDetails');
        $label = $this->doc->createElementNS($cbc, 'cbc:Label', 'Descuentos');
        $amount = $this->doc->createElementNS($cbc, 'cbc:Amount', number_format($this->nomina->total_descuentos, 2, '.', ''));
        $descuentos->appendChild($label);
        $descuentos->appendChild($amount);
        $parent->appendChild($descuentos);
    }

    /**
     * Totales
     */
    private function addTotals(DOMElement $parent): void
    {
        $cac = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
        $cbc = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';

        $totals = $this->doc->createElementNS($cac, 'cac:PaymentTerms');

        $netAmount = $this->doc->createElementNS($cbc, 'cbc:Amount', number_format($this->nomina->neto_pagar, 2, '.', ''));
        $totals->appendChild($netAmount);

        $parent->appendChild($totals);
    }
}
