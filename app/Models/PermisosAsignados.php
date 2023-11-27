<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario\Permiso;

class PermisosAsignados extends Model
{
    use HasFactory;

    protected $fillable = [
        'permiso_id',
        'model_type',
        'model_id'
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function permiso()
    {
        return $this->belongsTo(Permiso::class, 'permiso_id', 'id');
    }
}
