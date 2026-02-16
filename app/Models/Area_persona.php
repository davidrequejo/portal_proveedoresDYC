<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Area_persona extends Model
{
    protected $table = 'area_persona';
    protected $primaryKey = 'idarea_persona';
    //public $timestamps = true;

    protected $fillable = ['descripcion','estado_trash','user_created','user_updated'];


    // Método para obtener los 
    public static function select2area_persona()
    {
        return DB::table('area_persona as te')
            ->select('te.idarea_persona', 'te.descripcion')
            ->get();
    }

}
