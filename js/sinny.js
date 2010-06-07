$(document).ready(function() {
    var o = $("#phrase").offset();

    $("#phrase").css({position:'relative', margin:'0px', top:o.top, left:o.left});

    var num_entities = $("#phrase").attr('entities');    
    for (var i = 0; i < num_entities; i++) {
        $('#entity_'+i).css({position:'relative'});
    }

  var sin = new PeriodicLetterEffect($("#phrase"), {'animationRate' : 30});
  sin.cache = {};
  sin.periodicUpdate = function() {
    for (var i = 0; i < this.num_entities; i++) {
      this.affectLetter($('#entity_'+i), i);
    }
    this.num_updates += 1;        
  };

  sin.affectLetter = function(letter, dist) {
    var id = (dist+(this.num_updates % this.num_entities));
    if (sin.cache[id] == undefined) {
      sin.cache[id] = (Math.sin(id*(Math.PI * 2) / (this.num_entities) ) * 25 );
    }
    letter.css({'top' : sin.cache[id]+'px'}); 
  };
  sin.start();
});
