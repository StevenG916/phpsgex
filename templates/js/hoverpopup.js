var init= false;

function initPopup(){
    $(".popupHover").each( function(){
        attachOverPopup( $($(this).data("target")), $(this) );
    } );
}

function attachOverPopup( element, popup ){
    element.mousemove( function(event){
        popup.show();
        popup.css("top", event.pageY -window.pageYOffset +15);
        popup.css("left", event.pageX +15);
    } );

    element.mouseleave( function(){
        popup.hide();
    } );
}