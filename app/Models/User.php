<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Trais\Has_roles;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use Has_roles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'token',
        'is_passowrd',
        'activo',
        'rol_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'activo'            => 'boolean',
        'is_password'       => 'boolean'
    ];

    public function password(): Attribute
    {
        return Attribute::make(
            get:fn($val) => $val,
            set:fn($val) => Hash::make($val),
        );
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'usuario_permisos', 'usuario_id', 'permiso_id')->withPivot(['action']);
    }

    public function getTokenText(){
        return $this->token;
    }

    public function cargar(){
        $this->habilidades = $this->getHabilidades();
        $this->permisos;
        $this->rol;

        return $this;
    }
    

}
