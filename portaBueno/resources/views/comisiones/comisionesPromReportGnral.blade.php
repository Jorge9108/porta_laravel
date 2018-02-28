@include('index')

<div class="example" data-text="comisiones de promotores">
	<div class="grid">
		<h3 align="center">Reporte general de comisiones promotores</h3>
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
			@if($tipoComisionPromotor == 'BONO')
				<tr>
				<th class="sortable-column sort-asc" id="nombreP">Nombre de promotor</th>
				<th class="sortable-column" id="ciudad">Ciudad</th>
				<th class="sortable-column" id="cuenta">Cuenta</th>
				<th class="sortable-column" id="sueldoB">Sueldo base</th>
				<th class="sortable-column" id="portasE">Exitosas</th>
				<th class="sortable-column" id="portasP">Pagar</th>
				<th class="sortable-column" id="tipoP">Tipo promotor</th>
				<th class="sortable-column" id="bono">Bono<br>21-22</th>
				<th class="sortable-column" id="bono2">Bono<br>26-27</th>
				<th class="sortable-column" id="bono3">Bono<br>31-32</th>
				<th class="sortable-column" id="desc">Descuento</th>
				<th class="sortable-column" id="total">Total</th>
				<th class="sortable-column">Archivo</th>
				</tr>
			@elseif($tipoComisionPromotor == 'UNITARIO')
				<tr>
				<th class="sortable-column sort-asc" id="nombreP">Nombre de promotor</th>
				<th class="sortable-column" id="ciudad">Ciudad</th>
				<th class="sortable-column" id="cuenta">Cuenta</th>
				<th class="sortable-column" id="sueldoB">Sueldo base</th>
				<th class="sortable-column" id="portasE">Exitosas</th>
				<th class="sortable-column" id="portasP">A pagar</th>
				<th class="sortable-column" id="tipoP">Tipo promotor</th>
				<th class="sortable-column" id="pagoU">Pago unitario</th>
				<th class="sortable-column" id="desc">Descuento</th>
				<th class="sortable-column" id="total">Total</th>
				<th class="sortable-column">Archivo</th>
				</tr>
			@endif
		</thead>
		<tbody>
			<?php $cont = 0; $cU = 0; $contU = 0;?>
			@forelse($comisionesConcentrado as $comCon => $val)
			<?php $varI = 0; $varF = $varI+3; $vFU = $varI+4; ?>
				<tr>
				@if($val->estatus != 1 && $val->estatus != 2)
					<td style="color: red">{{ $val->StrApellidoPa }} {{ $val->StrApellidoMa }} {{ $val->StrNombre }}</td>
				@else
					<td>{{ $val->StrApellidoPa }} {{ $val->StrApellidoMa }} {{ $val->StrNombre }}</td>
				@endif
				<td>{{ $val->StrCiudad }}</td>
				<td>{{ $val->num_cuenta }}</td>
				<td>{{ '$'.$val->sueldo }}</td>
				<td>{{ $val->portas_num }}</td>
				@if($val->porcentaje < 60)
					<td style="color: red">{{ $val->total_pagar }}</td>
				@else
					<td>{{ $val->total_pagar }}</td>
				@endif
				<td>{{ $val->tipo_promotor }}</td>
				@php $total = 0; @endphp
				@if($tipoComisionPromotor == 'BONO')	
					@foreach(array_splice($tbCom, $varI, $varF) as $key)	
						@if($val->total_pagar >= $key)
							<td>{{ '$'.$bonos[$cont] }} </td>
							@php $total += $bonos[$cont]; @endphp
						@else
							<td></td>
						@endif
						<?php $cont++; $varI++; ?>
					@endforeach
					<td>{{ '$'.$array[$comCon] }}</td>
					<td>{{ '$'.($total-$array[$comCon])}}</td>
				@elseif($tipoComisionPromotor == 'UNITARIO')
					@foreach(array_splice($tbCom, $varI, $vFU) as $key)
						@if($val->total_pagar >= $key && $val->total_pagar <= $tbCom2[$contU])
							@php $cU = $bonos[$contU]; @endphp
						@endif
						<?php $contU++; $varI++; ?>
					@endforeach
					<td>{{ '$'.($cU * $val->total_pagar) }}</td>
					<td>{{ '$'.$array[$comCon] }}</td>
					<td>{{ '$'.(($cU * $val->total_pagar) - $array[$comCon])}}</td>
				@endif			
					<td align="center"><a href="comisionesPromotoresDownload/{{$val->StrApellidoPa}}/{{$val->StrApellidoMa}}/{{$val->StrNombre}}/{{$val->StrCiudad}}/{{$fechaI}}/{{$fechaF}}/{{$fechaInicialI}}/{{$fechaFinalI}}/{{$val->StrNom_corto}}/{{$val->SupAsignado}}/{{$val->total_pagar}}/{{$val->num_movistar}}/{{$val->porcentaje}}/{{$val->portas_num}}/{{$quincena}}"><span class="mif-download2 mif-lg" data-role="hint" data-hint-background="bg-blue" data-hint-color="fg-white" data-hint-mode="2" data-hint="Exportar reporte"></span></a>
				</tr>
			@empty
				<h1>No hay datos en aun</h1>
			@endforelse
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
