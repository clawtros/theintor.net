var typing_position = 0;
var blink_interval;
var destination;
var typing_array = [];

var to_typing_array = function(p) {
  var result = [];
  var storage = [];
  for (var i in phrase) {
      var character = phrase[i];
      if (!character.match(/[\<\&]/) && storage.length == 0) {
          result.push(phrase[i]);
      } else {
          storage.push(character);
          if (character.match(/[\>\;]/)) {
              result.push(storage.join(""));
              storage = [];
          }
      }
  }
  
  return result;
}

typing_array = to_typing_array(phrase);

function type_character() {
    $("#typing_location").html(typing_array.slice(0,typing_position++).join(""));
    if (typing_position <= typing_array.length) { 
        if (typing_array[typing_position-1].length > 1) {
            type_character();
        } else {
            setTimeout(type_character, Math.floor(Math.random()*240)+50);
        }
    } else {
        //$("#cursor").hide();
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
    setInterval(blink_cursor, 750);
    setTimeout(type_character, 150);
}

$(document).ready(function() {
    $("#phrase").html('<div id="type"></div>');    

    destination = $("#type");
    start_typing();
});
