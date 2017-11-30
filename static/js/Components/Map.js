/*
 * Description: Maps encapsulator
 * Version: 0.1
 * Author: Daniel Andrade
 * Date: 01/04/2016
 * This code may not be reused without proper permission from its creator.
 */


var GoogleMaps = new Emitter();
GoogleMaps.loaded = false;
GoogleMaps.OnLoad = function(fn){
    if (this.loaded){
        fn();
    }else{
        this.once('load', fn);
    }
};
GoogleMaps.Load = function(){
    this.loaded = true;
    this.emit('load');
    console.log('loaded gmaps')
};


/**
 * @class Overlay
 * @property boolean loaded
 * @property element
 * @property Object options
 * @property google.maps.Map map
 * @param o
 * @constructor
 */
GoogleMaps.Map = function (o){
    this.applyOptions(o, {});
    this.loaded = false;
    if (typeof this.element == 'string') this.element = $(this.element);

    this.overlays = [];


    this.Create();
};
GoogleMaps.Map.inherit(OptionsReceiver);
GoogleMaps.Map.inherit(Emitter);
GoogleMaps.Map.prototype.Create = function(){
    var that = this;
    
    GoogleMaps.OnLoad(function(){
        that.map = new google.maps.Map(that.element[0], that.options);

        that.emit('create');
    });
};
GoogleMaps.Map.prototype.Reset = function(){
    google.maps.event.trigger(this.map, 'resize');
    if (this.options.center) this.map.setCenter(this.options.center);
    if (this.options.zoom) this.map.setZoom(this.options.zoom);
};


GoogleMaps.Map.prototype.AddOverlay = function(over){
    if (this.overlays.indexOf(over)<0){
        this.overlays.push(over);
        if (over.loaded){
            this.FitOverlays();
        }else{
            var that = this;
            over.on('load', function(){
                that.FitOverlays();
            });
        }
    }
};
GoogleMaps.Map.prototype.RemoveOverlay = function(over){
    var idx = this.overlays.indexOf(over);
    if (idx>=0){
        this.overlays.splice(idx,1);

        this.FitOverlays();
    }
};
GoogleMaps.Map.prototype.ClearOverlays = function(){
    var overlays = this.overlays.concat([]);
    for (var i = 0; i < overlays.length; i++){
        var overlay = overlays[i];
        overlay.Hide();
    }
};

GoogleMaps.Map.prototype.SetDefaultViewport = function(o, show){
    this.defaultViewport = o;
    if (show) this.ShowDefaultViewport();
};
GoogleMaps.Map.prototype.ShowDefaultViewport = function(){
    this.SetViewport(this.defaultViewport);
};

GoogleMaps.Map.prototype.SetViewport = function(o){
    if (!this.map) return;
    var bounds = new google.maps.LatLngBounds(o.southwest, o.northeast);
    this.map.fitBounds(bounds);
};
GoogleMaps.Map.prototype.GetOverlaysMaxViewport = function(){
    var north = -100;
    var south = 100;
    var east = -200;
    var west = 200;
    for (var i = 0; i < this.overlays.length; i++){
        var o = this.overlays[i];
        if (!o.loaded){
            continue;
        }

        var vp = o.layer.getDefaultViewport();
        var NE = vp.getNorthEast();
        var N = NE.lat();
        var E = NE.lng();
        var SW = vp.getSouthWest();
        var S = SW.lat();
        var W = SW.lng();

        if (N > north) north = N;
        if (E > east) east = E;
        if (S < south) south = S;
        if (W < west) west = W;
    }
    if (this.overlays.length == 0){
        if (this.defaultViewport){
            return this.defaultViewport;
        }
    }

    return {northeast: {lat: north, lng: east}, southwest: {lat: south, lng: west}};
};
GoogleMaps.Map.prototype.FitOverlays = function(){
    this.SetViewport(this.GetOverlaysMaxViewport());
};




/**
 * @class Overlay
 * @property string url
 * @property boolean loaded
 * @property boolean status
 * @property google.maps.KmlLayer layer
 * @property google.maps.Map map
 * @param o
 * @constructor
 */
GoogleMaps.Overlay = function (o){
    this.applyOptions(o, {});
    this.loaded = false;

    this.Create();
};
GoogleMaps.Overlay.inherit(OptionsReceiver);
GoogleMaps.Overlay.inherit(Emitter);
GoogleMaps.Overlay.prototype.Create = function(){
    var that = this;
    GoogleMaps.OnLoad(function(){
        that.layer = new google.maps.KmlLayer({
            url: that.url,
            suppressInfoWindows: true,
            preserveViewport: true
        });

        that.emit('create');

        google.maps.event.addListener(that.layer, 'metadata_changed', function () {
            if (that.layer.getStatus()){
                that.Loaded();
            }
        });
    });
};
GoogleMaps.Overlay.prototype.Loaded = function(){
    this.loaded = true;
    this.emit('load');
};
GoogleMaps.Overlay.prototype.Draw = function(map){
    if (!this.layer){
        var that = this;
        this.once('create', function(){
            that.Draw(map);
        });

        return this;
    }

    if (map){
        this.map = map;
    }

    if (this.map instanceof GoogleMaps.Map){
        this.map.AddOverlay(this);
        map = this.map.map;
    }

    this.layer.setMap(map);

    return this;
};
GoogleMaps.Overlay.prototype.Hide = function(){
    if (!this.layer){
        var that = this;
        this.once('create', function(){
            that.Hide();
        });

        return this;
    }

    if (this.map instanceof GoogleMaps.Map) {
        this.map.RemoveOverlay(this);
    }
    this.layer.setMap(null);

    return this;
};
GoogleMaps.Overlay.prototype.Center = function(map){
    if (!this.layer){
        var that = this;
        this.once('create', function(){
            that.Center();
        });

        return this;
    }
    if (!this.loaded){
        var that = this;
        this.once('load', function(){
            that.Center();
        });

        return this;
    }

    if (!map) map = this.map;

    if (map instanceof GoogleMaps.Map) map = map.map;

    var bounds = this.layer.getDefaultViewport();
    map.fitBounds(bounds);

    return this;
};

$(function(){
    $('head').append('<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB8tl8kXvS491pyudaMDqagnt1vpLehNUQ&callback=GoogleMaps.Load&libraries=places"></script>')
});