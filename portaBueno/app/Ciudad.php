<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
	protected $table = 'ciudad';
	protected $fillable = [
		'nombre', 'ciudad', 'folio_ciudad', 'IntActivo', 'porcentaje'
	];
	public $timestamps = false;
}
