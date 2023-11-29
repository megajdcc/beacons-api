<?php

namespace App\Models;

use App\Models\PermisosAsignados;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Permiso extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_permisos', 'permiso_id', 'usuario_id')->withPivot(['action']);
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_permisos', 'permiso_id', 'rol_id')->withPivot('actions');
    }

    public function modelos()
    {
        return $this->hasMany(PermisosAsignados::class, 'permiso_id', 'id');
    }

}
