<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', [
	'as' => 'index', 
	'uses' => 'Controller@index'
]);
//Comisiones
//Manda al index de comisiones --Gnral o Individual--
Route::get('/comisionesPromotores', [
	'as' => 'comisionesPromotores', 
	'uses' => 'Comisiones\ComisionesPromotoresController@index'
]);

//Manda al reporte de las comisiones de promotores
Route::post('/comisionesPromotoresReport', [
	'as' => 'comisionesPromotoresReport', 
	'uses' => 'Comisiones\ComisionesPromotoresController@report'
]);

//Manda a la funcion quw exporta el reporte de comisiones de promotores individual
Route::get('/comisionesPromotoresDownload/{apellidoP}/{apellidoM}/{nombre}/{ciudad}/{fechaI}/{fechaF}/{StrNom_corto}/{supAsignado}/{totalPagar}/{totalMov}/{porcientoMov}/{totalDts}/{quincena}', [
	'as' => 'comisionesPromotoresDownload', 
	'uses' => 'Comisiones\ComisionesPromotoresController@exportReport'
]);

//Manda al reporte de las comisiones de promotores general
Route::post('/comisionesPromReportGnral', [
	'as' => 'comisionesPromReportGnral', 
	'uses' => 'Comisiones\ComisionesPromotoresController@generalReport'
]);

//Manda a la funcion que exporta el reporte de comisiones de promotores general
Route::get('/comisionesPromDownloadGnral/{tipoComisionPromotor}/{periodo}/{fechaI}/{fechaF}', [
	'as' => 'comisionesPromDownloadGnral', 
	'uses' => 'Comisiones\ComisionesPromotoresController@exportReportGnral'
]);

Route::post('/paginacion', [
	'as' => 'paginacion', 
	'uses' => 'Comisiones\ComisionesPromotoresController@paginacion'
]);

//Adminstrador
//Administra los porcentajes de ciudades y da de alta el reporte de comisiones
Route::get('/generaComision', [
	'as' => 'generaComision', 
	'uses' => 'Administrador\AdministradorController@generaComision'
]);

//Actualiza el porcentaje de la ciudad
Route::post('/actualizaCiudad', [
	'as' => 'actualizaCiudad', 
	'uses' => 'Administrador\AdministradorController@updateCiudad'
]);

//Guarda datos para permitir generar reporte
Route::post('/guardaReporteGenerado', [
	'as' => 'guardaReporteGenerado', 
	'uses' => 'Administrador\AdministradorController@generaReporte'
]);