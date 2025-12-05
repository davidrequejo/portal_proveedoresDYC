<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use App\Models\Permiso;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $table = 'users';

    protected $primaryKey = 'id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'idpersona',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'usuario_permiso', 'users_id', 'idpermiso')
                    ->withPivot('idusuario_permiso')
                    ->withTimestamps();
    }

    public static function personas_sin_usuario()
    {

      $personas = DB::table('persona as p')
      ->join('tipo_persona as tp', 'p.idtipo_persona', '=', 'tp.idtipo_persona')
      ->leftJoin('users as u', 'p.idpersona', '=', 'u.idpersona')
      ->select( 'p.idpersona', 'p.nombre_razonsocial', 'p.numero_documento', 'tp.descripcion as Rolpersona' )
      ->where('p.estado', 1) ->whereNull('u.idpersona') ->get();

      return $personas;

    }



        // helper genérico: ¿tiene un permiso?
    public function hasGrupo(string $grupo): bool
    {
        if (! $this->relationLoaded('permisos')) {
            $this->load('permisos');
        }

        return $this->permisos->contains('grupo', $grupo);
    }
    
    public function getGrupoUtilitariosAttribute(): bool         { return $this->hasGrupo('Utilitarios'); }        // grupo_utilitarios
    public function getGrupoconfiguracionAttribute(): bool        { return $this->hasGrupo('configuracion'); }     // grupo_configuracion



    // helper genérico: ¿tiene un permiso?
    public function hasPermiso(string $escenario): bool
    {
        if (! $this->relationLoaded('permisos')) {
            $this->load('permisos');
        }
        return $this->permisos->contains('escenario', $escenario);
    }


    // atajos (para llamarlos como Auth::user()->perm_presupuesto)
    public function getPermPresupuestoAttribute(): bool         { return $this->hasPermiso('presupuesto'); }        // perm_presupuesto
    public function getPermProyectoAttribute(): bool            { return $this->hasPermiso('proyecto'); }           // perm_proyecto
    public function getPermRecursoAttribute(): bool             { return $this->hasPermiso('recurso'); }            // perm_recurso
    public function getPermConfiguracionAttribute(): bool       { return $this->hasPermiso('configuracion'); }      // perm_configuracion
    public function getPermUtilitarioAttribute(): bool          { return $this->hasPermiso('utilitario'); }         // perm_utilitario
    public function getPermCombinarTxtAttribute(): bool         { return $this->hasPermiso('combinar_txt'); }       // perm_combinar_txt
    public function getPermUsuarioAttribute(): bool             { return $this->hasPermiso('usuarios'); }           // perm_usuario
    public function getPermTipoSocioNogocioAttribute(): bool    { return $this->hasPermiso('tipo_socio_negocio'); }  // perm_tipo_socio_negocio
    public function getPermTipoEstandarAttribute(): bool        { return $this->hasPermiso('tipo_estandar'); }      // perm_tipo_estandar
    public function getPermProveedorAttribute(): bool           { return $this->hasPermiso('proveedor'); }           // perm_proveedor
    public function getPermPersonaAttribute(): bool             { return $this->hasPermiso('persona'); }           // perm_persona
}
