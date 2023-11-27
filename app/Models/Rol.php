<?php

namespace App\Models;

use App\Models\AcademiaVideo;
use App\Models\Comision;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'rol_permisos', 'rol_id', 'permiso_id')->withPivot(['actions']);
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'rol_id', 'id');
    }

 


}
