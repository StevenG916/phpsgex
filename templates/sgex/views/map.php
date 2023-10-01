<?php
use phpsgex\controllers\BaseController;

$controller= BaseController::$currentController;
?>

<script src="templates/js/map.js"></script>
<script id="cityTemplate" type="text/x-jquery-tmpl">
	<td 
	{{if type != null}}
	class="v1" onclick="window.location.href= '?pg=City&id=${villageId}'"
	{{/if}}
	>
	</td>
</script>
<script>
	var mapX= <?=$controller->x;?>, mapY= <?=$controller->y;?>, repeater;
	$(document).ready( function(){
		setMapOffset( <?=(int)($controller->mapSizeX /2);?>, <?=(int)($controller->mapSizeY /2);?> );
		loadMap(mapX,mapY);
	} );
</script>

<div>
	X: <input type="number" id="mapX" value="<?=$controller->x;?>" min="0" required>
	Y: <input type="number" id="mapY" value="<?=$controller->y;?>" min="0" required>
	<button class="button" onclick="moveToCoords()">Go</button>
</div>

<table class="mapBorder" style="margin-right: auto; margin-left: auto;">
	<tr><td></td><td onmouseover="repeater=setInterval(function(){loadMap(mapX,mapY-1)}, 500)" onmouseout="clearInterval(repeater)"><button class="button"> &uArr; </button></td><td></td></tr>
	<tr><td onmouseover="repeater=setInterval(function(){loadMap(mapX-1,mapY)}, 500)" onmouseout="clearInterval(repeater)"><button class="button"> &lArr; </button></td>
		<td><table class="map" id="map"></table></td>
		<td onmouseover="repeater=setInterval(function(){loadMap(mapX+1,mapY)}, 500)" onmouseout="clearInterval(repeater)"><button class="button"> &rArr; </button></td></tr>
	<tr><td></td><td onmouseover="repeater=setInterval(function(){loadMap(mapX,mapY+1)}, 500)" onmouseout="clearInterval(repeater)"><button class="button"> &dArr; </button></td><td></td></tr>
</table>