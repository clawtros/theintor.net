var updateshadow = function(e) {
    var mx = e.pageX;
    var my = e.pageY;

    //$($(".entity").get().reverse()).each(function(i, span) {
    $(".entity").each(function(i, span) {
        var ent = $(span);
        var ex = ent.offset().left + ent.width()/2;
        var ey = ent.offset().top + ent.height()/2;
        ent.css("text-shadow", "#333 " + (ex-mx)/2 + "px " + (ey-my)/2 + "px " + " "+ Math.sqrt((ex-mx)*(ex-mx) + (ey-my)*(ey-my))*0.06 +"px");
        //ent.css("color", "transparent");
    });
};


$(document).ready(function() {    
    $(document).mousemove(updateshadow);
});
