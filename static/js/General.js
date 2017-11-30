window.isModule = function () {
    return typeof module !== 'undefined' && typeof module.exports !== 'undefined';
};

RegExp.Email = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;


/**
 * Generates a random integer
 * @param max
 * @param min
 * @returns {number}
 */
Math.randomInt = function(max, min){
    if(!min) min = 0;
    return Math.floor(Math.random()*(max-min))+min;
};

/**
 * Pads a string (e.g.: "9" may become "009" and "10" "010").
 * @param character
 * @param size
 * @param [right]
 * @returns {String}
 */
String.prototype.pad = function (character, size, right) {
    var s = this+"";
    if (!right){
        while (s.length < size) s = character + s;
    }else{
        while (s.length < size) s = s + character;
    }
    return s;
};

String.prototype.format = function(values, pattern){
    if (!pattern) pattern = function (key){ return '{'+key+'}'; };
    
    var final = this.toString();
    for (var i in values){
        if (values.hasOwnProperty(i)){
            var match = pattern;
            if (typeof pattern == 'string'){
                match = pattern.replace('?', i);
            }else if (pattern instanceof Function){
                match = pattern(i);
            }

            final = final.replace(
                new RegExp(RegExp.escape(match), 'g'),
                values[i]
            );
        }
    }

    return final;
};

String.prototype.nl2br = function(){
    return this.replace(/\n/g, '<br>');
};

RegExp.escape = function (str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"); // $& means the whole matched string
};

Number._decimalChar = '.';
Number.setDecimalChar = function (val) {
    this._decimalChar = val;
};
Number.prototype.pad = function (size, decimalSize, decimalChar) {
    if (!decimalChar) decimalChar = Number._decimalChar;

    var negative = this < 0;
    var val = Math.abs(this);

    var str = val.toString();
    str = str.split(".");

    var result = str[0].pad("0", size ? size : 0);

    if(decimalSize && str.length == 1){
        str[1] = '0';
    }

    if (str.length==2){
        result += decimalChar + str[1].pad("0", decimalSize, true);
    }

    if (negative) result = "-"+result;

    return result;
};


Array.prototype.selfConcat = function () {
    for (var i = 0; i < arguments.length; i++){
        var a = arguments[i];
        if (a instanceof Array){
            this.push.apply(this, a);
        }
    }
};
Array.prototype.removeElements = function (elements) {
    if (!(elements instanceof Array)) elements = [elements];

    var that = this;
    elements.forEach(function(e) {
        for(var i=that.length-1; i>=0; i--) {
            if(that[i]==e) { that.splice(i,1); }
        }
    });

    return this;
};
Array.prototype.flatten = function() {
    return this.reduce(function (flat, toFlatten) {
        return flat.concat(Array.isArray(toFlatten) ? toFlatten.flatten() : toFlatten);
    }, []);
};

/**
 * Simple prototype inheritance
 * @param {Function} from
 */
Function.prototype.inherit = function(from){
    var thisProto = this.prototype;
    var fromProto = from.prototype;
    for (var i in fromProto){
        if (i != 'constructor' && fromProto.hasOwnProperty(i)){
            thisProto[i] = fromProto[i];
        }
    }

    if (!fromProto.__parent){
        thisProto.__parent = [from];
    }else{
        thisProto.__parent = fromProto.__parent.concat([from]);
    }

    thisProto.Parent = Function.prototype.Parent;

    for (i in from){
        if (from.hasOwnProperty(i)){
            this[i] = from[i];
        }
    }
};
/**
 * Invokes a parent class method
 * @param {string|null} method Method name
 * @param {Array} args Method arguments
 * @param {Function} sourceClass The class that originated the call
 * @constructor
 */
Function.prototype.Parent = function(method, args, sourceClass){
    var parent;
    if (sourceClass === this.constructor){
        parent = this.__parent[this.__parent.length-1];
    }else{
        var idx = this.__parent.indexOf(sourceClass);
        if (idx > 0){
            parent = this.__parent[idx-1];
        }
    }
    if (!parent){
        throw "Couldn't find source class in inheritance stack";
    }

    if (parent && !method) {
        parent.apply(this, args);
    }else if (parent && parent.prototype[method]){
        if (parent.prototype[method] instanceof Function)
            return parent.prototype[method].apply(this, args);
    }
};

/**
 * @param obj
 * @returns {boolean}
 */
Function.prototype.IsInstance = function(obj){
    if (obj.constructor === this) return true;
    if (obj.__parent){
        return obj.__parent.indexOf(this) !== -1;
    }
    return false;
};
Function.prototype.IsParent = function(constructor){
    if (constructor === this) return true;
    if ((constructor instanceof Function) && constructor.prototype.__parent){
        return constructor.prototype.__parent.indexOf(this) !== -1;
    }
    return false;
};


function HasUniqueId() {}
HasUniqueId.prototype.GetUId = function () {
    if (!this.__uid){
        this.__uid = Date.now() + '_' + Math.randomInt(9999999).pad(7);
    }
    return this.__uid;
};

/**
 * Emitter class
 * @extends HasUniqueId
 * @constructor
 */
function Emitter(){}
Emitter.inherit(HasUniqueId);
Emitter.AnyEvent = '*';
Emitter.prototype.on = function(event, fn, key, once){
    if (!this.__boundEvents) this.__boundEvents = {};
    if (!this.__boundEvents[event]) this.__boundEvents[event] = [];

    this.__boundEvents[event].push({callback: fn, key: key, once: once});

    return this;
};
Emitter.prototype.onAny = function(fn, key, once){
    this.on(Emitter.AnyEvent, fn, key, once);

    return this;
};
Emitter.prototype.off = function(event, fn){
    if (!this.__boundEvents || !this.__boundEvents[event]) return this;

    if (fn instanceof Array){
        for (var i = 0; i < fn.length; i++){
            this.off(event, fn[i]);
        }
    }else if (fn){
        var idx;

        if (fn.callback instanceof Function){
            idx = this.__boundEvents[event].indexOf(fn);
        } else {
            fn = this.__boundEvents[event].filter(function(e){return e.key === fn});
            if (fn.length)
                idx = this.__boundEvents[event].indexOf(fn[0]);
        }

        if (idx >= 0)
            this.__boundEvents[event].splice(idx, 1);
    }else{
        this.__boundEvents[event] = [];
    }

    return this;
};
Emitter.prototype.emit = function(event, args){
    if (this.__boundEvents && this.__boundEvents[event]){
        var removeElements = [];

        var eventData = this.__boundEvents[event].concat([]);

        for (var i = 0; i < eventData.length; i++){
            var data = eventData[i];
            data.callback.apply(this, args);
            if (data.once) removeElements.push(data);
        }

        this.off(event, removeElements);
    }
    if (event !== Emitter.AnyEvent && this.__boundEvents && this.__boundEvents[Emitter.AnyEvent]){
        this.emit(Emitter.AnyEvent, ([event]).concat(args));
    }

    return this;
};
Emitter.prototype.once = function(event, fn, key){
    this.on(event, fn, key, true);

    return this;
};


function Templatable(){}
/**
 * Get a template compiled
 * @param key
 * @param [data]
 * @return {*|jQuery|HTMLElement}
 * @constructor
 */
Templatable.prototype.Template = function (key, data) {
    if (this.constructor.templates){
        if (!data) data = {};

        var r = this.constructor.templates[key];
        if (!(r instanceof Function)){
            this.constructor.templates[key] = Handlebars.compile(r);
            r = this.constructor.templates[key];
        }

        return $(r(data));
    }else{
        throw "Template not found in constructor";
    }
};
Templatable.Template = function (name) {
    if (!this._compiledTemplates){
        this._compiledTemplates = {};
    }

    if (!this._compiledTemplates[name]){
        this._compiledTemplates[name] = Handlebars.compile($("script#"+name+"-template").html())
    }

    return this._compiledTemplates[name];
};


//// Options
function OptionsReceiver(def){
    this.applyOptions(def);
}
OptionsReceiver.inherit(Emitter);
OptionsReceiver.prototype.applyOptions = function(o, def){
    if (!o) o = {};
    if (!def) def = {};

    for (var i in o){
        if (o.hasOwnProperty(i)){
            def[i] = o[i];
        }
    }
    for (i in def) {
        if (def.hasOwnProperty(i)) {
            this[i] = def[i];
        }
    }
    
    return this;
};

if (isModule()){
    module.exports.HasUniqueId = HasUniqueId;
    module.exports.Emitter = Emitter;
    module.exports.Templatable = Templatable;
    module.exports.OptionsReceiver = OptionsReceiver;

    window.importExport = function (o) {
        Object.keys(o).forEach(function(key) {
            window[key] = o[key];
        });
    }
}