<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonaCuentaBancaria extends Model
{
    use HasFactory;

    // Nombre real de la tabla
    protected $table = 'persona_cuentabancaria';

    // Primary Key
    protected $primaryKey = 'idpersona_cuentabancaria';

    // Auto incremento
    public $incrementing = true;

    // Tipo de la PK
    protected $keyType = 'int';

    // Timestamps habilitados
    public $timestamps = true;

    // Columnas asignables masivamente
    protected $fillable = [
        'idpersona',
        'idbanco',
        'tipocuenta',
        'predeterminado',
        'moneda',
        'numero_cuenta',
        'numero_cuenta_abono',
        'cuenta_interbancaria',
        'estado_trash',
        'estado_delete',
        'user_trash',
        'user_delete',
        'user_created',
        'user_updated',
    ];

    // Valores por defecto
    protected $attributes = [
        'estado_trash'  => '1',
        'estado_delete' => '1',
        'predeterminado'=> '0',
    ];

    /* =========================
     * Relaciones
     * ========================= */

    // Persona
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idpersona');
    }

    // Banco
    public function banco()
    {
        return $this->belongsTo(Banco::class, 'idbanco');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'idpersona', 'idpersona');
    }

    /* =========================
     * Métodos personalizados
     * ========================= */

    /**
     * Obtener cuentas bancarias activas de una persona.
     */
    public static function obtenerCuentasActivasProveedor($idpersona)
    {
        return self::where('idpersona', $idpersona)
                    ->where('estado_trash', '1')
                    ->where('estado_delete', '1')
                    ->get();
    }
}
