@include('index')
@if (Request::get('id') == 1)
	@php $url = route('comisionesPromotoresReport'); @endphp
@else
	@php $url = route('comisionesPromReportGnral'); @endphp
@endif

<h1 align="center">{{$title}}</h1><br><br>
<form action="{{ $url }}" method="post">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<div class="grid" style="padding: 0 5% 0 5%">
	<div class="row cells4">
		<div class="cell">
			<div class="input-control select">
				<label>Año</label>
				<select name="year" id="year" type="select" required="required">
					<option value="">Seleccione uno</option>
					<option value="2017">2017</option>
					<option value="2018">2018</option>
				</select>
			</div>
		</div>
		<div class="cell">
			<div class="input-control select">
				<label>Mes</label>
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
				<label>Quincena</label>
				<select id="quincena" name="quincena" required="required">
					<option value="">Seleccione uno</option>
					<option value="1">Primera quincena</option>
					<option value="2">Segunda quincena</option>
				</select>
			</div>
		</div>
		<div class="cell">
			<div class="input-control select">
				<label>Tipo de promotor</label>
				<select id="tipoPromotor" name="tipoPromotor" required="required">
					<option value="">Seleccione uno</option>
					<option value="BONO">Por bono</option>
					<option value="UNITARIO">Por unidad</option>
				</select>
			</div>
		</div>
	</div>
	<div class="row cells">
		<div class="cell" align="center"><button type="submit" class="button primary loading-pulse">CONSULTAR</button></div>
	</div>
</div>
</form>
