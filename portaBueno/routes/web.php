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

//Manda al index de comisiones --Gnral o Individual--
Route::get('/comisionesPromotores', [
	'as' => 'comisionesPromotores', 
	'uses' => 'ComisionesPromotoresController@index'
]);

//Manda al reporte de las comisiones de promotores
Route::post('/comisionesPromotoresReport', [
	'as' => 'comisionesPromotoresReport', 
	'uses' => 'ComisionesPromotoresController@report'
]);

//Manda a la funcion quw exporta el reporte de comisiones de promotores individual
Route::get('/comisionesPromotoresDownload/{apellidoM}/{apellidoP}/{nombre}/{ciudad}/{fechaI}/{fechaF}/{fechaInicialI}/{fechaFinalI}/{StrNom_corto}/{supAsignado}/{totalPagar}/{totalMov}/{porcientoMov}/{totalDts}/{quincena}', [
	'as' => 'comisionesPromotoresDownload', 
	'uses' => 'ComisionesPromotoresController@exportReport'
]);

//Manda al reporte de las comisiones de promotores general
Route::post('/comisionesPromReportGnral', [
	'as' => 'comisionesPromReportGnral', 
	'uses' => 'ComisionesPromotoresController@generalReport'
]);

//Manda a la funcion que exporta el reporte de comisiones de promotores general
Route::get('/comisionesPromDownloadGnral/{tipoComisionPromotor}/{periodo}/{fechaI}/{fechaF}', [
	'as' => 'comisionesPromDownloadGnral', 
	'uses' => 'ComisionesPromotoresController@exportReportGnral'
]);