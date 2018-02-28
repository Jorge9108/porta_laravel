<div class="app-bar" data-role="appbar">
	<a class="app-bar-element branding" href="{{ route('index') }}">PORTA</a>
	<ul class="app-bar-menu">
		<li>
			<a href="" class="dropdown-toggle"><span class="mif-money icon"></span>COMISIONES</a>
			<ul class="d-menu" data-role="dropdown">
				<li><a class="fluent-button" id="comisionesPromotores" href="{{route('comisionesPromotores', ['id' => 1])}}">Comisiones de promotores</a></li>
				<li><a class="fluent-button" id="comisionesPromGnral" href="{{route('comisionesPromotores', ['id' => 2])}}">Relacion de pagos promotor</a></li>
			</ul>
		</li>
	</ul>
	<div class="app-bar-pullbutton automatic"></div>
</div>
