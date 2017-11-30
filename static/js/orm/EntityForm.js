/*
 * Description: Entity form and suporting classes
 * Version: 0.1
 * Author: Daniel Andrade
 * Date: 19/11/2015
 * This code may not be reused without proper permission from its creator.
 */
/**
 *
 * @param {Function | Object} entity
 * @param {EntityForm.Fields.BaseField[]} entity.Fields
 * @param {EntityForm.Fields.BaseField[]} [fields]
 * 
 * @attribute {EntityForm.Fields.BaseField[]} fields 
 * 
 * @extends Emitter
 * @constructor
 */
function EntityForm(entity, fields){
    this.entity = entity;
    this.fields = fields ? fields : this.entity.Fields.concat([]);
    this.hiddenFields = [];

    this.CreateElement();

    this.on('beforeShow', function(){
        for(var i = 0; i < this.fields.length; i++) {
            var field = this.fields[i];

            if (this.hiddenFields[i]){
                field.element.css('display', 'none');
            }else{
                field.element.css('display', '');
            }

            this.element.append(field.element);
        }
    });

    this.on('show', function(){
        for(var i = 0; i < this.fields.length; i++) {
            var field = this.fields[i];
            field.emit('show');
        }
    });
    
    var uid = this.GetUId();
}
EntityForm.inherit(Emitter);
EntityForm.prototype.CreateElement = function(){
    this.element = $('<div />');

    for(var i = 0; i < this.fields.length; i++){
        var field = this.fields[i];
        this.element.append(field.element);
    }

    return this;
};
/**
 * Updates the forms to match the object's state
 * @param obj
 */
EntityForm.prototype.Update = function(obj){
    for(var i = 0; i < this.fields.length; i++){
        if (!this.hiddenFields[i]){
            var field = this.fields[i];
            field.Update(obj);
        }
    }

    return this;
};
/**
 * Updates the object to match the forms's state
 * @param obj
 */
EntityForm.prototype.Read = function(obj){
    for(var i = 0; i < this.fields.length; i++){
        if (!this.hiddenFields[i]){
            var field = this.fields[i];
            field.Read(obj);
        }
    }

    return this;
};
EntityForm.prototype.Validate = function(){
    return this.element.validate();
};
EntityForm.prototype.ValidationClear = function(){
    this.element.validationClear();

    return this;
};
EntityForm.prototype.Focus = function(){
    if (this.fields[0])
        this.fields[0].Focus();

    return this;
};
/**
 *
 * @param field
 * @return {EntityForm.Fields.FormField}
 * @constructor
 */
EntityForm.prototype.GetField = function (field) {
    if (typeof field === 'string'){
        var result = this.fields.filter(function (e) {
            return e.attributeName == field;
        });

        if (result.length){
            return result[0];
        }
    }else if (typeof field === 'number'){
        return this.fields[field];
    }

    return null;
};
EntityForm.prototype.GetFieldIndex = function (field) {
    if (typeof field !== 'object'){
        field = this.GetField(field);
    }

    if (field){
        return this.fields.indexOf(field);
    }else{
        return -1;
    }
};
EntityForm.prototype.AddField = function (field, after) {
    if (!field) return;

    if (typeof after !== 'undefined'){
        var idx;

        if (typeof after !== 'number'){
            idx = this.GetFieldIndex(after);
        }else{
            idx = after;
        }

        if (idx >= -1){
            var start = this.fields.splice(0, idx+1);
            start.push(field);
            this.fields = start.concat(this.fields);
        }
    }else{
        this.fields.push(field);
    }

    return this;
};
EntityForm.prototype.HideField = function (field) {
    if (typeof field !== 'number') {
        field = this.GetFieldIndex(field);
    }

    if (field >= 0 && field < this.fields.length){
        this.hiddenFields[field] = true;
    }

    return this;
};
EntityForm.prototype.UnhideField = function (field) {
    if (typeof field !== 'number') {
        field = this.GetFieldIndex(field);
    }

    if (field >= 0 && field < this.fields.length){
        this.hiddenFields[field] = false;
    }

    return this;
};

EntityForm.Fields = {};