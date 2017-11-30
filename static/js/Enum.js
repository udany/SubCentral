/*
 * Description: Enum Class for js
 * Version: 0.1
 * Author: Daniel Andrade
 * Date: 24/11/2015
 * This code may not be reused without proper permission from its creator.
 */
function Enum(values, data){
    for(var i in values){
        if (values.hasOwnProperty(i)){
            this[i] = values[i];
        }
    }

    this._values = values;
    this._data = data ? data : [];
}
Enum.prototype.forEach = function(fn){
    for(var i in this._values){
        if (this._values.hasOwnProperty(i)){
            var val = this._values[i];
            var data = this._data[val];
            fn(i, val, data);
        }
    }
};
Enum.prototype.getKey = function(value){
    for(var i in this._values){
        if (this._values.hasOwnProperty(i)){
            if (this._values[i] == value){
                return i;
            }
        }
    }

    return false;
};
Enum.prototype.Data = function(val){
    if (this._data && typeof this._data[val] !== 'undefined'){
        return this._data[val];
    }

    return null;
};

if (isModule()){
    module.exports = Enum;
}