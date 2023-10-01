var offsetX= 0, offsetY= 0;

function setMapOffset(offX, offY){
	offsetX= offX;
	offsetY= offY;
}

function loadMap(x,y){
    mapX= x; mapY= y;
	
	x -= offsetX; y -= offsetY;
	
    $.getJSON("?pg=Map&act=Get&x="+x+"&y="+y, function(result){
        $("#map").empty();
        result.forEach( function(row){
            var tr= $("<tr></tr>");
            tr.appendTo("#map");
            $("#cityTemplate").tmpl(row).appendTo(tr);
        } );
    });
}

function moveToCoords(){
    loadMap( $("#mapX").val(), $("#mapY").val() );
}