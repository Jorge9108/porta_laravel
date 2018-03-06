@include('index')

<h1 align="center">Administración de porcentajes y reportes de comisiones</h1><br><br>
<div class="grid" style="padding: 0 5% 0 5%">
	<form action="#" id="form">
	<h4>Selecciona mes y quincena para generar reporte</h4><br>
	<div class="row cells4">	
		<div class="cell">
			<div class="input-control select">
				<label>Mes</label><br>
				<select name="mes" id="mes" required="required">
					<option value="">Seleccione uno</option>
					@for ($i=1; $i < 13; $i++)
					<option value='{{$i}}'>{{$meses[$i]}}</option>
					@endfor
				</select>
			</div>
		</div>
		<div class="cell">
			<div class="input-control select">
				<label>Tipo de promotor</label><br>
				<select id="tipoPromotor" name="tipoPromotor" required="required">
					<option value="">Seleccione uno</option>
					<option value="BONO">Por bono</option>
					<option value="UNITARIO">Por unidad</option>
				</select>
			</div>
		</div>
		<div class="cell">
			<label>Quincena</label><br>
			<label class="input-control radio">
				<input type="radio" name="quincena" checked value="1">
				<span class="check"></span>
				<span class="caption">Quincena 1</span>
			</label>
				<label class="input-control radio">
				<input type="radio" name="quincena" value="2">
				<span class="check"></span>
				<span class="caption">Quincena 2</span>
			</label>
		</div>
		<div class="cell"><button class="button primary loading-pulse">GUARDAR</button></div>
	</div>
	</form>

	<form action="#" id="formC">
	<h4>Porcentajes aceptados de ciudades</h4><br>
	<div class="row cells4">	
		<div class="cell">
			<div class="input-control select">
				<label>Ciudad</label>
				<select name="ciudad" id="ciudad" required="required">
					<option value="">Seleccione uno</option>
					@foreach ($ciudades as $ciudad)
						<option value='{{$ciudad->porcentaje}}'>{{ $ciudad->nombre }}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="cell">
			<div class="input-control text full-size">
				<label>Porcentaje</label>
				<input type="number" id="porcentaje" required="required">
			</div>
		</div>
		<div class="cell" align="right"><button class="button primary loading-pulse">ACTUALIZAR</button></div>
	</div>
	</form>
</div>
<script type="text/javascript">
	$.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')} });
	//Inserta datos para generar reporte
	$('#form').submit(function (e) {
		e.preventDefault();
		var param = {
			'mes': $('#mes option:selected').val(),
			'tipoPromotor': $("#tipoPromotor option:selected").val(),
			'quincena': $('input[name=quincena]:checked').val()
		};

		$.ajax({
			url: "{{route('guardaReporteGenerado')}}",
			type: 'POST',
			dataType: 'json',
			data: param,
			success:function(data){
				if (data == 1)
					$.Notify({type: 'success', caption: 'Datos guardados', content: "Ya puede generar reporte de comisiones"});
				else if (data == 0)
					$.Notify({type: 'warning', caption: '¡Nota!', content: "Los datos para el reporte ya han sido guardados, no puede volver a guardarlos"});
			}
		});
	});

	//Actualiza datos de porcentaje
	$('#ciudad').change(function(e){
		$('#porcentaje').val($(this).val());	
	});
	$('#formC').submit(function (e) {
		e.preventDefault();
		var param = {
			'ciudad': $('#ciudad option:selected').text(),
			'porcentaje': $("#porcentaje").val()
		};

		$.ajax({
			url: "{{route('actualizaCiudad')}}",
			type: 'POST',
			dataType: 'json',
			data: param,
			success:function(data){
				$.Notify({type: 'success', caption: '¡Nota!', content: "Porcentaje actualizado"});
				$('#ciudad option:selected').val(data);
			}
		});
	});
</script>