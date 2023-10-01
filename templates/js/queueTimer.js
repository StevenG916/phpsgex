function timeDigit(digit){
    digit= digit.toString();
    if( digit.length == 1 ) digit= "0" + digit;
    return digit;
}

function timer(element){
    var time= element.text().split(":");

    var secs= parseInt(time[2]), mins= parseInt(time[1]), hours= parseInt(time[0]);
    if( secs > 0 ) secs--;
    else {
        secs= 59;
        if(mins > 0) mins--;
        else {
            hours--;
            mins= 59;
        }
    }

    if( hours >= 0 )
        element.text( timeDigit(hours)+":"+timeDigit(mins)+":"+timeDigit(secs) );
    //else location.reload();
}

function tim(elem){
	var countDownDate = new Date(elem.data("time")).getTime();

	var x = setInterval(function(){
		var now = new Date().getTime();

		// Find the distance between now an the count down date
		var distance = countDownDate - now;
		
		// Time calculations for days, hours, minutes and seconds
		var days = Math.floor(distance / (1000 * 60 * 60 * 24));
		var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
		var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
		var seconds = Math.floor((distance % (1000 * 60)) / 1000);
		
		// Output the result in an element with id="demo"
		elem.text(days + "d " + hours + "h " + minutes + "m " + seconds + "s ");
		
		// If the count down is over, write some text 
		if (distance < 0) {
			clearInterval(x);
			elem.text("Completo");
			window.location.reload();
		}
	}, 100);
}