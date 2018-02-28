<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GeneraComisiones extends Model
{
	protected $table = 'genera_comisiones';

	protected $fillable = [
	'periodo', 'tipoComision', 'estatus'
	];

	public $timestamps = false;
}
