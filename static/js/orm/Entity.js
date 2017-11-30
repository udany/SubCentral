/*
 * Description: A script
 * Version: 0.1
 * Author: Daniel Andrade
 * Date: 19/11/2015
 * This code may not be reused without proper permission from its creator.
 */
var ORM = {};

if (isModule()){
    var Emitter = require('./../General').Emitter;
}

/// ENTITY
/**
 * @extends Emitter
 * @constructor
 */
function Entity(a){
    this.FillFromArray(a);
}
Entity.inherit(Emitter);
Entity.prototype.FillFromArray = function(a){
    if (!a) a = [];
    var attributes = this.constructor.Attributes;

    for(var i = 0; i < attributes.length; i++){
        var attribute = attributes[i];
        var name = attribute.name;
        if (a.hasOwnProperty(name) || !this.hasOwnProperty(name)){
            var val = a[name];
            if (typeof val === 'undefined') val = null;

            attribute.Set(this, val);
        }
    }

    if (a['__class']){
        this.__class = a['__class'];
    }
    this.emit('fill');
};
Entity.prototype.GetAttribute = function(attr){
    var r = this.constructor.Attributes.filter(function(e){return e.name == attr});
    if (r.length){
        return r[0];
    }else{
        return null;
    }
};
Entity.prototype.Get = function(attr){
    if (attr.Get){
        return attr.Get(this);
    }else if (attr instanceof Function){
        return attr(this);
    }else{
        var thisAttr = this.GetAttribute(attr);
        if (thisAttr){
            return thisAttr.Get(this);
        }else{
            return this[attr];
        }
    }
};
Entity.prototype.Set = function(attr, val){
    if (attr.Set){
        return attr.Set(this);
    }else if (attr instanceof Function){
        return attr(this, val);
    }else{
        var thisAttr = this.GetAttribute(attr);
        if (thisAttr){
            return thisAttr.Set(this, val);
        }else{
            return this[attr] = val;
        }
    }
};
Entity.prototype.Serialize = function(){
    var attributes = this.constructor.Attributes;

    var result = {};

    for(var i = 0; i < attributes.length; i++){
        var attribute = attributes[i];
        result[attribute.name] = attribute.Get(this);
    }
    if (this.__class){
        result['__class'] = this.__class;
    }else{
        result['__class'] = Entity.ClassMap.JS2PHP(this);
    }

    return result;
};
/**
 * Use's Serialize/FillFromArray to clone this object
 * @constructor
 */
Entity.prototype.Clone = function () {
    var data = JSON.parse(JSON.stringify(this.Serialize()));
    return Entity.FromArray(data);
};
Entity.prototype.GetKey = function () {
    if (this.KeyName){
        return this.Get(this.KeyName);
    }else{
        return this.Get(this.constructor.Attributes[0]);
    }
};
/**
 * @param obj Entity
 * @return boolean
 */
Entity.prototype.Equals = function (obj) {
    return obj.GetKey() == this.GetKey();
};
Entity.prototype.Save = function(o){
    var options = new OptionsReceiver(this.constructor.Options.Save);

    options.applyOptions(o);

    var that = this;
    var t = options.preMessage ? toast('info', options.preMessage, null, 0) : null;

    options.data[options.dataObjectKey] = JSON.stringify(this.Serialize());

    Session.Do(options.controller, options.method, options.data, function(data){
        that.FillFromArray(data);
        that.emit('save');

        if (t)
            t.fadeOut(300, function(){t.remove()});

        if (options.successMessage)
            toast('success', options.successMessage);

        if (options.callback) options.callback(data);
    });
};
Entity.prototype.Delete = function(o){
    var options = new OptionsReceiver(this.constructor.Options.Delete);
    options.applyOptions(o);
    
    var that = this;
    var t = options.preMessage ? toast('info', options.preMessage, null, 0) : null;

    options.data[options.dataObjectKey] = JSON.stringify(this.Serialize());

    Session.Do(options.controller, options.method, options.data, function(data){
        if (data && data.status === false){
            if (options.errorMessage)
                toast('error', options.errorMessage);
        }else{
            that.emit('delete');

            if (t)
                t.fadeOut(300, function(){t.remove()});

            if (options.successMessage)
                toast('success', options.successMessage);

            if (options.callback) options.callback(data);
        }
    });
};
Function.prototype.AttributesInherit = function(){
    if (this.prototype.__parent){
        var parentArr = this.prototype.__parent;
        var parent = parentArr[parentArr.length-1];

        var attr = parent.Attributes;
        var thisAttr = this.Attributes;
        if (attr && thisAttr && attr.concat) thisAttr = attr.concat(thisAttr);

        this.Attributes = thisAttr;
    }
};

Entity.Options = {
    Save: {
        controller: 'entity',
        method: 'save',
        preMessage: 'Saving...',
        successMessage: 'Saved',
        data: {},
        dataObjectKey: 'object'
    },
    Delete: {
        controller: 'entity',
        method: 'delete',
        preMessage: 'Deleting...',
        successMessage: 'Deleted',
        errorMessage: 'Failed to delete',
        data: {},
        dataObjectKey: 'object'
    },
    Select: {
        controller: 'entity',
        method: 'select',
        data: {},
        dataObjectKey: 'object',
        filter: []
    }
};

Entity.Select = function(entity, o, callback){
    var options = new OptionsReceiver(this.Options.Select);
    options.applyOptions(o);

    if (!entity || !Entity.IsParent(entity)){
        if (entity instanceof Function && !callback){
            callback = entity;
        }

        entity = this;
    }

    if (!(entity instanceof Function) && window[entity]) entity = window[entity];

    var cl = entity.__class ? entity.__class : entity.name;

    options.data.class = cl;
    options.data.filter = JSON.stringify(options.filter);
    options.data.order = options.order ? options.order : '';

    Session.Do(options.controller, options.method, options.data, function(data){
        var r = [];
        for (var i = 0; i < data.length; i++){
            r.push(Entity.FromArray(data[i]));
        }

        if (callback) callback(r);
    });
};
Entity.Inherit = function(cl, from){
    cl.inherit(from);
    cl.AttributesInherit();
    Entity.ClassMap.Register(cl);
};
ORM.Entity = Entity;

function SurrogateEntity(a){
    this.Parent(null, arguments, SurrogateEntity);
}
SurrogateEntity.inherit(Entity);
SurrogateEntity.prototype.Save = function(){
    var t = toast('info', 'Updated', null, 1000);
    this.emit('save');
};

Entity.ClassMap = {
    map: {},
    JS2PHP: function(o){
        if (!(o instanceof Function)) o = o.constructor;
        return o.__class;
    },
    PHP2JS: function(name){
        return this.map[name];
    },
    Register: function(fn, name){
        if (!name) name = fn.name;
        fn.__class = name;
        this.map[name] = fn;
    }
};
Entity.FromArray = function(a){
    if (!a){
        return null;
    }
    
    if (!a.__class){
        throw "Object doesn't describe it's class, make sure it contains a __class property";
    }
    
    var c = this.ClassMap.PHP2JS(a.__class);

    if (!(c instanceof Function)){
        throw "Couldn't locate class within current scope, make sure the script defining the class: "+a.__class;
    }

    return new c(a);
};


Array.prototype.ToEntityArray = function(entity){
    var r = [];
    for (var i in this){
        if (this.hasOwnProperty(i)){
            if (entity){
                r[i] = new entity(this[i]);
            }else{
                r[i] = Entity.FromArray(this[i]);
            }
        }
    }

    return r;
};
Array.prototype.SortByField = function(fields){
    if (!(fields instanceof Array)){
        fields = [fields];
    }

    return this.sort(function (a, b) {
        var i = 0;
        while (i < fields.length){
            var field = fields[i];
            if (a.Get(field) < b.Get(field)) {
                return -1;
            }
            if (a.Get(field) > b.Get(field)) {
                return 1;
            }
            i++;
        }
        return 0;
    });
};


/// Attributes
Entity.Attributes = {};

// Object
Entity.Attributes.Object = function(name){
    this.name = name;
};
Entity.Attributes.Object.prototype.Get = function(obj){
    return obj[this.name];
};
Entity.Attributes.Object.prototype.Set = function(obj, val){
    obj[this.name] = val;
};

// String
Entity.Attributes.String = function(name){
    this.name = name;
};
Entity.Attributes.String.prototype.Get = function(obj){
    return obj[this.name] === null ? null : obj[this.name].toString();
};
Entity.Attributes.String.prototype.Set = function(obj, val){
    obj[this.name] = val === null ? null : val.toString();
};

// Integer
Entity.Attributes.Integer = function(name, nullable){
    this.name = name;
    this.nullable = nullable ? true : false;
};
Entity.Attributes.Integer.prototype.Get = function(obj){
    var val = parseInt(obj[this.name], 10);
    return isNaN(val) ? (this.nullable ? null : 0) : val;
};
Entity.Attributes.Integer.prototype.Set = function(obj, val){
    val = parseInt(val, 10);
    obj[this.name] = isNaN(val) ? (this.nullable ? null : 0) : val;
};

// Float
Entity.Attributes.Float = function(name){
    this.name = name;
};
Entity.Attributes.Float.prototype.Get = function(obj){
    var val = parseFloat(obj[this.name]);
    return isNaN(val) ? 0 : val;
};
Entity.Attributes.Float.prototype.Set = function(obj, val){
    val = parseFloat(val);
    obj[this.name] = isNaN(val) ? 0 : val;
};

// Boolean
Entity.Attributes.Boolean = function(name, nullable){
    this.name = name;
    this.nullable = nullable === true;
};
Entity.Attributes.Boolean.prototype.Get = function(obj){
    var val = obj[this.name];
    
    if (this.nullable){
        if (val === null) return null;
    }

    return val ? true : false;
};
Entity.Attributes.Boolean.prototype.Set = function(obj, val){
    if (this.nullable && val === null){
        obj[this.name] = null;
        return;
    }
    
    obj[this.name] = val ? true : false;
};

// Json
Entity.Attributes.Json = function(name){
    this.name = name;
};
Entity.Attributes.Json.prototype.Get = function(obj){
    return JSON.stringify(obj[this.name]);
};
Entity.Attributes.Json.prototype.Set = function(obj, val){
    obj[this.name] = JSON.parse(val);
};

// EntityList
Entity.Attributes.Entity = function(name, encodeAsJson, defaultEntity){
    this.name = name;
    this.encodeAsJson = encodeAsJson !== false;
    this.defaultEntity = defaultEntity;
};
Entity.Attributes.Entity.prototype.Get = function(obj){
    var a = obj[this.name] ? obj[this.name].Serialize() : null;
    return this.encodeAsJson ? JSON.stringify(a) : a;
};
Entity.Attributes.Entity.prototype.Set = function(obj, val){
    var dataArray = this.encodeAsJson ? JSON.parse(val) : val;

    if (this.defaultEntity){
        obj[this.name] = new this.defaultEntity(dataArray);
    }else{
        obj[this.name] = Entity.FromArray(dataArray);
    }
};

// EntityList
Entity.Attributes.EntityList = function(name, encodeAsJson, defaultEntity){
    this.name = name;
    this.encodeAsJson = encodeAsJson !== false;
    this.defaultEntity = defaultEntity;
};
Entity.Attributes.EntityList.prototype.Get = function(obj){
    var a = obj[this.name];
    var r = [];
    for (var i = 0; i < a.length; i++){
        var e = a[i];
        r.push(e.Serialize())
    }
    return this.encodeAsJson ? JSON.stringify(r) : r;
};
Entity.Attributes.EntityList.prototype.Set = function(obj, val){
    var a = this.encodeAsJson ? JSON.parse(val) : val;
    if (!a) a = [];
    var r = [];
    for (var i = 0; i < a.length; i++){
        var e = a[i];
        if (this.defaultEntity){
            r.push(new this.defaultEntity(e));
        }else{
            r.push(Entity.FromArray(e));
        }
    }
    obj[this.name] = r;
};

// EntityMap
Entity.Attributes.EntityMap = function(name, encodeAsJson){
    this.name = name;
    this.encodeAsJson = encodeAsJson === false ? false : true;
};
Entity.Attributes.EntityMap.prototype.Get = function(obj){
    var a = obj[this.name];
    var r = [];
    for (var i in a){
        if (a.hasOwnProperty(i)){
            var e = a[i];
            r[i] = e.Serialize();
        }
    }
    return this.encodeAsJson ? JSON.stringify(r) : r;
};
Entity.Attributes.EntityMap.prototype.Set = function(obj, val){
    var a = this.encodeAsJson ? JSON.parse(val) : val;
    if (!a) a = [];
    var r = [];
    for (var i in a){
        if (a.hasOwnProperty(i)) {
            var e = a[i];
            r[i] = Entity.FromArray(e);
        }
    }
    obj[this.name] = r;
};

// UnixTimestamp
Entity.Attributes.UnixTimestamp = function(name, absolute){
    this.name = name;
    this.absolute = absolute ? 1 : 0;
};
Entity.Attributes.UnixTimestamp.prototype.Get = function(obj){
    if (obj[this.name] instanceof Date){
        if (this.absolute){
            var dt = new Date(obj[this.name].getTime());
            dt.setMinutes(dt.getMinutes() - dt.getTimezoneOffset());

            return Math.floor(dt.getTime()/1000);
        }else{
            return Math.floor(obj[this.name].getTime()/1000);
        }
    }else{
        return obj[this.name] === null ? null : 0;
    }
};
Entity.Attributes.UnixTimestamp.prototype.Set = function(obj, val){
    var timestamp = parseInt(val, 10);
    if (val !== null && !isNaN(timestamp)){
        var dt = new Date(timestamp * 1000);
        
        if (this.absolute){
            dt.setMinutes(dt.getMinutes() + dt.getTimezoneOffset());
        }

        obj[this.name] = dt;
    }else{
        obj[this.name] = null;
    }
};

if (isModule()) {
    module.exports = Entity;
}