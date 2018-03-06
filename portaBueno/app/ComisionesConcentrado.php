<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComisionesConcentrado extends Model
{
    protected $table = 'consetrado_comisiones';

    protected $primaryKey = 'id_comisiones';
    public $timestamps = false;
    protected $fillable = ['StrNom_corto', 'periodo', 'portas_num', 'estatus_baja', 'num_movistar', 'porcentaje', 'total_pagar', 'descuento', 'StrRegion'];
}
