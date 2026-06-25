<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\EmpresaScope;

class Afiliado extends BaseModel
{
    use HasFactory;
    protected $table = 'afiliados';

    protected $fillable = [

        'empresa_id',
        'empresa_laboral_id',
        'asesor_id',
        'documento_id',
        'subtipo_cotizante_id',

        'numero_documento',

        'primer_nombre',
        'segundo_nombre',

        'primer_apellido',
        'segundo_apellido',

        'fecha_nacimiento',
        'sexo',

        'correo',
        'telefono',
        'direccion',
        'ciudad',

        'google_drive_folder_id',

        'estado'
    ];
    protected $casts = [
        'fecha_nacimiento' => 'date',
        'estado' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function empresaLaboral()
    {
        return $this->belongsTo(EmpresaLaboral::class);
    }

    public function asesor()
    {
        return $this->belongsTo(Asesor::class);
    }

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    public function subtipoCotizante()
    {
        return $this->belongsTo(SubtipoCotizante::class);
    }
    public function recibos()
{
    return $this->hasMany(Recibo::class);
}
public function afiliacion()
{
    return $this->hasOne(Afiliacion::class, 'afiliado_id', 'id')
        ->where('estado', 1);
}

    public static function rules($id = null)
{
    return [

        'empresa_id' => [
            'required',
            'exists:empresas,id'
        ],

        'documento_id' => [
            'required',
            'exists:documentos,id'
        ],

        'numero_documento' => [
            'required',
            'string',
            'max:50',
            'unique:afiliados,numero_documento,' . $id . ',id,empresa_id,' . request('empresa_id')
        ],

        'primer_nombre' => [
            'required',
            'string',
            'max:255'
        ],

        'primer_apellido' => [
            'required',
            'string',
            'max:255'
        ],

        'correo' => [
            'nullable',
            'email'
        ],

    ];
}
protected static function booted(): void
{
    // Auto asignar empresa
    static::creating(function ($model) {
        if (session()->has('empresa_id')) {
            $model->empresa_id = session('empresa_id');
        }
    });

    // 🔥 SINCRONIZAR ESTADO CON AFILIACION
    static::updating(function ($afiliado) {

        if ($afiliado->isDirty('estado')) {

            // Cambiar estado de afiliación relacionada
            if ($afiliado->afiliacion) {
                $afiliado->afiliacion->update([
                    'estado' => $afiliado->estado
                ]);
            }

        }

    });
}
public function afiliaciones()
{
    return $this->hasMany(Afiliacion::class);
}
public function servicios()
{
    return $this->hasMany(AfiliadoServicio::class);
}

}