<?php

namespace App\Http\Controllers\Comisiones;

use Illuminate\Support\Facades\Session;
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

class ComisionesPromotoresController extends Controller
{
	//Index de comisiones de promotores
	public function index() {
		$title = 'Comisiones de Promotores';
			$meses = [
			1=>"Enero", 2=>"Febrero",
			3=>"Marzo", 4=>"Abril",
			5=>"Mayo", 6=>"Junio",
			7=>"Julio", 8=>"Agosto",
			9=>"Septiembre", 10=>"Octubre",
			11=>"Noviembre", 12=>"Diciembre"
		];
		return view('comisiones/comisionesPromotores', compact('title', 'meses'));
	}

	//Reporte de comisiones de promotores
	public function report(Request $request) {
		//if($request->ajax()) {
			$year = $request->year;
			$mes = $request->mes;
			$quincena = $request->quincena;
			$tipoComisionPromotor = $request->tipoPromotor;
			$fecha = new DateTime($year.'-'.$mes);
			$meses = [
				1=>"Enero", 2=>"Febrero",
				3=>"Marzo", 4=>"Abril",
				5=>"Mayo", 6=>"Junio",
				7=>"Julio", 8=>"Agosto",
				9=>"Septiembre", 10=>"Octubre",
				11=>"Noviembre", 12=>"Diciembre"
			];
			$mesL =  substr($meses[$mes], 0, 3);
			$periodo = $mesL.$year.'_'.$quincena;
			if($quincena == 1) {
				$fecha->modify('first day of this month');
				$fechaI = $fecha->format('Y-m-d');
				$fechaF = $year.'-'.$mes.'-15';
				$periodo1 = $year.'-'.$mes.'-10';
			}
			elseif($quincena == 2) {
				$fecha->modify('last day of this month');
				$fechaF = $fecha->format('Y-m-d');
				$fechaI = $year.'-'.$mes.'-16';
				$periodo1 = $year.'-'.$mes.'-20';
			}
			$generaComisiones = GeneraComisiones::where('periodo', $periodo1)->where('tipoComision', $tipoComisionPromotor)->first();
		
			if (!isset($generaComisiones)){
				//Session::flash();
				return back()->with('msg', 'Aún no esta permitido generar el reporte de comisiones.');
			}
			elseif ($generaComisiones->estatus == 0){
				$generaComisiones->update(['estatus'=> 1]);
				return $this->prueba($fechaI, $fechaF, $year, $tipoComisionPromotor, $quincena, $periodo, 'insert');
			}else return $this->prueba($fechaI, $fechaF, $year, $tipoComisionPromotor, $quincena, $periodo, 'update');
		//}
	}

	protected function prueba($fechaI, $fechaF, $year, $tipoComisionPromotor, $quincena, $periodo, $opc) {
		//Datos entre las tablas tblpromotores y captura
		$capturas = Capturas::select(
			'captura.StrNom_corto', 'tblpromotores.SupAsignado', 'tblPromotores.estatus',
			'tblPromotores.StrNombre', 'tblPromotores.StrApellidoPa', 'tblPromotores.StrRegion',
			'tblPromotores.StrApellidoMa', 'tblPromotores.StrCiudad',
			DB::raw('count(captura.estatus_solicitud) as estatus_solicitud')
		)
		->join('tblPromotores', 'tblpromotores.StrNom_corto', '=', 'captura.StrNom_corto')
		->whereBetween('captura.fecha_ulti_sttus', [$fechaI, $fechaF])
		->where('captura.estatus_solicitud', 'Portabilidad_Exitosa')
		->where('tblpromotores.tipo_comision', $tipoComisionPromotor)
		->groupBy('captura.StrNom_corto')
		->orderBy('tblPromotores.StrApellidoPa')
		->get();

		foreach ($capturas as $key => $value) {
			if ($value->estatus_solicitud >= 1) {
				$validaciones = Capturas::select('captura.StrNom_corto',DB::raw('count(captura.estatus_solicitud) as solicitudMovistar'))
				->join('validacion', 'captura.id_cliente', '=', 'validacion.id_cliente')
				->whereBetween('captura.fecha_ulti_sttus', [$fechaI, $fechaF])
				->where('captura.estatus_solicitud', 'Portabilidad_Exitosa')
				->where('validacion.proveedor_actual', 'MOVISTAR')
				->where('captura.StrNom_corto', $value->StrNom_corto)
				->get();
				foreach ($validaciones as $key2) {
					$array[] = $key2->solicitudMovistar;
				}
				$porcentajeCiudad = Ciudad::select('porcentaje')->where('nombre', $value->StrCiudad)->first();
				$porcentajes[] = $porcentajeCiudad->porcentaje;
				$concentradoCom = ComisionesConcentrado::select()->where('periodo', $periodo)->where('StrNom_corto', $value->StrNom_corto)->first();
				$porcientoMov = round($array[$key]/$value->estatus_solicitud * 100, 0);
				$totalP = $value->estatus_solicitud;
				if ($porcientoMov < $porcentajeCiudad->porcentaje)
					$totalP = round($array[$key] / ($porcentajeCiudad->porcentaje/100), 0);
				if ($opc == 'update') {
					$valor = [
						'portas_num' => $value->estatus_solicitud,
						'estatus_baja' => $value->estatus,
						'num_movistar' => $array[$key],
						'porcentaje' => $porcientoMov,
						'total_pagar' => $totalP,
						'descuento' => ($value->estatus_solicitud - $totalP),
						'StrRegion' => $value->StrRegion
					];
					$concentradoCom->update($valor);
				}
				if ($opc == 'insert') {
					$valor = [
						'StrNom_corto' => $value->StrNom_corto,
						'periodo' => $periodo,
						'portas_num' => $value->estatus_solicitud,
						'estatus_baja' => $value->estatus,
						'num_movistar' => $array[$key],
						'porcentaje' => $porcientoMov,
						'total_pagar' => $totalP,
						'descuento' => ($value->estatus_solicitud - $totalP),
						'StrRegion' => $value->StrRegion
					];
					$concentradoCom->create($valor);
				}
			}
		}
		//return response()->view('comisiones/comisionesPromotoresReport', $fechaI, $fechaF, $tipoComisionPromotor, $periodo, $capturas, $array, $quincena, $porcentajes);
		return view('comisiones/comisionesPromotoresReport', compact('capturas', 'array', 'fechaI', 'fechaF', 'quincena', 'porcentajes', 'tipoComisionPromotor', 'periodo'));
		//return response()->json([1, $fechaI, $fechaF, $tipoComisionPromotor, $periodo, $capturas, $array, $quincena, $porcentajes, ]);
	}

	//Exporta el reporte de comisiones de promotores
	public function exportReport($apellidoP, $apellidoM, $nombre, $ciudad, $fechaI, $fechaF, $StrNom_corto, $supAsignado, $totalPagar, $totalMov, $porcientoMov, $totalDts, $quincena) {
		$dtsPromotor = TblPromotores::select()->where('StrNom_corto', $StrNom_corto)->first();//Datos del promotor
		$meses = [
			1=>"Enero", 2=>"Febrero",
			3=>"Marzo", 4=>"Abril",
			5=>"Mayo", 6=>"Junio",
			7=>"Julio", 8=>"Agosto",
			9=>"Septiembre", 10=>"Octubre",
			11=>"Noviembre", 12=>"Diciembre"
		];
		$mes =  substr($meses[substr($fechaI, 6, 1)], 0, 3);
		$periodo = $mes.substr($fechaI, 0, 4).'_'.$quincena;
		$fileName = $periodo.'_'.$apellidoP.$apellidoM.$nombre.'_'.$ciudad.'_'.$dtsPromotor->tipo_comision.'_'.$supAsignado;
		//Datos de la lista
		$capturasR = Capturas::select(
			'contatena', 'num_a_portar', 'ciudad_cliente',
			'nip', 'StrNom_corto', 'estatus_solicitud', 'encuesta_estatus',
			'fecha_ulti_sttus', 'folio_asignado', 'fecha_validacion'
		)
		->whereBetween('fecha_ulti_sttus', [$fechaI, $fechaF])
		->where('StrNom_corto', $StrNom_corto)
		->orderBy('fecha_ulti_sttus')
		->get();
		$totalRegistros = count($capturasR);
		
		Excel::create($fileName, function($excel) use($apellidoP, $apellidoM, $nombre, $ciudad, $fechaI, $fechaF, $StrNom_corto, $supAsignado, $totalPagar, $totalMov, $porcientoMov, $totalDts, $totalRegistros, $capturasR, $dtsPromotor){
			$excel->sheet('Reporte', function($sheet) use($apellidoP, $apellidoM, $nombre, $ciudad, $fechaI, $fechaF, $StrNom_corto, $supAsignado, $totalPagar, $totalMov, $porcientoMov, $totalDts, $totalRegistros, $capturasR, $dtsPromotor){
				$sheet->mergeCells('A1:H1');// Titulo general y primera tabla
				$sheet->row(1, ['REPORTE DE COMISIONES DE LA QUINCENA PROMOTORES']);// Titulo general y primera tabla
				$sheet->mergeCells('A2:H2');// Periodo
				$sheet->row(2, ['PERIODO DEL '.$fechaI.' AL '.$fechaF]);// Periodo
				$sheet->mergeCells('A3:H3');//Promotor, Tipo promotor
				$sheet->row(3, ['PROMOTOR: '.$apellidoP.' '.$apellidoM.' '.$nombre.' -- TIPO DE PROMOTOR: '.$dtsPromotor->tipo_promotor]);//Promotor, Tipo promotor
				$sheet->mergeCells('A4:H4');//Supervisor asignado, Tipo comisión, Ciudad
				$sheet->row(4, ['TIPO DE COMISION: '.$dtsPromotor->tipo_comision.' -- SUPERVISOR: '.$supAsignado.' -- CIUDAD: '.$ciudad]);
				//Titulos de la lista
				$sheet->row(6, ['#', 'NOMBRE DEL CLIENTE', 'NUMERO CEL.', 'ESTATUS', 'PROVEEDOR', 'PROMOCION', 'FECHA VALIDADA', 'FECHA ULT. ESTATUS']);
				
				//Contenido de la lista
				$fila = 7; $cont = 1;
				foreach ($capturasR as $key) {
					$dtsExt = Validaciones::select('proveedor_actual', 'pregunta3')
					->where('folio_asignado', $key->folio_asignado)
					->first();
					$sheet->row($fila, [
						$cont, $key->contatena, $key->num_a_portar, $key->estatus_solicitud,
						$dtsExt->proveedor_actual, $dtsExt->pregunta3, $key->fecha_validacion, $key->fecha_ulti_sttus
					]);
					if ($fila %2 == 0)
						$sheet->cells('A'.$fila.':H'.$fila, function($cells) { $cells->setBackground('#ECECEC'); });
					$fila++; $cont++;
				}

				//Diseño a la hoja
				$sheet->setPageMargin(.30);
				$sheet->cells('A1:A4', function($cells) {
					$cells->setAlignment('center'); // manipulate the range of cells	
					$cells->setFontFamily('Arial'); // Set font 
					$cells->setFontWeight('bold'); // Set font weight
				});
				$sheet->cells('A6:H6', function($cells) {
					$cells->setBackground('#000000'); // Set black background
					$cells->setFontColor('#ffffff'); // Set with font color
					$cells->setAlignment('center');
				});
			});
			$excel->sheet('Tablas Generales', function($sheet) use($apellidoP, $apellidoM, $nombre, $ciudad, $fechaI, $fechaF, $StrNom_corto, $supAsignado, $totalPagar, $totalMov, $porcientoMov, $totalDts, $totalRegistros, $capturasR, $dtsPromotor){
				$sheet->mergeCells('A1:H1');// Titulo general y primera tabla
				$sheet->row(1, ['REPORTE DE COMISIONES DE LA QUINCENA']);// Titulo general y primera tabla
				$sheet->mergeCells('A2:H2');// Periodo
				$sheet->row(2, ['PERIODO DEL '.$fechaI.' AL '.$fechaF]);// Periodo
				$sheet->mergeCells('A3:H3');//Promotor, Tipo promotor
				$sheet->row(3, ['PROMOTOR: '.$apellidoP.' '.$apellidoM.' '.$nombre.' -- TIPO DE PROMOTOR: '.$dtsPromotor->tipo_promotor]);//Promotor, Tipo promotor
				$sheet->mergeCells('A4:H4');//Supervisor asignado, Tipo comisión, Ciudad
				$sheet->row(4, ['TIPO DE COMISION: '.$dtsPromotor->tipo_comision.' -- SUPERVISOR: '.$supAsignado.' -- CIUDAD: '.$ciudad]);
				
				$capturasDts = Capturas::select(DB::raw('count(estatus_solicitud) as solicitudTotal'), 'estatus_solicitud')
				->whereBetween('fecha_ulti_sttus', [$fechaI, $fechaF])
				->where('StrNom_corto', $StrNom_corto)
				->groupBy('estatus_solicitud')
				->orderBy('estatus_solicitud')
				->get();

				$totalPagarEfectivo = 0;
				$totalCobro = 0;

				//Primer tabla de totales
				$tipoComision = $dtsPromotor->tipo_comision;
				$sheet->appendRow(array(''));
				$sheet->appendRow(array('PROVEEDOR', 'REAL', 'PORCENTAJE', 'A PAGAR'));
				$sheet->rows([
					['MOVISTAR', $totalMov, $porcientoMov.'%', $totalMov],
					['OTRA COMPAÑIA', ($totalDts-$totalMov), (100-$porcientoMov).'%', ($totalPagar-$totalMov)],
					['TOTAL:', ($totalMov+($totalDts-$totalMov)), 'TOTAL A PAGAR:', ($totalMov+($totalPagar-$totalMov))]
				]);
				//Segunda tabla de portabilidades exitosas y bonos
				$sheet->rows([array(''),array('')]); $fila = 13; 
				$sheet->appendRow(array('PORTAS EXITOSAS QUINCENALES', 'COMISION: '.$dtsPromotor->tipo_comision));
				$comisiones = TblComisiones::select()
				->where('descripcion', $dtsPromotor->tipo_promotor)
				->where('tipo', $dtsPromotor->tipo_comision)
				->orderBy('rango')->get();
				//Despliega dts de la tabla comisiones
				foreach ($comisiones as $comisiones) {
					if ($comisiones->tipo == 'BONO') {
						if ($totalPagar >= $comisiones->rango)
							$totalPagarEfectivo += $comisiones->bono; 
						$sheet->appendRow([$comisiones->validacionLogica.' '.$comisiones->rango, '$'.$comisiones->bono]);
						$sheet->cells('A'.$fila.':B'.$fila, function($cells) { 
							$cells->setAlignment('center'); 
							$cells->setBorder('thin', 'none', 'none', 'none');
						}); $fila++;
					}else if ($comisiones->tipo == 'UNITARIO') {
						if ($totalPagar >= $comisiones->rango && $totalPagar < $comisiones->rango2)
							$totalPagarEfectivo = $totalPagar * $comisiones->comision_unitaria; 
						$sheet->appendRow([$comisiones->rango.' A '.$comisiones->rango2, '$'.$comisiones->comision_unitaria]);
						$sheet->cells('A'.$fila.':B'.$fila, function($cells) { 
							$cells->setAlignment('center'); 
							$cells->setBorder('thin', 'none', 'none', 'none');
						}); $fila++;
					}
				}
				//Tercera tabla de datos exitosos, etc
				$sheet->rows([array(''),array('')]); $fila+=2;
				$sheet->appendRow(array('ETIQUETAS DE FILA:', ''));
				$sheet->cells('A'.$fila.':B'.$fila, function($cells) { // Style title last table
					$cells->setBackground('#000000'); // Set black background
					$cells->setFontColor('#ffffff'); // Set with font color
					$cells->setAlignment('center');
				}); $fila++;
				$sheet->appendRow(array($apellidoP.' '.$apellidoM.' '.$nombre, $totalRegistros));
				$sheet->cells('A'.$fila.':B'.$fila, function($cells) { $cells->setBorder('none', 'none', 'thin', 'none'); });
				$sheet->cell('B'.$fila, function($cell) { $cell->setAlignment('center'); });
				foreach ($capturasDts as $cd) {
					if ($cd->estatus_solicitud == 'sim_no_recup_penalizada')
						$totalCobro++;
					$sheet->appendRow([$cd->estatus_solicitud, $cd->solicitudTotal]);
					$fila++;
					$sheet->cell('B'.$fila, function($cell) { $cell->setAlignment('center'); });
				}
				$sheet->appendRow(array('TOTAL GENERAL', $totalRegistros));
				$sheet->appendRow(array('TOTAL PARA COMISIONES', $totalPagar));
				$sheet->appendRow(array('TOTAL A PAGO', '$'.$totalPagarEfectivo));
				$costoSim = Comisiones::select('cobro_sim')->first();
				if($totalCobro > 0)
					$totalCobro = $totalCobro * $costoSim->cobro_sim;
				$sheet->appendRow(array('DESCUENTO', '$'.$totalCobro));
				for ($i=0; $i < 4; $i++) {
					$fila++;
					$sheet->cell('A'.$fila, function($cell) { $cell->setBackground('#48F582'); });
					$sheet->cell('B'.$fila, function($cell) { $cell->setBackground('#48F582'); $cell->setAlignment('center'); });
				}
				$sheet->cell('A'.($fila-3), function($cell) { $cell->setBackground('#008FFD'); $cell->setFontWeight('bold'); });
				$sheet->cell('B'.($fila-3), function($cell) { $cell->setBackground('#008FFD'); $cell->setAlignment('center'); });

				//Diseño a la hoja
				$sheet->setPageMargin(.30);
				$sheet->cells('A1:A4', function($cells) { //Style first 4 rows
					$cells->setAlignment('center'); // manipulate the range of cells	
					$cells->setFontFamily('Arial'); // Set font 
					$cells->setFontWeight('bold'); // Set font weight
					$cells->setBorder('none', 'none', 'thin', 'none');
				});
				$sheet->cells('A6:D6', function($cells) { //Style row A6 to D6
					$cells->setBackground('#000000'); // Set black background
					$cells->setFontColor('#ffffff'); // Set with font color
					$cells->setAlignment('center');
				});
				$sheet->cells('A7:D7', function($cells) { $cells->setBorder('none', 'none', 'thin', 'none'); });
				$sheet->cells('B7:D7', function($cells) { $cells->setAlignment('center'); });
				$sheet->cells('A8:D8', function($cells) { $cells->setBorder('none', 'none', 'thin', 'none'); });
				$sheet->cells('B8:D8', function($cells) { $cells->setAlignment('center'); });
				$sheet->cell('A9', function($cell) { $cell->setAlignment('right'); $cell->setFontWeight('bold'); });
				$sheet->cell('B9', function($cell) { $cell->setBackground('#BDBDBD'); $cell->setAlignment('center'); });
				$sheet->cell('C9', function($cell) { $cell->setAlignment('right'); $cell->setFontWeight('bold'); });
				$sheet->cell('D9', function($cell) { $cell->setBackground('#BDBDBD'); $cell->setAlignment('center'); });
				
				$sheet->cells('A12:B12', function($cells) { //Style row A12 to B12
					$cells->setBackground('#000000'); // Set black background
					$cells->setFontColor('#ffffff'); // Set with font color
					$cells->setAlignment('center');
				});
			});
		})->export('xlsx');
	}

	//Reporte de comisiones generales de los promotores (Relacion de pagos promo) //NO FUNCIONA POR EL MOMENTO
	public function generalReport(Request $request) {
		$this->validate($request,[
			'year' => 'required',
			'mes' => 'required',
			'quincena' => 'required',
			'tipoPromotor' => 'required'
		]);

		$year = $request->get('year');
		$mes = $request->get('mes');
		$quincena = $request->get('quincena');
		$tipoComisionPromotor = $request->get('tipoPromotor');
		$meses = [
			1=>"Enero", 2=>"Febrero",
			3=>"Marzo", 4=>"Abril",
			5=>"Mayo", 6=>"Junio",
			7=>"Julio", 8=>"Agosto",
			9=>"Septiembre", 10=>"Octubre",
			11=>"Noviembre", 12=>"Diciembre"
		];
		$mes =  substr($meses[$mes], 0, 3);
		$periodo = $mes.$year.'_'.$quincena;

		$comisionesPeriodos = ComisionesPeriodo::select('fecha_inicial','fecha_final','periodo','inicial_ingresada','final_ingresada')
		->where('periodo', $periodo)
		->where('ano', $year)
		->first();
		$fechaI = $comisionesPeriodos->fecha_inicial;
		$fechaF = $comisionesPeriodos->fecha_final;
		$fechaInicialI = $comisionesPeriodos->inicial_ingresada;
		$fechaFinalI = $comisionesPeriodos->final_ingresada;

		//datos de la tabla
		$comisionesConcentrado = ComisionesConcentrado::select(
			'consetrado_comisiones.StrNom_corto','consetrado_comisiones.portas_num',
			'consetrado_comisiones.StrRegion','consetrado_comisiones.total_pagar',
			'consetrado_comisiones.porcentaje','consetrado_comisiones.num_movistar',
			'tblpromotores.StrNom_corto','tblpromotores.StrCiudad','tblpromotores.StrApellidoPa',
			'tblpromotores.StrApellidoMa','tblpromotores.StrNombre','tblpromotores.sueldo',
			'tblpromotores.estatus','tblpromotores.num_cuenta','tblpromotores.observaciones',
			'tblpromotores.tipo_promotor','tblpromotores.tipo_comision','tblpromotores.SupAsignado'
		)
		->join('tblpromotores', 'consetrado_comisiones.StrNom_corto', '=', 'tblpromotores.StrNom_corto')
		->where('consetrado_comisiones.periodo', $periodo)
		->where('tblpromotores.tipo_comision', $tipoComisionPromotor)
		->orderBy('tblPromotores.StrApellidoPa')
		->get();

		//Penslizaciones para cobro
		foreach ($comisionesConcentrado as $key => $value) {
			$capturas = Capturas::select(DB::raw('count(captura.estatus_solicitud) as estatus_solicitud'))
			->whereBetween('fecha_ulti_sttus', [$comisionesPeriodos->fecha_inicial, $comisionesPeriodos->fecha_final])
			->where('estatus_solicitud', 'sim_no_recup_penalizada')
			->where('StrNom_corto', $value->StrNom_corto)
			->get();
			foreach ($capturas as $key2) {
				$portasPagar[] = $key2;
			}
			$tblComisiones = TblComisiones::select()->where('descripcion', $value->tipo_promotor)->where('tipo', $tipoComisionPromotor)->orderBy('rango')->get();
			foreach ($tblComisiones as $key => $val) {
				if ($tipoComisionPromotor == 'BONO') {
					$tbCom[] = $val->rango;
					$bonos[] = $val->bono;
				}else if($tipoComisionPromotor == 'UNITARIO') {
					$tbCom[] = $val->rango;
					$tbCom2[] = $val->rango2;
					$bonos[] = $val->comision_unitaria;
				}
			}
		}
		$costoSim = Comisiones::select('cobro_sim')->first();
		foreach ($portasPagar as $key3) {
			$costoTotalSim = $costoSim->cobro_sim * $key3->estatus_solicitud;
			$array[] = $costoTotalSim;
		}
		
		return view('comisiones/comisionesPromReportGnral', compact('tipoComisionPromotor', 'comisionesConcentrado', 'array', 'tbCom', 'bonos', 'tbCom2', 'fechaI', 'fechaF', 'fechaInicialI', 'fechaFinalI', 'quincena', 'periodo'));
	}

	//Exporta el reporte de comisiones de promotores general
	public function exportReportGnral($tipoComisionPromotor, $periodo, $fechaI, $fechaF) {
		$fileName = 'ReporteGnralComisionesPromotores_'.$periodo.'_del_'.$fechaI.'_al_'.$fechaF;
		//Consulta los datos de las comisiones
		$comisionesConcentrado = ComisionesConcentrado::select(
			'consetrado_comisiones.StrNom_corto','consetrado_comisiones.portas_num',
			'consetrado_comisiones.StrRegion','consetrado_comisiones.total_pagar',
			'consetrado_comisiones.porcentaje','consetrado_comisiones.num_movistar',
			'tblpromotores.StrNom_corto','tblpromotores.StrCiudad','tblpromotores.StrApellidoPa',
			'tblpromotores.StrApellidoMa','tblpromotores.StrNombre','tblpromotores.sueldo',
			'tblpromotores.estatus','tblpromotores.num_cuenta','tblpromotores.observaciones',
			'tblpromotores.tipo_promotor','tblpromotores.tipo_comision','tblpromotores.SupAsignado'
		)
		->join('tblpromotores', 'consetrado_comisiones.StrNom_corto', '=', 'tblpromotores.StrNom_corto')
		->where('consetrado_comisiones.periodo', $periodo)
		->where('tblpromotores.tipo_comision', $tipoComisionPromotor)
		->orderBy('tblPromotores.StrApellidoPa')
		->get();	

		foreach ($comisionesConcentrado as $key => $val) {
			//Consulta de las comisiones para ontener bonos
			$tblComisiones = TblComisiones::select()->where('descripcion', $val->tipo_promotor)->where('tipo', $tipoComisionPromotor)->orderBy('rango')->get();
			foreach ($tblComisiones as $key => $value) {
				if ($tipoComisionPromotor == 'BONO') {
					if ($val->total_pagar >= $value->rango)
						$dts[] = $value->bono;
					else $dts[] = '';
				}elseif ($tipoComisionPromotor == 'UNITARIO') {
					if ($val->total_pagar >= $value->rango && $val->total_pagar <= $value->rango2)
						$dts[] = $value->comision_unitaria;
				}
			}
		}

		Excel::create($fileName, function($excel) use($tipoComisionPromotor, $periodo, $fechaI, $fechaF, $comisionesConcentrado, $dts) {
			$excel->sheet('Reporte', function($sheet) use($tipoComisionPromotor, $periodo, $fechaI, $fechaF, $comisionesConcentrado, $dts) {
				$sheet->mergeCells('A1:N1');// Titulo general y primera tabla
				$sheet->row(1, ['REPORTE GENERAL DE COMISIONES DE LA QUINCENA PROMOTORES']);// Titulo general y primera tabla
				$sheet->mergeCells('A2:N2');// Periodo
				$sheet->row(2, ['PERIODO DEL '.$fechaI.' AL '.$fechaF]);// Periodo
				$cont = 1; $fila = 5; $var = 0;
				$costoSim = Comisiones::select('cobro_sim')->first();
				
				if($tipoComisionPromotor == 'BONO')
					$sheet->row(4, ['#', 'NOMBRE DE PROMOTOR', 'ESTATUS', 'CIUDAD', 'CUENTA', 'SUELDO BASE', 'EXITOSAS', 'A PAGAR', 'TIPO PROMOTOR', 'BONO 21-22', 'BONO 26-27', 'BONO 31-32', 'DESCUENTO', 'TOTAL']); //Titulos de la lista si es por bono
				elseif ($tipoComisionPromotor == 'UNITARIO')
					$sheet->row(4, ['#', 'NOMBRE DE PROMOTOR', 'ESTATUS', 'CIUDAD', 'CUENTA', 'SUELDO BASE', 'EXITOSAS', 'A PAGAR', 'TIPO PROMOTOR', 'PAGO UNITARIO', 'DESCUENTO', 'TOTAL']);//Titulos de la lista si es por unitario

				foreach ($comisionesConcentrado as $key => $val) {
					//Consulta del total de sims que debe el protor
					$solicitudes = Capturas::select(DB::raw('count(captura.estatus_solicitud) as estatus_solicitud'))
					->whereBetween('fecha_ulti_sttus', [$fechaI, $fechaF])
					->where('estatus_solicitud', 'sim_no_recup_penalizada')
					->where('StrNom_corto', $val->StrNom_corto)
					->first();
					$costoTotalSim = $solicitudes->estatus_solicitud * $costoSim->cobro_sim; //Costo de las sims que debe el promotor
					//Condicion que define el estatus del promotor
					if ($val->estatus != 1 && $val->estatus != 2) $estatus = 'BAJA';
					else $estatus = 'ACTIVO';
					//Impresion de los datos dependiendo del tipo de comisión
					if ($tipoComisionPromotor == 'BONO') {
						$dt1=''; $dt2=''; $dt3='';
						if (is_numeric($dts[$var])) {
							$total = $dts[$var]; $dt1 = '$'.$dts[$var];
							if (is_numeric($dts[$var+1])) {
								$total += $dts[$var+1]; $dt2 = '$'.$dts[$var+1];
								if (is_numeric($dts[$var+2]))
									$total += $dts[$var+2]; $dt3 = '$'.$dts[$var+2];
							}
						}else $total = 0;
						$total = $total - $costoTotalSim;
						$sheet->appendRow([
							$cont, $val->StrApellidoPa.' '.$val->StrApellidoMa.' '.$val->StrNombre,
							$estatus, $val->StrCiudad, $val->num_cuenta, '$'.$val->sueldo,
							$val->portas_num, $val->total_pagar, $val->tipo_promotor, 
							$dt1, $dt2, $dt3, '$'.$costoTotalSim, '$'.$total
						]);$var+=3;
					}elseif ($tipoComisionPromotor == 'UNITARIO'){
						$cU = 0;
						if (is_numeric($dts[$var])) {
							$cU = $dts[$var] * $val->total_pagar;
						}
						$total = $cU - $costoTotalSim;
						$sheet->appendRow([
							$cont, $val->StrApellidoPa.' '.$val->StrApellidoMa.' '.$val->StrNombre,
							$estatus, $val->StrCiudad, $val->num_cuenta, '$'.$val->sueldo,
							$val->portas_num, $val->total_pagar, $val->tipo_promotor, '$'.$cU, '$'.$costoTotalSim, '$'.$total
						]);$var++;
					}
					//Diseño a las filas de la tabla
					if ($val->estatus != 1 && $val->estatus != 2)
						$sheet->cells('A'.$fila.':B'.$fila, function($cells) { $cells->setFontColor('#FE2E2E'); });
					$sheet->cells('E'.$fila.':H'.$fila, function($cells) { $cells->setAlignment('center'); });
					if ($val->porcentaje < 60)
						$sheet->cell('H'.$fila, function($cell) { $cell->setFontColor('#FE2E2E'); });
					$sheet->cells('J'.$fila.':N'.$fila, function($cells) { $cells->setAlignment('center'); });
					$cont++; $fila++;
				}
				
				//Diseño de la hoja
				$sheet->setPageMargin(.30);
				$sheet->cells('A1:A2', function($cells) {
					$cells->setAlignment('center'); // manipulate the range of cells	
					$cells->setFontFamily('Arial'); // Set font 
					$cells->setFontWeight('bold'); // Set font weight
				});
				$sheet->cells('A4:N4', function($cells) { //Style row A12 to B12
					$cells->setBackground('#000000'); // Set black background
					$cells->setFontColor('#ffffff'); // Set with font color
					$cells->setAlignment('center');
				});
			});
		})->export('xlsx');
	}

	public function paginacion(Request $request) {
		if ($request->ajax()) {
			$data = "";
			//Datos entre las tablas tblpromotores y captura
			$capturas = Capturas::select(
				'captura.StrNom_corto', 'tblpromotores.SupAsignado', 'tblPromotores.estatus',
				'tblPromotores.StrNombre', 'tblPromotores.StrApellidoPa', 'tblPromotores.StrRegion',
				'tblPromotores.StrApellidoMa', 'tblPromotores.StrCiudad',
				DB::raw('count(captura.estatus_solicitud) as estatus_solicitud')
			)
			->join('tblPromotores', 'tblpromotores.StrNom_corto', '=', 'captura.StrNom_corto')
			->whereBetween('captura.fecha_ulti_sttus', [$request->fechaI, $request->fechaF])
			->where('captura.estatus_solicitud', 'Portabilidad_Exitosa')
			->where('tblpromotores.tipo_comision', $request->tipoComisionPromotor)
			->groupBy('captura.StrNom_corto')
			->orderBy('tblPromotores.StrApellidoPa')
			->limit(9)
			->get();

			foreach ($capturas as $key => $value) {

				if ($value->estatus_solicitud >= 1) {
					$validaciones = Capturas::select('captura.StrNom_corto',DB::raw('count(captura.estatus_solicitud) as solicitudMovistar'))
					->join('validacion', 'captura.id_cliente', '=', 'validacion.id_cliente')
					->whereBetween('captura.fecha_ulti_sttus', [$request->fechaI, $request->fechaF])
					->where('captura.estatus_solicitud', 'Portabilidad_Exitosa')
					->where('validacion.proveedor_actual', 'MOVISTAR')
					->where('captura.StrNom_corto', $value->StrNom_corto)
					->first();
					$porcentajeCiudad = Ciudad::select('porcentaje')->where('nombre', $value->StrCiudad)->first();
					$porcientoMov = round($validaciones->solicitudMovistar/$value->estatus_solicitud * 100, 0); $totalPagar = $value->estatus_solicitud;
					$data .= "<tr>";
					$data .= "<td>$value->StrApellidoPa $value->StrApellidoMa $value->StrNombre</td>";
					$data .= "<td align='center'>$value->estatus_solicitud</td>";
					$data .= "<td align='center'>$validaciones->solicitudMovistar</td>";
					if ($porcientoMov >= $porcentajeCiudad->porcentaje) {
						$data .= "<td align='center'>$porcientoMov%</td>";
						$data .= "<td align='center'>$totalPagar</td>";
					}else {
						$totalPagar = round($validaciones->solicitudMovistar / ($porcentajeCiudad->porcentaje/100), 0);
						$data .= "<td align='center' style='color: red'>$porcientoMov%</td>";
						$data .= "<td align='center' style='color: red'>$totalPagar</td>";
					}
					$data .= "<td>$value->StrCiudad</td>";
					$data .= "<td align='center'><a href=".route('comisionesPromotoresDownload', [$value->StrApellidoPa,$value->StrApellidoMa,$value->StrNombre,$value->StrCiudad,$request->fechaI,$request->fechaF,$value->StrNom_corto,$value->SupAsignado,$totalPagar,$validaciones->solicitudMovistar,$porcientoMov,$value->estatus_solicitud,$request->quincena])."><span class='mif-download2 mif-lg' data-role='hint' data-hint-background='bg-blue' data-hint-color='fg-white' data-hint-mode='2' data-hint='Exportar reporte'></span></a></td>";
					$data .= "</tr>";
				}
			}
			return response()->json($data);
		}
	}

	
}