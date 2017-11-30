if (isModule.bind(this)()){
    var Emitter = require('./../General').Emitter;

    require("./slideshow.css");
}

function SlideShow(images){
    this.images = [];
    this.currentIndex = null;
    this.visible = false;
    this.zoom = false;

    this.Render();
    this.SetImages(images);
}
SlideShow.prototype.Render = function(){
    this.element = $("<div></div>").attr("class", 'slideshow').css('display', 'none');

    this.header = $("<div></div>").attr("class", 'slideshowHeader');
    this.element.append(this.header);
    this.title = $("<div></div>").css("float", 'left');
    this.header.append(this.title);

    this.imageHolder = $("<div></div>").attr("class", 'imageHolder');
    this.element.append(this.imageHolder);

    this.nextElement = $("<div></div>").attr("class", 'next').append($("<span></span>").html(">"));
    this.element.append(this.nextElement);
    this.prevElement = $("<div></div>").attr("class", 'prev').append($("<span></span>").html("<"));
    this.element.append(this.prevElement);

    this.closeElement = $("<div></div>").attr("class", 'close').html("x");
    this.element.append(this.closeElement);

    this.zoomElement = $("<div></div>").attr("class", 'zoomBtn').append(
        $("<span />").attr('src', 'glyphicon glyphicon-zoom-in').css("width", 32).css("height", 32)
    );
    this.element.append(this.zoomElement);

    var slideShow = this;

    // Thumbs
    this.thumbs = $("<div></div>").attr("class", 'thumbnails');
    this.element.append(this.thumbs);

    this.header.append(this.title);

    $("body").append(this.element);

    // Social
    for(i in SlideShow.SocialIcons){
        SlideShow.SocialIconElements[i] = $("<a />").attr("class", 'social').attr("href", "#").css("display", "none").append(
            $("<img />").attr('src', SlideShow.IconsUrl+SlideShow.SocialIcons[i])
        );
        this.header.append(SlideShow.SocialIconElements[i]);
    }

    //Events
    this.element.on('click', ".next", function(){
        slideShow.Next();
        window.focus();
    });
    this.element.on('click', ".prev", function(){
        slideShow.Prev();
        if (document.focus) document.focus();
    });


    window.onresize = function(){
        slideShow.OnResize();
    };

    $(document)
        .on("keyup", function(e){
            if (slideShow.visible){
                if (e.which == 37){
                    //Left arrow
                    slideShow.Prev();
                }else if (e.which == 39){
                    //Right arrow
                    slideShow.Next();
                }else if (e.which == 27){
                    //Esc
                    slideShow.Close();
                }
            }
        });
    this.imageHolder
        .on("touchstart", function(e){
            if (slideShow.visible && slideShow.zoom){
                var x = e.originalEvent.touches[0].clientX;
                var y = e.originalEvent.touches[0].clientY;
                slideShow.PositionZoomedImage(x,y);
            }
        })
        .on("touchmove", function(e){
            if (slideShow.visible && slideShow.zoom){
                var x = e.originalEvent.touches[0].clientX;
                var y = e.originalEvent.touches[0].clientY;
                slideShow.PositionZoomedImage(x,y);
            }
        })
        .on("mousemove", function(e){
            if (slideShow.visible && slideShow.zoom){
                var x = e.pageX;
                var y = e.pageY;
                slideShow.PositionZoomedImage(x,y);
            }
        })
        .on("touchstop", function(e){
        })
        .on("swipeleft", function(e){
            if (slideShow.visible && !slideShow.zoom){
                slideShow.Next();
            }
        })
        .on("swiperight", function(e){
            if (slideShow.visible && !slideShow.zoom){
                slideShow.Prev();
            }
        })
        .on("swipedown", function(e){
            if (slideShow.visible && !slideShow.zoom){
                slideShow.Close();
            }
        });
    this.closeElement.on("click", function(){slideShow.Close();})
    this.zoomElement.on("click", function(){slideShow.Zoom();})
};
SlideShow.prototype.SetImages = function (images) {
    var slideShow = this;

    this.images = [];

    if (images){
        for(var i = 0; i < images.length; i++){
            var img = images[i];
            if (!(img instanceof SlideShowImage)){
                img = new SlideShowImage(img[0],img[1],img[2],img[3],img[4]);
            }
            this.images.push(img);
        }
    }

    this.thumbs.html('');
    for (i = 0; i < this.images.length; i++){
        img = this.images[i];

        img.thumbEl = $("<img />").attr('src', img.thumb).attr("data-index", i).on("click", function(){
            var index = $(this).attr("data-index");
            slideShow.SetImage(slideShow.images[index], -1);
        });

        this.thumbs.append(img.thumbEl);
    }
};


SlideShow.prototype.Open = function(index){
    this.visible = true;
    this.element.css('display','block');
    this.OnResize();
    this.SetImage(this.images[index ? index : 0]);
    $("body").css('overflow', 'hidden');
}
SlideShow.prototype.Close = function(){
    this.ZoomStop();
    this.visible = false;
    this.currentIndex = null;
    this.element.css('display','none');
    $("body").css('overflow', 'auto');
}

SlideShow.prototype.SetImage = function(image, direction){
    var slideshow = this;
    function UpdateView(){
        slideshow.imageHolder.html('');
        slideshow.currentIndex = slideshow.images.indexOf(image);
        slideshow.SelectThumbnail(image);
        slideshow.SetTitle(image);
        slideshow.SetSocial(image);
        slideshow.header.fadeIn(300);
        slideshow.zoomElement.css('display', image.zoomable ? 'block' : 'none');

        slideshow.DisplayImage(image, function(){
            image.ShantayYouStay(direction*-1);
        });
    }

    if (this.currentIndex===null){
        UpdateView();
    }else{
        this.ZoomStop();
        this.header.fadeOut(300);
        var cImage = this.images[this.currentIndex];
        cImage.SashayAway(direction,function(){
            UpdateView();
        })
    }
};
SlideShow.prototype.SetTitle = function(image){
    this.title.html("<div class='title'>"+image.title+"</div><br/>Image "+(this.currentIndex+1)+" of "+this.images.length);
};
SlideShow.prototype.SetSocial = function(image){
    $(".social", this.header).css("display", "none");
    for(var i in image.socialLinks){
        SlideShow.SocialIconElements[i].attr("href", image.socialLinks[i]).css("display", "inline");
    }
};
SlideShow.prototype.SelectThumbnail = function(image){
    $('.current', this.thumbs).removeClass('current');
    image.thumbEl.addClass('current');
    var scrollLeft = this.thumbs.scrollLeft();
    var scrollTop = this.thumbs.scrollTop();
    var offset = image.thumbEl.position();

    var width = this.thumbs.width();
    var thumbWidth = image.thumbEl.width();
    var height = this.thumbs.height();
    var thumbHeight = image.thumbEl.height();

    var left = (scrollLeft+offset.left) - (width/2) + (thumbWidth/2);
    var top = (scrollTop+offset.top) - (height/2) + (thumbHeight/2);
    if (left < 0) left = 0;
    if (top < 0) top = 0;

    this.thumbs.animate({scrollLeft: left, scrollTop: top}, 300);
};
SlideShow.prototype.DisplayImage = function(image, onShow){
    if (image.loaded){
        if (this.currentIndex == this.images.indexOf(image)){
            this.ResizeImage(image);
            this.imageHolder.append(image.element);
            if (onShow) onShow();
        }
    }else{
        var slideShow = this;
        image.Load(function(){
            slideShow.DisplayImage(image, onShow);
        });
    }
};
SlideShow.prototype.OnResize = function(){
    var h = $(window).height();
    var w = $(window).width();
    if ((w-120)*(h-50) > (w)*(h-150)){
        this.element.addClass("horizontal");
    }else{
        this.element.removeClass("horizontal");
    }
    if(this.currentIndex !== null) this.ResizeImage(this.images[this.currentIndex]);
};
SlideShow.prototype.ResizeImage = function(image){
    if(!image) image = this.images[this.currentIndex];
    var h = this.imageHolder.height();
    var w = this.imageHolder.width();
    image.Resize(w, h);
};

SlideShow.prototype.Next = function(){
    var next = (this.currentIndex+1)%this.images.length;
    this.SetImage(this.images[next], -1);
};
SlideShow.prototype.Prev = function(){
    var prev = (this.currentIndex-1)>=0 ? (this.currentIndex-1) : (this.images.length-1);
    this.SetImage(this.images[prev], 1);
};

SlideShow.prototype.Zoom = function(){
    if (!this.zoom){
        this.ZoomStart();
    }else{
        this.ZoomStop();
    }
}
SlideShow.prototype.ZoomStart = function(){
    this.zoom = true;
    this.element.addClass("zoom");
    this.PositionZoomedImage(0,0);
}
SlideShow.prototype.ZoomStop = function(){
    this.zoom = false;
    this.element.removeClass("zoom");
    this.ResizeImage();
}
SlideShow.prototype.PositionZoomedImage = function(x, y){
    var image = this.images[this.currentIndex];

    var h = this.imageHolder.height();
    var w = this.imageHolder.width();

    var margin = w*.1;

    h = h-margin;
    w = w-margin;
    var ratioX = (x)/w;
    var ratioY = (y)/h;
    var deltaX = image.width > w ? image.width - w : 0;
    var deltaY = image.height > h ? image.height - h : 0;
    var offsetX = Math.round(deltaX * ratioX)-margin;
    var offsetY = Math.round(deltaY * ratioY)-margin;

    image.element
        .css("width", image.width)
        .css('height', image.height)
        .css('marginLeft', -offsetX)
        .css('marginTop', -offsetY);
}
SlideShow.IconsUrl = "";
SlideShow.SocialIcons = {};
SlideShow.SocialIconElements = {};


function SlideShowImage(url, title, thumb, zoomable, socialLinks){
    this.url = url;
    this.thumb = thumb ? thumb : url;
    this.title = title;
    this.zoomable = zoomable ? true : false;
    this.socialLinks = socialLinks;
    this.image = new Image();
    this.element = $(this.image);

    this.width = 0;
    this.height = 0;

    this.cWidth = 0;
    this.cHeight = 0;

    this.loaded = false;
}
SlideShowImage.inherit(Emitter);
SlideShowImage.prototype.Load = function(onComplete){
    // Set an alias to "this" to be referenceable in anonymous functions
    var obj = this;

    this.image.onload = function() {
        obj.width = this.width;
        obj.height = this.height;
        obj.loaded = true;

        obj.emit('load');
        if (onComplete) onComplete();
    };

    this.image.src = this.url;
};
SlideShowImage.prototype.Resize = function(maxW, maxH){
    if ((this.height/maxH) < (this.width/maxW)){
        this.cWidth = maxW;
        this.cHeight = Math.floor((this.height / this.width)*maxW);
    }else{
        this.cWidth = Math.floor((this.width / this.height)*maxH);
        this.cHeight = maxH;
    }

    var halfWidth = Math.floor(this.cWidth/2)*-1;
    var halfHeight = Math.floor(this.cHeight/2)*-1;

    this.element
        .css("width", this.cWidth)
        .css('height', this.cHeight)
        .css('marginLeft', halfWidth)
        .css('marginTop', halfHeight);
};
SlideShowImage.prototype.SashayAway = function(direction, onComplete){
    var startingLeft = 50;
    var finalLeft = (startingLeft + (direction*100));
    this.element.css('left', startingLeft+"%").animate({left: finalLeft+"%"}, 300, onComplete);
};
SlideShowImage.prototype.ShantayYouStay = function(direction, onComplete){
    var finalLeft = 50;
    var startingLeft = (finalLeft + (direction*100));
    this.element.css('left', startingLeft+"%").animate({left: finalLeft+"%"}, 300, onComplete);
};


(function() {
    var supportTouch = $.support.touch,
        scrollEvent = "touchmove scroll",
        touchStartEvent = supportTouch ? "touchstart" : "mousedown",
        touchStopEvent = supportTouch ? "touchend" : "mouseup",
        touchMoveEvent = supportTouch ? "touchmove" : "mousemove";
    $.event.special.swipeupdown = {
        setup: function() {
            var thisObject = this;
            var $this = $(thisObject);
            $this.bind(touchStartEvent, function(event) {
                var data = event.originalEvent.touches ?
                        event.originalEvent.touches[ 0 ] :
                        event,
                    start = {
                        time: (new Date).getTime(),
                        coords: [ data.pageX, data.pageY ],
                        origin: $(event.target)
                    },
                    stop;

                function moveHandler(event) {
                    if (!start) {
                        return;
                    }
                    var data = event.originalEvent.touches ?
                        event.originalEvent.touches[ 0 ] :
                        event;
                    stop = {
                        time: (new Date).getTime(),
                        coords: [ data.pageX, data.pageY ]
                    };

                    // prevent scrolling
                    if (Math.abs(start.coords[1] - stop.coords[1]) > 10) {
                        event.preventDefault();
                    }
                }
                $this
                    .bind(touchMoveEvent, moveHandler)
                    .one(touchStopEvent, function(event) {
                        $this.unbind(touchMoveEvent, moveHandler);
                        if (start && stop) {
                            if (stop.time - start.time < 1000 &&
                                Math.abs(start.coords[1] - stop.coords[1]) > 30 &&
                                Math.abs(start.coords[0] - stop.coords[0]) < 75) {
                                start.origin
                                    .trigger("swipeupdown")
                                    .trigger(start.coords[1] > stop.coords[1] ? "swipeup" : "swipedown");
                            }
                        }
                        start = stop = undefined;
                    });
            });
        }
    };
    $.each({
        swipedown: "swipeupdown",
        swipeup: "swipeupdown"
    }, function(event, sourceEvent){
        $.event.special[event] = {
            setup: function(){
                $(this).bind(sourceEvent, $.noop);
            }
        };
    });
})();


if (isModule()) {
    module.exports.default = SlideShow;
    module.exports.SlideShow = SlideShow;
    module.exports.SlideShowImage = SlideShowImage;
}