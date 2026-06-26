<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Models\NominaElectronica;
use App\Models\Recibo;
use DOMDocument;
use DOMElement;

class NotaCreditoDebitoGenerator
{
    protected DOMDocument $doc;
    protected NominaElectronica $nomina;
    protected string $tipo; // 'credito' o 'debito'
    protected string $razonAjuste;
    protected float $monto;

    public function __construct()
    {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
    }

    /**
     * Generar XML de nota de crédito
     */
    public function generarNotaCredito(NominaElectronica $nomina, string $razonAjuste, float $monto): string
    {
        $this->nomina = $nomina;
        $this->tipo = 'credito';
        $this->razonAjuste = $razonAjuste;
        $this->monto = $monto;

        return $this->generarXml();
    }

    /**
     * Generar XML de nota de débito
     */
    public function generarNotaDebito(NominaElectronica $nomina, string $razonAjuste, float $monto): string
    {
        $this->nomina = $nomina;
        $this->tipo = 'debito';
        $this->razonAjuste = $razonAjuste;
        $this->monto = $monto;

        return $this->generarXml();
    }

    /**
     * Generar XML
     */
    private function generarXml(): string
    {
        $cac = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
        $cbc = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';

        // Elemento raíz
        $nota = $this->doc->createElement('CreditDebitNote');
        $nota->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:CreditDebitNote-2');
        $nota->setAttribute('xmlns:cbc', $cbc);
        $nota->setAttribute('xmlns:cac', $cac);
        $nota->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');

        $this->doc->appendChild($nota);

        // Tipo de nota
        $tipoNota = $this->doc->createElementNS($cbc, 'cbc:CreditDebitNoteType', $this->tipo === 'credito' ? '91' : '92');
        $nota->appendChild($tipoNota);

        // ID
        $id = $this->doc->createElementNS($cbc, 'cbc:ID', 'NC' . $this->nomina->numero_nomina . '_' . now()->format('YmdHis'));
        $nota->appendChild($id);

        // Fecha de emisión
        $fecha = $this->doc->createElementNS($cbc, 'cbc:IssueDate', now()->format('Y-m-d'));
        $nota->appendChild($fecha);

        // Hora
        $hora = $this->doc->createElementNS($cbc, 'cbc:IssueTime', now()->format('H:i:s'));
        $nota->appendChild($hora);

        // Referencia a nómina original
        $refInv = $this->doc->createElementNS($cac, 'cac:BillingReference');
        $refInvId = $this->doc->createElementNS($cbc, 'cbc:ID', $this->nomina->numero_nomina);
        $refInv->appendChild($refInvId);
        $nota->appendChild($refInv);

        // Razón del ajuste
        $razon = $this->doc->createElementNS($cbc, 'cbc:CreditDebitNoteReason', $this->razonAjuste);
        $nota->appendChild($razon);

        // Información del payer
        $payer = $this->doc->createElementNS($cac, 'cac:PayerParty');
        $payerId = $this->doc->createElementNS($cbc, 'cbc:PartyID', $this->nomina->empresa->nit);
        $payer->appendChild($payerId);
        $payerName = $this->doc->createElementNS($cbc, 'cbc:PartyName', $this->nomina->empresa->nombre);
        $payer->appendChild($payerName);
        $nota->appendChild($payer);

        // Información del receiver (empleado)
        $receiver = $this->doc->createElementNS($cac, 'cac:ReceiverParty');
        $receiverId = $this->doc->createElementNS($cbc, 'cbc:PartyID', $this->nomina->recibo->afiliado->documento);
        $receiver->appendChild($receiverId);
        $receiverName = $this->doc->createElementNS($cbc, 'cbc:PartyName', $this->nomina->recibo->afiliado->nombre);
        $receiver->appendChild($receiverName);
        $nota->appendChild($receiver);

        // Totales (monto a ajustar)
        $totales = $this->doc->createElementNS($cac, 'cac:RequestedMonetaryTotal');
        $montoAjuste = $this->doc->createElementNS($cbc, 'cbc:PayableAmount', number_format($this->monto, 2, '.', ''));
        $totales->appendChild($montoAjuste);
        $nota->appendChild($totales);

        return $this->doc->saveXML();
    }
}
