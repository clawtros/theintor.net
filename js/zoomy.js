$(document).ready(function() {
    var osize = parseInt($("#phrase").css('font-size'));
    var s = new PeriodicLetterEffect($("#phrase"), {'animationRate' : 50});
    s.affectLetter = function(letter, dist) {
        var strength = osize * ((this.num_entities - Math.abs(dist)) / this.num_entities);
        letter.css('font-size', strength+'px'); 
    };
    s.start();
}); 
