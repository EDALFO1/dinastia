<?php

namespace App\Models;

class Plan extends BaseModel
{
    protected $table = 'planes';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'descripcion',
        'incluye_eps',
        'porcentaje_eps',
        'incluye_pension',
        'porcentaje_pension',
        'incluye_caja',
        'porcentaje_caja',
        'incluye_arl',
        'nivel_arl',
        'porcentaje_arl',
        'usa_admin_fijo',
        'valor_admin_fijo',
        'orden',
        'estado',
    ];

    protected $casts = [
        'incluye_eps'     => 'boolean',
        'incluye_pension' => 'boolean',
        'incluye_caja'    => 'boolean',
        'incluye_arl'     => 'boolean',
        'usa_admin_fijo'  => 'boolean',
        'estado'          => 'boolean',
    ];

    /**
     * Calcula el valor del plan para un parámetro anual dado.
     * Los porcentajes se almacenan como valores porcentuales (ej. 4.0 = 4%).
     *
     * @return array{total: int, componentes: array<string, int>}
     */
    public function calcularValor(ParametroAnual $param): array
    {
        $smmlv    = (float) $param->salario_minimo;
        $ceil100  = fn(float $v): int => (int) (ceil($v / 100) * 100);

        $componentes = [];
        $total       = 0;

        if ($this->incluye_eps && $this->porcentaje_eps > 0) {
            $v = $ceil100($smmlv * ($this->porcentaje_eps / 100));
            $componentes['EPS'] = $v;
            $total += $v;
        }

        if ($this->incluye_pension && $this->porcentaje_pension > 0) {
            $v = $ceil100($smmlv * ($this->porcentaje_pension / 100));
            $componentes['Pensión'] = $v;
            $total += $v;
        }

        if ($this->incluye_caja && $this->porcentaje_caja > 0) {
            $v = $ceil100($smmlv * ($this->porcentaje_caja / 100));
            $componentes['Caja'] = $v;
            $total += $v;
        }

        if ($this->incluye_arl && $this->porcentaje_arl > 0) {
            $v = $ceil100($smmlv * ($this->porcentaje_arl / 100));
            $componentes["ARL {$this->nivel_arl}"] = $v;
            $total += $v;
        }

        $admin = $this->usa_admin_fijo
            ? (int) $this->valor_admin_fijo
            : (int) $param->administracion;

        $componentes['Admin'] = $admin;
        $total += $admin;

        return ['total' => $total, 'componentes' => $componentes];
    }

    public static function nivelesArl(): array
    {
        return [
            'I'   => ['label' => 'Riesgo I',   'porcentaje' => 0.5220],
            'II'  => ['label' => 'Riesgo II',  'porcentaje' => 1.0440],
            'III' => ['label' => 'Riesgo III', 'porcentaje' => 2.4360],
            'IV'  => ['label' => 'Riesgo IV',  'porcentaje' => 4.3500],
            'V'   => ['label' => 'Riesgo V',   'porcentaje' => 6.9600],
        ];
    }

    public static function rules(): array
    {
        return [
            'nombre'           => ['required', 'string', 'max:120'],
            'descripcion'      => ['nullable', 'string', 'max:255'],
            'incluye_eps'      => ['nullable', 'boolean'],
            'porcentaje_eps'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'incluye_pension'  => ['nullable', 'boolean'],
            'porcentaje_pension' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'incluye_caja'     => ['nullable', 'boolean'],
            'porcentaje_caja'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'incluye_arl'      => ['nullable', 'boolean'],
            'nivel_arl'        => ['nullable', 'in:I,II,III,IV,V'],
            'porcentaje_arl'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'usa_admin_fijo'   => ['nullable', 'boolean'],
            'valor_admin_fijo' => ['nullable', 'numeric', 'min:0'],
            'orden'            => ['nullable', 'integer', 'min:0'],
            'estado'           => ['nullable', 'boolean'],
        ];
    }
}
