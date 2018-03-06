<div class="app-bar" data-role="appbar">
	<a class="app-bar-element branding" href="{{ route('index') }}">PORTA</a>
	<ul class="app-bar-menu">
		<li>
			<a href="" class="dropdown-toggle">COMISIONES</a>
			<ul class="d-menu" data-role="dropdown">
				<li><a class="fluent-button" id="comisionesPromotores" href="{{route('comisionesPromotores')}}">Comisiones de promotores</a></li>
				
			</ul>
		</li>
		<li>
			<a href="" class="dropdown-toggle">ADMON</a>
			<ul class="d-menu" data-role="dropdown">
				<li><a class="fluent-button" id="comisionesPromotores" href="{{route('generaComision')}}">Porcentajes de ciudades</a></li>
			</ul>
		</li>
	</ul>
	<div class="app-bar-pullbutton automatic"></div>
</div>
