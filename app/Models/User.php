<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Permiso;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
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
        return $this->belongsToMany(Permiso::class, 'usuario_permiso', 'idusuario', 'idpermiso')
                    ->withPivot('idusuario_permiso')
                    ->withTimestamps();
    }

    // helper genérico: ¿tiene un permiso?
    public function hasPermiso(string $escenario): bool
    {
        if (! $this->relationLoaded('permisos')) {
            $this->load('permisos');
        }
        return $this->permisos->contains('escenario', $escenario);
    }

    // atajos (para llamarlos como Auth::user()->perm_presupuesto)
    public function getPermPresupuestoAttribute(): bool     { return $this->hasPermiso('presupuesto'); }        // perm_presupuesto
    public function getPermProyectoAttribute(): bool        { return $this->hasPermiso('proyecto'); }           // perm_proyecto
    public function getPermRecursoAttribute(): bool         { return $this->hasPermiso('recurso'); }            // perm_recurso
    public function getPermConfiguracionAttribute(): bool   { return $this->hasPermiso('configuracion'); }      // perm_configuracion
    public function getPermUtilitarioAttribute(): bool      { return $this->hasPermiso('utilitario'); }         // perm_utilitario
    public function getPermImportarHoraAttribute(): bool    { return $this->hasPermiso('importar_hora'); }      // perm_importar_hora
    public function getPermCombinarTxtAttribute(): bool         { return $this->hasPermiso('combinar_txt'); }       // perm_combinar_txt
}
