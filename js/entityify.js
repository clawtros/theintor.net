var effects = [];
var EID = 0;

var PeriodicLetterEffect = function(element, user_options) {

  effects[EID] = this;
  this.id = EID;
  this.num_updates = 0;
  this.current_entity = 0;
  this.num_entities = element.attr('entities');
  EID++;

  if (this.num_entities) 
    this.delta = 255 / this.num_entities;

  this.opts = { 
    'animationRate' : 50
  };

  $.extend(this.opts, user_options);

  this.effectStrength = function(dist) {
    var d = Math.abs(dist);
    return Math.round((this.num_entities * this.delta) - (d * this.delta));
  };

  this.affectLetter = function(letter, dist) {
    var cstrength = this.effectStrength(dist);
    letter.css("color","rgb("+cstrength+",50,25)");
  };
  
  this.periodicUpdate = function() {
    for (var i = 0; i < this.num_entities; i++) {
      var dist = i - this.current_entity;
      this.affectLetter($('#entity_'+i), dist);
    }
    if (this.current_entity == this.num_entities) position_delta = -1;
    if (this.current_entity == 0) position_delta = 1;
    this.current_entity += position_delta;

    this.num_updates += 1;
  };

  this.start = function() {
    this.interval = setInterval('effects['+this.id+'].periodicUpdate()', this.opts.animationRate);
  };
  
  this.stop = function() {
    clearInterval(this.interval);
  };
}



jQuery.fn.extend({
  entityify: function(opts) {
    function string_to_entities(s) {
      return s.split("");
    }
    
    function idify_entities(entities) {
      var replacement = "";
      for (var i in entities) {
        if ($(
        replacement += '<span id="entity_'+i+'" class="entity">'+entities[i]+'</span>';
      }
      return replacement;
    }

    function to_typing_array(phrase) {
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
    
    var options = {};
    $.extend(options, opts);

    if (this.attr('entitied') != 'true') {
      var es = to_typing_array(this.html());
      this.html(idify_entities(es));
      this.attr('entitied','true');
      this.attr('entities',es.length);
    }
    
  }
});



$(document).ready(function() {      
  $("#phrase").entityify({ 'prefix' : 'entity_' });
});
