@include('index')
<?php $cont = 0; ?>
<div class="example" data-text="comisiones de promotores">
	<ul class="breadcrumbs2">
		<li><a href="{{ route('index') }}"><span class="icon mif-home"></span></a></li>
		<li><a href="{{ route('comisionesPromotores') }}">Comisiones de promotores</a></li>
		<li><a href="#">Reporte de comisiones promotores</a></li>
	</ul>
	<div class="grid">
		<h3 align="center">Reporte de comisiones promotores</h3>
		<div class="row cells9">
			<div class="cell colspan5"><h4 align="right">{{ $fechaI.' al '.$fechaF }}</h4></div>
			<div class="cell colspan4">
				<div class="toolbar rounded" align="left">
					<a class="toolbar-button" href="comisionesPromDownloadGnral/{{$tipoComisionPromotor}}/{{$periodo}}/{{$fechaI}}/{{$fechaF}}"><span class="mif-floppy-disk" data-role="hint" data-hint-background="bg-blue" data-hint-color="fg-white" data-hint-mode="2" data-hint="Exportar reporte general"></span></a>
				</div>
			</div>
		</div>
	</div>
	<table class="table border cell-hovered hovered">
		<thead>
			<tr>
			<th class="sortable-column sort-asc" id="nombreP">Nombre de promotor</th>
			<th class="sortable-column" id="portasE">Portabilidades exitosas</th>
			<th class="sortable-column" id="portasM">Portabilidades Movistar</th>
			<th class="sortable-column" id="porcentajeM">Porcentaje Movistar</th>
			<th class="sortable-column" id="portasP">Portabilidades a pagar</th>
			<th class="sortable-column" id="ciudad">Ciudad</th>
			<th class="sortable-column">Archivo</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($capturas as $capturas => $value)
		@if ($cont != 9)
			<?php $porcientoMov = round($array[$capturas]/$value->estatus_solicitud * 100, 0); $totalPagar = $value->estatus_solicitud; ?>
				<tr>
				<td>{{ $value->StrApellidoPa }} {{ $value->StrApellidoMa }} {{ $value->StrNombre }}</td>
				<td align="center">{{ $value->estatus_solicitud }}</td>
				<td align="center">{{ $array[$capturas] }}</td>
				@if ($porcientoMov >= $porcentajes[$capturas])
				<td align="center">{{ $porcientoMov }}%</td>
				<td align="center">{{ $totalPagar }}</td>
				@else
				<?php $totalPagar = round($array[$capturas] / ($porcentajes[$capturas]/100), 0); ?>
				<td align="center" style="color: red">{{ $porcientoMov }}%</td>
				<td align="center" style="color: red">{{ $totalPagar }}</td>
				@endif
				<td>{{ $value->StrCiudad }}</td>
				<td align="center"><a href="comisionesPromotoresDownload/{{$value->StrApellidoPa}}/{{$value->StrApellidoMa}}/{{$value->StrNombre}}/{{$value->StrCiudad}}/{{$fechaI}}/{{$fechaF}}/{{$value->StrNom_corto}}/{{$value->SupAsignado}}/{{$totalPagar}}/{{$array[$capturas]}}/{{$porcientoMov}}/{{$value->estatus_solicitud}}/{{$quincena}}"><span class="mif-download2 mif-lg" data-role="hint" data-hint-background="bg-blue" data-hint-color="fg-white" data-hint-mode="2" data-hint="Exportar reporte"></span></a></td>
				</tr>
			<?php $cont++; ?>
		@endif
		@endforeach
		</tbody>
	</table>
	<?php $array[] = $capturas; $numP = ceil(sizeof($array)/9); ?>
	<div class="pagination rounded" id="pagination">
		<span class="item disabled" id="back"><</span>
		@for ($i=0; $i<$numP; $i++)
		@if(($i+1) == 1)
			<span class="item current" id="{{$i+1}}">{{ $i+1 }}</span>
		@else
			<span class="item" id="{{$i+1}}">{{ $i+1 }}</span>
		@endif
		@endfor
		<span class="item" id="next">></span>
	</div>
</div>

<script>
$.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')} });
//Cuando presiona boton back
$('#back').click(function(e) {
	var posA = $('.current');
	if ($(this).hasClass('disabled')) {
	}else {
		if (posA.attr('id') == 1) $(this).addClass('disabled');
		else {
			posA.removeClass('current');
			$('#'+(posA.attr('id')-1)).addClass('current');
			$('#next').removeClass('disabled');
		}
	}
});
//Cuando presionas boton next
$('#next').click(function(e) {
	var posA = $('.current');
	if ($(this).hasClass('disabled')) {	
	}else {
		if (posA.attr('id') == {{$numP}}) $(this).addClass('disabled');
		else {
			posA.removeClass('current');
			$('#'+(parseInt(posA.attr('id'))+1)).addClass('current');
			$('#back').removeClass('disabled');
		}
	}
});

$('#pagination span').click(function(e) {
	if ($(this).attr('id') != 'back' && $(this).attr('id') != 'next') {
		$('.current').removeClass('current');
		$(this).addClass('current');
		if($(this).attr('id') != 1)
			$('#back').removeClass('disabled');
		else 
			$('#back').addClass('disabled');
		if($(this).attr('id') == {{$numP}})
			$('#next').addClass('disabled');
		else 
			$('#next').removeClass('disabled');

		e.preventDefault();
	var param = {
		'fechaI': '{{ $fechaI }}',
		'fechaF': '{{ $fechaF }}',
		'quincena': '{{ $quincena }}',
		'tipoComisionPromotor': '{{ $tipoComisionPromotor }}',
	};

	$.ajax({
		url:"{{ route('paginacion') }}",
		type:'POST',
		dataType: 'json',
		data: param,
		success:function(data){
			$('tbody').html(data);
		}
	});	
	}
	
		
	

	
	
});
</script>

