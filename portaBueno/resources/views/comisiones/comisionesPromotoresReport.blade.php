@include('index')

<div class="example" data-text="comisiones de promotores">
	<h3 align="center">Reporte de comisiones promotores</h3>
	<h4 align="center">{{ $fechaI.' al '.$fechaF }}</h4>
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
			<?php $porcientoMov = round($array[$capturas]/$value->estatus_solicitud * 100, 0); $totalPagar = $value->estatus_solicitud; ?>
				<tr>
				<td>{{ $value->StrApellidoPa }} {{ $value->StrApellidoMa }} {{ $value->StrNombre }}</td>
				<td align="center">{{ $value->estatus_solicitud }}</td>
				<td align="center">{{ $array[$capturas] }}</td>
				@if ($porcientoMov >= 60)
				<td align="center">{{ $porcientoMov }}%</td>
				<td align="center">{{ $totalPagar }}</td>
				@else
				<?php $totalPagar = $array[$capturas] + (round($array[$capturas] / 0.6, 0) - $array[$capturas]); ?>
				<td align="center" style="color: red">{{ $porcientoMov }}%</td>
				<td align="center" style="color: red">{{ $totalPagar }}</td>
				@endif
				<td>{{ $value->StrCiudad }}</td>
				<td align="center"><a href="comisionesPromotoresDownload/{{$value->StrApellidoPa}}/{{$value->StrApellidoMa}}/{{$value->StrNombre}}/{{$value->StrCiudad}}/{{$fechaI}}/{{$fechaF}}/{{$fechaInicialI}}/{{$fechaFinalI}}/{{$value->StrNom_corto}}/{{$value->SupAsignado}}/{{$totalPagar}}/{{$array[$capturas]}}/{{$porcientoMov}}/{{$value->estatus_solicitud}}/{{$quincena}}"><span class="mif-download2 mif-lg" data-role="hint" data-hint-background="bg-blue" data-hint-color="fg-white" data-hint-mode="2" data-hint="Exportar reporte"></span></a></td>
				</tr>
			@endforeach
		</tbody>
	</table>
	
</div>
<script>
$(document).ready(function(){
	$("th").click(function () {
		id = $(this).attr('id');
		if ($('#'+id).hasClass('sort-asc')) {
			$('th').removeClass("sort-asc");
			$('#'+id).addClass("sort-desc");
		}else {
			$('th').removeClass("sort-desc");
			$('th').removeClass("sort-asc");
			$('#'+id).addClass("sort-asc");
		}
	});
});
</script>
