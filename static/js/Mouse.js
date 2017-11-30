var Mouse = new Emitter();

Mouse.UpdateFixed = function(){
    this.FixedX = this.X - $(window).scrollLeft();
    this.FixedY = this.Y - $(window).scrollTop();
};

$(window)
    .bind('touchmove', function(jQueryEvent) {
        jQueryEvent.preventDefault();
        var event = window.event;

        Mouse.X = event.touches[0].pageX;
        Mouse.Y = event.touches[0].pageY;
        Mouse.UpdateFixed();
        Mouse.emit('move', [jQueryEvent]);
    })
    .bind('mousemove', function(e){
        Mouse.X = e.pageX;
        Mouse.Y = e.pageY;
        Mouse.UpdateFixed();
        Mouse.emit('move', [e]);
    });