<?php

namespace App\Http\Controllers\Administrador;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ComisionesConcentrado;
use App\GeneraComisiones;
use App\ComisionesPeriodo;
use App\ComisionesMovil;
use App\TblPromotores;
use App\TblComisiones;
use App\Validaciones;
use App\Comisiones;
use App\Capturas;
use App\Ciudad;
use DateTime;
use Excel;
use DB;

class AdministradorController extends Controller
{
	public function generaComision() {
		$meses = [
			1=>"Enero", 2=>"Febrero",
			3=>"Marzo", 4=>"Abril",
			5=>"Mayo", 6=>"Junio",
			7=>"Julio", 8=>"Agosto",
			9=>"Septiembre", 10=>"Octubre",
			11=>"Noviembre", 12=>"Diciembre"
		];
		$ciudades = Ciudad::select('nombre', 'porcentaje')->get();
		return view('administrador/generaComisiones', compact('ciudades', 'meses'));
	}

	public function updateCiudad(Request $request) {
		if($request->ajax()) {
			$ciudad = Ciudad::where('nombre', $request->ciudad)->first();
			$ciudad->update(['porcentaje' => $request->porcentaje]);
			$porcentaje = $ciudad->porcentaje;
			return response()->json($porcentaje); 
		}
	}

	public function generaReporte(Request $request) {
		if($request->ajax()) {
			if ($request->quincena == 1) 
				$periodo = date('Y').'-'.$request->mes.'-10';
			elseif ($request->quincena == 2)
				$periodo = date('Y').'-'.$request->mes.'-20';
			$generaComision = GeneraComisiones::where('periodo', $periodo)->where('tipoComision', $request->tipoPromotor)->first();
			
			if (!isset($generaComision)) {
				$dts = [
					'periodo' => $periodo,
					'tipoComision' => $request->tipoPromotor,
					'estatus' => 0,
				];
				$guardaDts = GeneraComisiones::create($dts);
				return response()->json(1);
			}else
				return response()->json(0);
		}
	}
}