EntityForm.Fields.ReadOnlyField = function (attribute, options, entity){
    this.Parent(null, arguments, EntityForm.Fields.ReadOnlyField);
};
EntityForm.Fields.ReadOnlyField.inherit(EntityForm.Fields.BaseField);

EntityForm.Fields.ReadOnlyField.prototype.Read = function(obj){};


/**
 * Read Only Form field
 * @param {string|Object} attribute
 *
 * @param {Object} options
 * @param {string} options.label
 * @param {string} [options.help]
 *
 * @param {string|Function} [entity]
 *
 * @property formElement
 *
 * @extends EntityForm.Fields.FormField
 * @constructor
 */
EntityForm.Fields.ReadOnlyFormField = function (attribute, options, entity){
    this.Parent(null, arguments, EntityForm.Fields.ReadOnlyFormField);
};
EntityForm.Fields.ReadOnlyFormField.inherit(EntityForm.Fields.ReadOnlyField);
EntityForm.Fields.ReadOnlyFormField.prototype.CreateElement = function(){
    this.element = $("<div />").attr('class', 'form-group');

    if (this.options.label){
        this.labelElement = $('<label />').attr('for', this.attributeName).html(this.options.label);
        this.element.append(this.labelElement);

        if (this.options.help){
            $('<span />')
                .attr('class', 'glyphicon glyphicon-question-sign')
                .popover({
                    content: this.options.help,
                    trigger: 'click',
                    html: true
                })
                .css({
                    display: 'inline-block',
                    marginLeft: '5px',
                    marginTop: '-5px',
                    cursor: 'pointer'
                })
                .appendTo(this.labelElement);
        }
    }

    this.CreateFormElement();

    this.element.append(this.formElement);
};




/**
 * Read Only Form field
 * @param {string|Object} attribute
 *
 * @param {Object} options
 * @param {string} options.label
 * @param {string} [options.help]
 *
 * @param {string|Function} [entity]
 *
 * @extends EntityForm.Fields.ReadOnlyFormField
 * @constructor
 */
EntityForm.Fields.Div = function (attribute, options, entity){
    this.Parent(null, arguments, EntityForm.Fields.Div);
};
EntityForm.Fields.Div.inherit(EntityForm.Fields.ReadOnlyFormField);

EntityForm.Fields.Div.prototype.CreateFormElement = function(){
    this.formElement = $('<div />')
        .attr('class', this.options.class ? this.options.class : 'form-control')
        .attr('id', this.attributeName);

    if (this.options.attributes){
        this.formElement.attr(this.options.attributes);
    }
};
EntityForm.Fields.Div.prototype.Set = function(val){
    this.formElement.html(val);
};




/**
 * Read Only Form field
 * @param {string|Object} attribute
 *
 * @param {Object} options
 * @param {string} options.label
 * @param {string} [options.help]
 * @param {string} [options.panelClass]
 * @param {Object} options.entityViewer
 * @param {Object} options.entityViewer.entity
 * @param {Array} [options.entityViewer.include]
 * @param {Array} [options.entityViewer.exclude]
 *
 * @param {string|Function} [entity]
 *
 * @extends EntityForm.Fields.ReadOnlyFormField
 * @constructor
 */
EntityForm.Fields.EntityViewer = function (attribute, options, entity){
    this.Parent(null, arguments, EntityForm.Fields.EntityViewer);

    this.entityViewer = new EntityViewer(options.entityViewer)
};
EntityForm.Fields.EntityViewer.inherit(EntityForm.Fields.ReadOnlyFormField);

EntityForm.Fields.EntityViewer.prototype.CreateElement = function(){
    this.element = $("<div />")
        .attr('class', 'panel')
        .addClass(this.options.panelClass ? this.options.panelClass : 'panel-primary');

    this.heading = $('<div class="panel-heading"></div>').html(this.options.label).appendTo(this.element);
    this.body = $('<div class="panel-body"></div>').appendTo(this.element);

    if (this.options.help){
        $('<span />')
            .attr('class', 'glyphicon glyphicon-question-sign')
            .popover({
                content: this.options.help,
                trigger: 'click',
                html: true
            })
            .css({
                display: 'inline-block',
                marginLeft: '5px',
                marginTop: '-5px',
                cursor: 'pointer'
            })
            .appendTo(this.heading);
    }

    this.formElement = this.element;
};
EntityForm.Fields.EntityViewer.prototype.Set = function(val){
    if (Entity.IsInstance(val)){
        this.body.html(this.entityViewer.GetTable(val));
    }
};