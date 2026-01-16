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
use App\Models\Persona;

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
        'estado_trash',
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

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idpersona', 'idpersona');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'usuario_permiso', 'users_id', 'idpermiso')
                    ->withPivot('idusuario_permiso')
                    ->withTimestamps();
    }

    public static function personas_sin_usuario($id = null)
    {

			$query = DB::table('persona as p')
					->join('tipo_persona as tp', 'p.idtipo_persona', '=', 'tp.idtipo_persona')
					->leftJoin('users as u', 'p.idpersona', '=', 'u.idpersona')
					->select(
							'p.idpersona',
							'p.nombre_razonsocial',
							'p.numero_documento',
							'tp.descripcion as Rolpersona'
					)
					->where('p.estado', 1);

			// 👉 Si llega un id, traer solo esa persona (tenga o no usuario)
			if ($id) {
					$query->where('p.idpersona', $id);
			} else {
					// 👉 Si no llega id, solo personas SIN usuario
					$query->whereNull('u.idpersona');
			}

			return $query->get();

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
    public function getGrupoconfiguracionAttribute(): bool        { return $this->hasGrupo('Configuración'); }     // grupo_configuracion
    public function getGrupoconProveedorAttribute(): bool        { return $this->hasGrupo('Proveedor'); }        // grupo_Proveedor
    public function getGrupoconClienteAttribute(): bool        { return $this->hasGrupo('Cliente'); }        // grupo_Cliente



    // helper genérico: ¿tiene un permiso?
    public function hasPermiso(string $escenario): bool
    {
        if (! $this->relationLoaded('permisos')) {
            $this->load('permisos');
        }
        return $this->permisos->contains('escenario', $escenario);
    }


    // atajos (para llamarlos como Auth::user()->perm_presupuesto)

    //UTILITARIOS
    public function getPermUsuarioAttribute(): bool             { return $this->hasPermiso('usuarios'); }           // perm_usuario
    public function getPermPersonaAttribute(): bool             { return $this->hasPermiso('persona'); }           // perm_persona
    //CONFIGURACION
    public function getPermProveedorTipoAttribute(): bool    { return $this->hasPermiso('proveedor_tipo'); }  // perm_proveedor_tipo
    public function getPermBancosAttribute(): bool    { return $this->hasPermiso('bancos'); }  // perm_bancos
    public function getPermPeriodoHomologacionAttribute(): bool    { return $this->hasPermiso('periodo_homologacion'); }  // perm_periodo_homologacion

    //Proveedor
    public function getPermProveedorVistaAdmAttribute(): bool { return $this->hasPermiso('proveedor_vista_adm'); }      // perm_proveedor_vista_adm
    public function getPermProveedorVistaDocumentosClientAttribute(): bool { return $this->hasPermiso('proveedor_vista_documentos_client'); }      // perm_proveedor_vista_documentos_client
    public function getPermProveedorVistaActDatosClientAttribute(): bool { return $this->hasPermiso('proveedor_vista_act_datos_client'); }      // perm_proveedor_vista_act_datos_client
    
    //cliente
    public function getPermClientVistaAdmAttribute(): bool           { return $this->hasPermiso('client_vista_adm'); }           // perm_client_vista_adm
    public function getPermClientVistaClientAttribute(): bool           { return $this->hasPermiso('client_vista_client'); }           // perm_client_vista_client

}
