$(document).ready(function(){
	$("#comisiones").click(function(event) {
		$('#contenedor').load("comisiones/comisiones.php");
	});

	$("#prueba").click(function(event) {
		$('#contenedor').load("conexionPrueba.php");
	});

});
