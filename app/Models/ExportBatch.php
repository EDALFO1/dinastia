<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\EmpresaScope;

class ExportBatch extends Model
{
    use HasFactory;
    protected $fillable = [
    'empresa_id',
    'codigo',
    'periodo',
    'recibos_count',
    'total'
];
public function recibos()
{
    return $this->hasMany(Recibo::class, 'export_batch_id');
}
protected static function booted(): void
    {
        // Auto asignar empresa
        static::creating(function ($model) {
            if (session()->has('empresa_id')) {
                $model->empresa_id = session('empresa_id');
            }
        });

    }
}
