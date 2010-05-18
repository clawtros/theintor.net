var typing_position = 0;
var blink_interval;
var destination;

function type_character() {
    $("#typing_location").append(phrase[typing_position++]);
    if (typing_position < phrase.length) { 
        setTimeout(type_character, Math.floor(Math.random()*500)+150);
    } else {
        $("#cursor").hide();
    }
}

function blink_cursor() {
    if ($("#cursor").css('visibility') == 'visible') { 
        $("#cursor").css("visibility", "hidden");       
    } else {
        $("#cursor").css("visibility", "visible");       
    }
}

function start_typing() {
    $(destination).html('<span id="typing_location"></span><span id="cursor">|</span>');
    setInterval(blink_cursor, 300);
    setTimeout(type_character, 150);
}

$(document).ready(function() {
    $("#phrase").html('<div id="type"></div>');    
    destination = $("#type");
    start_typing();
});
