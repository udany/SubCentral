//////// Basic fields
/**
 * Base Field
 * @param {string|Function} attribute
 * @param {Object} options
 * @param {string|Function} [entity]
 *
 * @extends Emitter
 * @constructor
 */
EntityForm.Fields.BaseField = function (attribute, options, entity){
    if (typeof attribute === 'string'){
        if (!entity){
            throw "Can't create field with a string attribute without a reference entity";
        }

        var attr = entity.Attributes.filter(function(e){return e.name === attribute})[0];
        if (!attr) console.log("Error: Couldn't find attribute "+attribute+" on entity "+entity.constructor.name);

        attribute = attr;
    }
    this.attribute = attribute;
    if (this.attribute && this.attribute.name){
        this.attributeName = this.attribute.name;
    }else{
        this.attributeName = '';
    }
    this.options = options;

    this.CreateElement();
};
EntityForm.Fields.BaseField.inherit(Emitter);
/**
 * Updates the field to match the object's state
 * @param obj
 */
EntityForm.Fields.BaseField.prototype.Update = function(obj){
    this.Set(this.attribute.Get(obj));
    this.emit('update');
};
/**
 * Updates the object to match the field's state
 * @param obj
 */
EntityForm.Fields.BaseField.prototype.Read = function(obj){
    this.attribute.Set(obj, this.Get());
    this.emit('read');
    if (obj.emit){
        obj.emit('change');
    }
};
EntityForm.Fields.BaseField.prototype.Focus = function(){
    if (this.formElement){
        var that = this;
        setTimeout(function(){
            that.formElement.focus();
        }, 1);
    }
};
EntityForm.Fields.BaseField.prototype.CreateElement = function(){};


/**
 * Base Form field
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
 * @extends EntityForm.Fields.BaseField
 * @constructor
 */
EntityForm.Fields.FormField = function (attribute, options, entity){
    this.Parent(null, arguments, EntityForm.Fields.FormField);
};
EntityForm.Fields.FormField.inherit(EntityForm.Fields.BaseField);
EntityForm.Fields.FormField.prototype.CreateElement = function(){
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
EntityForm.Fields.FormField.prototype.Required = function(val){
    this.Validator({required: val});
    return this;
};
EntityForm.Fields.FormField.prototype.Min = function(val){
    this.Validator({min: val});
    return this;
};
EntityForm.Fields.FormField.prototype.Max = function(val){
    this.Validator({max: val});
    return this;
};
EntityForm.Fields.FormField.prototype.MinLength = function(val){
    this.Validator({minlength: val});
    return this;
};
EntityForm.Fields.FormField.prototype.MaxLength = function(val){
    this.Validator({maxlength: val});
    return this;
};
EntityForm.Fields.FormField.prototype.Validator = function(props){
    this.formElement.prop(props);
    return this;
};


/**
 * Input Form field
 * @param {string|Object} attribute
 *
 * @param {Object} options
 * @param {string} options.label
 * @param {string} [options.help]
 * @param {string} [options.placeholder]
 * @param {string} [options.type] Input type parameter
 * @param {Object} [options.attributes]
 *
 * @param {string|Function} [entity]
 *
 * @property formElement
 *
 * @extends EntityForm.Fields.FormField
 * @constructor
 */
EntityForm.Fields.Input = function (attribute, options, entity){
    this.Parent(null, arguments, EntityForm.Fields.Input);
};
EntityForm.Fields.Input.inherit(EntityForm.Fields.FormField);
EntityForm.Fields.Input.prototype.CreateFormElement = function(){
    var that = this;

    this.formElement = $('<input />')
        .attr('class', 'form-control')
        .attr('id', this.attributeName)
        .attr('type', this.options.type)
        .attr('placeholder', this.options.placeholder)
        .addClass(this.options.class)
        .on('change keyup keydown', function () {
            that.emit('change');
        });

    if (this.options.attributes){
        this.formElement.attr(this.options.attributes);
    }
};
EntityForm.Fields.Input.prototype.Get = function(){
    return this.formElement.val();
};
EntityForm.Fields.Input.prototype.Set = function(val){
    this.formElement.val(val);
};


// UnixDateTime
EntityForm.Fields.UnixDateTime = function (attribute, options, entity){
    if (!options.type) options.type = 'date';

    this.date = null;

    this.Parent(null, arguments, EntityForm.Fields.UnixDateTime);
};
EntityForm.Fields.UnixDateTime.inherit(EntityForm.Fields.Input);
EntityForm.Fields.UnixDateTime.prototype.CreateFormElement = function(){
    var that = this;

    this.formElement = $('<div></div>')
        .addClass('input-group')
        .addClass(this.options.class);

    if (this.options.attributes){
        this.formElement.attr(this.options.attributes);
    }

    this.dateInput = $('<input />')
        .attr('class', 'form-control')
        .attr('type', 'date')
        .on('change keyup keydown', function () {
            that.emit('change');
        })
        .appendTo(this.formElement);

    $('<span></span>')
        .attr('class', 'input-group-addon')
        .html(' - ')
        .appendTo(this.formElement);

    this.timeInput = $('<input />')
        .attr('class', 'form-control')
        .attr('type', 'time')
        .on('change keyup keydown', function () {
            that.emit('change');
        })
        .appendTo(this.formElement);
};
EntityForm.Fields.UnixDateTime.prototype.Get = function(){
    this.date = Date.fromInputDate(this.dateInput, this.timeInput);

    return Math.round(this.date.getTime()/1000);
};
EntityForm.Fields.UnixDateTime.prototype.Set = function(val){
    this.date = val instanceof Date ? val : new Date(val * 1000);

    this.dateInput.val(this.date.format('Y-m-d'));
    this.timeInput.val(this.date.format('H:i'));
};


/**
 * Nullable Input field
 * @param {string|Object} attribute
 *
 * @param {Object} options
 * @param {string} options.label
 * @param {string} [options.help]
 * @param {string} [options.placeholder]
 * @param {string} [options.type] Input type parameter
 * @param {Object} [options.class]
 *
 * @param {string|Function} entity
 *
 * @property formElement
 *
 * @extends EntityForm.Fields.FormField
 * @constructor
 */
EntityForm.Fields.NullableInput = function (attribute, options, entity){
    this.Parent(null, arguments, EntityForm.Fields.NullableInput);
};
EntityForm.Fields.NullableInput.inherit(EntityForm.Fields.FormField);
EntityForm.Fields.NullableInput.prototype.CreateFormElement = function(){
    var that = this;

    this.formElement = $('<div></div>')
        .addClass('input-group');

    this.checkboxElement = $('<input />')
        .attr('type', 'checkbox')
        .change(function(){
            if (that.checkboxElement.prop('checked')){
                that.inputElement.val('');
            }
            that.emit('change');
        });

    this.checkboxLabelElement = $('<label></label>')
        .append(this.checkboxElement)
        .append('Null');

    this.addOnElement = $('<span></span>')
        .attr('class', 'input-group-addon checkbox')
        .append(this.checkboxLabelElement)
        .appendTo(this.formElement);

    this.inputElement = $('<input />')
        .attr('class', 'form-control')
        .attr('id', this.attributeName)
        .attr('type', this.options.type)
        .attr('placeholder', this.options.placeholder)
        .addClass(this.options.class)
        .on('change keyup keydown', function () {
            that.emit('change');
        })
        .change(function(){
            if (that.inputElement.val()){
                that.checkboxElement.prop('checked', false);
            }
        })
        .appendTo(this.formElement);
};
EntityForm.Fields.NullableInput.prototype.Get = function(){
    return this.checkboxElement.prop('checked') ? null : this.inputElement.val();
};
EntityForm.Fields.NullableInput.prototype.Set = function(val){
    if (val === null){
        this.checkboxElement.prop('checked', true);
        this.inputElement.val('');
    }else{
        this.checkboxElement.prop('checked', false);
        this.inputElement.val(val);
    }
};

// Text
EntityForm.Fields.Text = function (attribute, options, entity){
    this.Parent(null, arguments, EntityForm.Fields.Text);
    this.on('show', function(){
        this.Expand();
    })
};
EntityForm.Fields.Text.inherit(EntityForm.Fields.Input);

EntityForm.Fields.Text.prototype.CreateFormElement = function(){
    var that = this;

    this.formElement = $('<textarea />')
        .attr('class', 'form-control')
        .attr('id', this.attributeName)
        .attr('placeholder', this.options.placeholder)
        .addClass(this.options.class)
        .on('change keyup keydown', function () {
            that.emit('change');
        });

    if (this.options.style)
        this.formElement.css(this.options.style);

    if (this.options.expand !== false){
        var $element = this.formElement.get(0);
        $element.addEventListener('keyup', function() {
            that.Expand();
        }, false);
    }
};
EntityForm.Fields.Text.prototype.Set = function(val){
    this.Parent('Set', arguments, EntityForm.Fields.Text);
    this.Expand();
};
EntityForm.Fields.Text.prototype.Expand = function(){
    var $element = this.formElement.get(0);

    $element.style.height = 0;
    $element.style.height = $element.scrollHeight + 'px';

    if ($element.style.maxHeight && parseInt($element.style.height) > parseInt($element.style.maxHeight)){
        $element.style.overflow = 'auto';
    }else{
        $element.style.overflow = 'hidden';
    }
};

/**
 * Select Field
 * @param {string|Object} attribute
 *
 * @param {Object}  options
 * @param {string}  [options.label]
 * @param {string}  [options.help]
 *
 * @param {Enum|Object}  [options.options]
 *
 * @param {Object}  [options.noneOption]
 * @param {int}     [options.noneOption.value]
 * @param {string}  [options.noneOption.label]
 *
 * @param {Object}  [options.anyOption]
 * @param {int}     [options.anyOption.value]
 * @param {string}  [options.anyOption.label]
 *
 * @param {string|Function} [options.entity]
 * @param {string|Function} [options.entityValue]
 * @param {string|Function} [options.entityLabel]
 * @param {string|Function} [options.autoload]
 *
 * @param {string|Function} [entity]
 *
 * @property formElement
 *
 * @extends EntityForm.Fields.BaseField
 * @constructor
 */
EntityForm.Fields.Select = function (attribute, options, entity){
    this.Parent(null, arguments, EntityForm.Fields.Select);

    this.loaded = this.options.entity ? false : true;
    this.loading = false;
};
EntityForm.Fields.Select.inherit(EntityForm.Fields.Input);

EntityForm.Fields.Select.prototype.CreateFormElement = function(){
    var that = this;

    this.formElement = $('<select />')
        .attr('class', 'form-control')
        .attr('id', this.attributeName)
        .addClass(this.options.class)
        .on('change', function () {
            that.emit('change');
        });

    if (this.options.noneOption){
        $("<option />")
            .attr('value', typeof this.options.noneOption.value === 'undefined' ? '' : this.options.noneOption.value)
            .html(this.options.noneOption.label ? this.options.noneOption.label : 'None')
            .appendTo(that.formElement)
    }
    if (this.options.anyOption){
        $("<option />")
            .attr('value', typeof this.options.anyOption.value === 'undefined' ? -1 : this.options.anyOption.value)
            .html(this.options.anyOption.label ? this.options.anyOption.label : 'Any')
            .appendTo(that.formElement)
    }

    if (this.options.entity){
        if (this.options.autoload)
            this.Load();
    }else{
        if (!(this.options.options instanceof Enum)){
            this.options.options = new Enum(this.options.options);
        }

        var selectOpts = this.options.options;
        selectOpts.forEach(function(key, value, data){
            var opt = $("<option />")
                .attr('value', value)
                .html(data ? data : key)
                .appendTo(that.formElement);
        });

        this.loaded = true;
    }
};
EntityForm.Fields.Select.prototype.Load = function(){
    var that = this;

    this.loaded = false;
    this.formElement.html('');
    $("<option />")
        .attr('value', '')
        .html('Loading')
        .prop('selected', true)
        .appendTo(this.formElement);


    if (this.options.options){
        this.FillOptions(this.options.options)
    }else{
        this.loading = true;
        Entity.Select(this.options.entity, [], function(r){
            that.loading = false;
            that.FillOptions(r);
        });
    }
};
EntityForm.Fields.Select.prototype.FillOptions = function(options){
    this.formElement.html('');


    if (this.options.noneOption){
        if (typeof this.options.noneOption.value === 'undefined'){
            this.options.noneOption.value = '';
        }

        $("<option />")
            .attr('value', this.options.noneOption.value)
            .html(this.options.noneOption.label ? this.options.noneOption.label : 'None')
            .appendTo(this.formElement)
    }
    if (this.options.anyOption){
        $("<option />")
            .attr('value', typeof this.options.anyOption.value === 'undefined' ? -1 : this.options.anyOption.value)
            .html(this.options.anyOption.label ? this.options.anyOption.label : 'Any')
            .appendTo(this.formElement)
    }

    for(var i = 0; i < options.length; i++){
        var option = options[i];
        var opt = $("<option />")
            .attr('value', this.options.entityValue instanceof Function ? this.options.entityValue(option) : option[this.options.entityValue])
            .html(this.options.entityLabel instanceof Function ? this.options.entityLabel(option) : option[this.options.entityLabel])
            .appendTo(this.formElement);
    }

    this.loaded = true;
    this.emit('load');
};
EntityForm.Fields.Select.prototype.Set = function(val){
    if (this.loaded){
        if (val === null){
            if (this.options.noneOption){
                val = this.options.noneOption.value;
            }
        }
        this.Parent('Set', arguments, EntityForm.Fields.Select);
    }else{
        var that = this;

        this.once('load', function(){
            that.Set(val);
        }, null, true);

        if (!this.loading) this.Load();
    }
};


// ButtonGroup
/**
 *
 * @param attribute
 * @param options Object
 * @param options.type string Either "radio" or "checkbox" 
 * @param options.label string
 * @param options.vertical boolean
 * @param options.class string
 * @param options.buttonClass string
 * @param options.options Enum | Object
 * @param entity Entity
 *
 * @extends EntityForm.Fields.FormField
 * @constructor
 */
EntityForm.Fields.ButtonGroup = function (attribute, options, entity){
    if (!options.buttonClass) options.buttonClass = 'btn-default';
    this.Parent(null, arguments, EntityForm.Fields.ButtonGroup);
};
EntityForm.Fields.ButtonGroup.inherit(EntityForm.Fields.FormField);

EntityForm.Fields.ButtonGroup.prototype.CreateFormElement = function(){
    var that = this;

    this.formElement = $('<div />')
        .attr('class', this.options.vertical ? 'btn-group-vertical' : 'btn-group')
        .attr('data-toggle', 'buttons')
        .attr('id', this.attributeName)
        .addClass('block')
        .addClass(this.options.class);

    this.optionElements = [];

    if (!(this.options.options instanceof Enum)){
        this.options.options = new Enum(this.options.options);
    }

    this.options.options.forEach(function(key, value, data){
        var radio = $('<input />')
            .attr('type', that.options.type)
            .attr('value', value)
            .attr('name', that.attribute.name)
            .attr('autocomplete', 'off')
            .on('change keyup keydown', function () {
                that.emit('change');
            });

        var lbl = $('<label />')
            .addClass('btn')
            .addClass(that.options.buttonClass)
            .append(radio)
            .append(data ? data : key)
            .appendTo(that.formElement);

        that.optionElements[value] = radio;
    });
};
EntityForm.Fields.ButtonGroup.prototype.SelectInput = function(input){
    input.prop('checked', true).parent().addClass('active');
};


/**
 * Radio Button Group
 * @param attribute
 * @param options Object
 * @param options.label string
 * @param options.vertical boolean
 * @param options.class string
 * @param options.buttonClass string
 * @param options.options Enum | Object
 * @param options.sumValues boolean
 * @param entity Entity
 *
 * @extends EntityForm.Fields.ButtonGroup
 * @constructor
 */
EntityForm.Fields.RadioButtonGroup = function (attribute, options, entity){
    options.type = 'radio';
    this.Parent(null, arguments, EntityForm.Fields.RadioButtonGroup);
};
EntityForm.Fields.RadioButtonGroup.inherit(EntityForm.Fields.ButtonGroup);

EntityForm.Fields.RadioButtonGroup.prototype.Get = function(){
    return $(':checked', this.formElement).val();
};
EntityForm.Fields.RadioButtonGroup.prototype.Set = function(val){
    var inputs = $('input', this.formElement);
    var labels = $('label', this.formElement);
    inputs.prop('checked', false);
    labels.removeClass('active');

    this.SelectInput(this.optionElements[val]);
};

/**
 * Check Button Group
 * @param attribute
 * @param options Object
 * @param options.label string
 * @param options.vertical boolean
 * @param options.class string
 * @param options.buttonClass string
 * @param options.options Enum | Object
 * @param options.sumValues boolean
 * @param entity Entity
 *
 * @extends EntityForm.Fields.ButtonGroup
 * @constructor
 */
EntityForm.Fields.CheckButtonGroup = function (attribute, options, entity){
    options.type = 'checkbox';
    this.Parent(null, arguments, EntityForm.Fields.CheckButtonGroup);
};
EntityForm.Fields.CheckButtonGroup.inherit(EntityForm.Fields.ButtonGroup);
EntityForm.Fields.CheckButtonGroup.prototype.Get = function(){
    var r;

    if (this.options.sumValues){
        r = 0;
        $(':checked', this.formElement).each(function(){
            var el = $(this);
            r += parseInt(el.val(), 10);
        });
    }else {
        r = [];
        $(':checked', this.formElement).each(function(){
            var el = $(this);
            r.push(el.val())
        });
        return r;
    }

    return r;
};
EntityForm.Fields.CheckButtonGroup.prototype.Set = function(val){
    var that = this;
    var inputs = $('input', this.formElement);
    var labels = $('label', this.formElement);
    inputs.prop('checked', false);
    labels.removeClass('active');

    if (this.options.sumValues){
        inputs.each(function(){
            var el = $(this);
            var v = parseInt(el.val(), 10);
            if (val & v){
                that.SelectInput(el);
            }else{
                el.prop('checked', false).parent().removeClass('active');
            }
        });
    }else{
        for (var i = 0; i < val.length; i++){
            this.SelectInput(this.optionElements[val[i]]);
        }
    }
};

/**
 * Toggle Field
 * @param {string|Object} attribute
 * @param {Object}      options
 * @param {string}      [options.trueValue]
 * @param {string}      [options.falseValue]
 * @param {Enum}        [options.options]
 *
 * @extends EntityForm.Fields.RadioButtonGroup
 * @constructor
 */
EntityForm.Fields.Toggle = function (attribute, options){
    if (!options.trueValue) options.trueValue = 'Enabled';
    if (!options.falseValue) options.falseValue = 'Disabled';

    options.type = 'radio';
    options.options = new Enum([1, 0], [options.falseValue, options.trueValue]);

    this.Parent(null, arguments, EntityForm.Fields.Toggle);

    var that = this;
    this.on('change', function () {
        $('.btn', that.element).removeClass('btn-success btn-danger');

        var el = $('.btn input:checked', that.element);

        el.parent().addClass(el.val() == 1 ? 'btn-success' : 'btn-danger');
    });
};
EntityForm.Fields.Toggle.inherit(EntityForm.Fields.RadioButtonGroup);
EntityForm.Fields.Toggle.prototype.Get = function(){
    return parseInt($(':checked', this.formElement).val(), 10);
};
EntityForm.Fields.Toggle.prototype.Set = function(val){
    this.Parent('Set', [val ? 1 : 0], EntityForm.Fields.Toggle);
    this.emit('change');
};


/**
 * Entity List field
 * @param {string|Object} attribute
 *
 * @param {Object} options
 * @param {string} [options.label]
 * @param {string} [options.help]
 *
 * @param {Function[]} options.entities Min 1 Entity
 * @param {Function} [options.labelFunction] function(obj, labelElement){}
 *
 * @param {string|Function} entity
 *
 * @property formElement
 *
 * @extends EntityForm.Fields.FormField
 * @extends Templatable
 * @constructor
 */
EntityForm.Fields.EntityList = function (attribute, options, entity){
    this.Parent(null, arguments, EntityForm.Fields.EntityList);
    if (this.attribute instanceof Entity.Attributes.EntityList){
        this.attribute = new Entity.Attributes.Object(this.attribute.name);
    }else{
        throw "Invalid attribute for an EntityList field.";
    }

    /**
     *
     * @type {EntityForm[]}
     */
    this.entityForms = [];
};
EntityForm.Fields.EntityList.inherit(Templatable);
EntityForm.Fields.EntityList.inherit(EntityForm.Fields.FormField);

EntityForm.Fields.EntityList.templates = {
    'entityElement':
        `<li class="list-group-item self-clear">
            <div class="viewSection">
                <a class="remove btn btn-danger btn-xs pull-right" style="width: 70px;">Remove</a>
                <a class="edit btn btn-primary btn-xs pull-right" style="width: 70px;">Edit</a>
                <span class="entityLabel"></span>
            </div>
            <div class="editSection self-clear" style="display: none;">
                <div class="entityForm"></div>
                <div>
                    <a class="cancel btn btn-danger btn-xs pull-right" style="width: 70px;">Cancel</a>
                    <a class="save btn btn-success btn-xs pull-right" style="width: 70px;">Save</a>
                </div>
            </div>
        </li>`
};

EntityForm.Fields.EntityList.prototype.CreateElement = function(){
    this.Parent('CreateElement', arguments, EntityForm.Fields.EntityList);

    if (this.btnDiv){
        this.btnDiv.appendTo(this.element);
    }
};
EntityForm.Fields.EntityList.prototype.CreateFormElement = function(){
    var that = this;
    this.formElement = $('<ul />').attr('class', 'list-group');

    if (this.options.entities){
        var multipleEntities = this.options.entities.length > 1;

        this.btnDiv = $('<div />')
            .attr('class', multipleEntities ? 'dropdown' : '');
        this.addButton = $('<button />')
            .attr('type', 'button')
            .attr('class', 'btn btn-sm btn-success')
            .attr('data-toggle', multipleEntities ? 'dropdown' : '')
            .addClass(multipleEntities ? 'dropdown-toggle' : '')
            .html('New' + (multipleEntities ? ' <span class="caret"></span>' : ''))
            .appendTo(this.btnDiv);

        if (multipleEntities){
            this.dropdownElement = $('<ul />')
                .attr('class', 'dropdown-menu')
                .appendTo(this.btnDiv);
        }
    }
};
EntityForm.Fields.EntityList.prototype.BindButtonEvents = function(){
    var that = this;

    if (this.options.entities){
        var multipleEntities = this.options.entities.length > 1;

        this.addButton
            .click(function(){
                if (!multipleEntities){
                    that.AddEntity(that.options.entities[0]);
                }
            });

        if (multipleEntities){
            this.dropdownElement.html('');

            for(var i = 0; i < this.options.entities.length; i++){
                var entity = this.options.entities[i];
                var label, value;

                if (entity.label) {
                    label = entity.label;
                    value = entity.value;
                }else{
                    if (entity instanceof Function){
                        label = entity.name;
                        value = entity;
                    }else{
                        label = value = entity;
                    }
                }

                var li = $('<li />').appendTo(this.dropdownElement);
                var link = $('<a />')
                    .data('entity', value)
                    .html(label)
                    .appendTo(li)
                    .click(function(){
                        var data = $(this).data('entity');
                        that.AddEntity(data);
                    });
            }
        }
    }
};
EntityForm.Fields.EntityList.prototype.HideEdit = function(entity, element){
    this.currentEdit = null;
    var that = this;

    var editSection = $('>.editSection', element);
    var viewSection = $('>.viewSection', element);

    viewSection.css({
        opacity: 0,
        height: 'auto'
    });

    var viewHeight = viewSection.outerHeight();

    viewSection
        .css({
            height: 0,
            opacity: 1,
            overflow: 'hidden'
        })
        .animate({
            height: viewHeight
        }, 300);

    editSection
        .css({
            height: editSection.outerHeight(),
            overflow: 'hidden'
        })
        .animate({
            height: 0
        }, 300, function () {
            that.emit('editHidden')
        });
};
EntityForm.Fields.EntityList.prototype.ShowEdit = function(entity, element){
    var that = this;

    if (this.currentEdit){
        this.HideEdit(this.currentEdit.entity, this.currentEdit.element);
        this.once('editHidden', function () {
            that.ShowEdit(entity, element);
        });

        return;
    }

    this.currentEdit = {
        entity: entity,
        element: element
    };

    var form = this.GetEntityForm(entity.constructor);
    form.element.detach();

    $('.entityForm', element).html('').append(form.element);

    form.Update(entity);

    var editSection = $('>.editSection', element);
    var viewSection = $('>.viewSection', element);

    editSection.css({
        opacity: 0,
        display: 'block',
        height: 'auto'
    });

    var editHeight = editSection.outerHeight();

    editSection
        .css({
            height: 0,
            opacity: 1,
            overflow: 'hidden'
        })
        .animate({
            height: editHeight
        }, 300, function () {
            editSection.css('height', '');
        });

    viewSection
        .css({
            height: viewSection.outerHeight(),
            overflow: 'hidden'
        })
        .animate({
            height: 0
        }, 300, function () {
            that.emit('editShown')
        });

    $('a.save', element)
        .off('click')
        .click(function(){
            form.Read(entity);
            entity.Save();

            that.HideEdit(entity, element);
        });

    $('a.cancel', element)
        .off('click')
        .click(function(){
            that.HideEdit(entity, element);
        });
};
EntityForm.Fields.EntityList.prototype.CreateEntityElement = function(entity){
    var that = this;

    var element = this.Template('entityElement')
        .appendTo(this.formElement);

    var btnRemove = $('a.remove', element)
        .click(function(){
            if (!confirm('Are you sure you want to remove this?')) return;
            that.RemoveEntity(entity);
        });

    var btnEdit = $('a.edit', element)
        .click(function(){
            that.ShowEdit(entity, element)
        });

    var label = $('.entityLabel', element);
    label
        .html(this.options.labelFunction ? this.options.labelFunction(entity, label) : entity.toString());

    entity.off('save');
    entity.on('save', function(){
        label
            .html(that.options.labelFunction ? that.options.labelFunction(entity, label) : entity.toString());
    });

    return element;
};

EntityForm.Fields.EntityList.prototype.GetEntityForm = function(entity){
    if (!this.entityForms[entity.name]){
        this.entityForms[entity.name] = new EntityForm(entity);
    }
    return this.entityForms[entity.name];
};

EntityForm.Fields.EntityList.prototype.Get = function(){
    return this.list;
};
EntityForm.Fields.EntityList.prototype.Set = function(val){
    if (!val) val = [];
    this.formElement.html('');
    this.list = val.concat([]);
    for(var i = 0; i < val.length; i++){
        var el = this.CreateEntityElement(val[i]);
    }

    this.BindButtonEvents();
};
EntityForm.Fields.EntityList.prototype.AddEntity = function(entity){
    var cl;

    if (entity instanceof Function && Entity.IsParent(entity)){
        cl = entity;
    }else{
        cl = Entity.ClassMap.PHP2JS(entity);
    }

    var o = new cl();
    this.list.push(o);
    var el = this.CreateEntityElement(o);
    this.ShowEdit(o, el);
};
EntityForm.Fields.EntityList.prototype.RemoveEntity = function(entity){
    var idx = this.list.indexOf(entity);
    if (idx >= 0){
        this.list.splice(idx, 1);
    }
    this.Set(this.list);
};


/**
 *
 * @param attribute
 * @param options Object
 * @param options.label string
 * @param options.entity Function
 * @param options.entityLabel Function | string
 * @param options.selectedTableLabel string
 * @param options.allTableLabel string
 * @param options.customColumns Object
 * @param options.customColumns.all Array
 * @param options.customColumns.selected Array
 * @param options.customColumns.unselected Array
 * @param options.footer {*|jQuery|HTMLElement}
 * @param entity
 *
 * @extends Templatable
 * @extends EntityForm.Fields.FormField
 *
 * @constructor
 */
EntityForm.Fields.EntitySearch = function (attribute, options, entity){
    this.Parent(null, arguments, EntityForm.Fields.EntitySearch);

    // Make sure we have access to an entity list
    if (this.attribute instanceof Entity.Attributes.EntityList){
        // Access the object directly to avoid json conversions
        this.attribute = new Entity.Attributes.Object(this.attribute.name);
    }else{
        throw "Invalid attribute for an EntitySearch field.";
    }
};
EntityForm.Fields.EntitySearch.inherit(Templatable);
EntityForm.Fields.EntitySearch.inherit(EntityForm.Fields.FormField);

EntityForm.Fields.EntitySearch.templates = {
    btnEdit: '<button class="btn btn-success">Edit</button>',
    panel: '<div class="panel panel-primary">{{#if title}}<div class="panel-heading">{{title}}</div>{{/if}}<div class="panel-body"></div>{{#if footer}}<div class="panel-footer self-clear"></div>{{/if}}</div>'
};

EntityForm.Fields.EntitySearch.prototype.CreateElement = function(){
    this.Parent('CreateElement', arguments, EntityForm.Fields.EntitySearch);

    this.CreateModal();

    var that = this;
    this.btnDiv = $('<div />').appendTo(this.element);
    this.addButton = this.Template('btnEdit')
        .appendTo(this.btnDiv)
        .click(function(){
            that.OpenModal();
        });
};
EntityForm.Fields.EntitySearch.prototype.CreateModal = function(){
    this.modal = new StackingModal({
        title: this.options.label,
        dialog: {
            addClass: 'modal-lg'
        }
    });

    var that = this;

    this.modalContent = $('<div>');
    this.modal.SetBody(this.modalContent);


    this.panelChosen = this.Template('panel', {title: this.options.selectedTableLabel || 'Selected', footer: this.options.footer ? 1 : 0})
        .appendTo(this.modalContent);
    if (this.options.footer){
        $('.panel-footer').append(this.options.footer);
    }

    this.tableChosen = new DataTable(this.options.entity);
    this.tableChosen.element.appendTo($('.panel-body', this.panelChosen));


    this.panelAll = this.Template('panel', {title: this.options.allTableLabel || 'All'})
        .appendTo(this.modalContent);

    this.tableAll = new DataTable(this.options.entity);
    this.tableAll.element.appendTo($('.panel-body', this.panelAll));
    
    
    /// Custom Columns
    if (this.options.customColumns){
        if (this.options.customColumns.all){
            this.tableChosen.AddColumns(this.options.customColumns.all);
            this.tableAll.AddColumns(this.options.customColumns.all);
        }
        if (this.options.customColumns.selected){
            this.tableChosen.AddColumns(this.options.customColumns.selected);
        }
        if (this.options.customColumns.unselected){
            this.tableAll.AddColumns(this.options.customColumns.unselected);
        }
    }
    
    // Unselect column
    this.tableChosen.AddColumns([
        new DataTable.ActionColumn({
            label: 'Un-select',
            btnClass: 'btn-default',
            action: function(obj){
                that.Unselect(obj);
            },
            style: {
                width: '100px'
            }
        })
    ]);
    
    // Select column
    this.tableAll.AddColumns([
        new DataTable.ActionColumn({
            label: 'Select',
            btnClass: 'btn-primary',
            action: function(obj){
                that.Select(obj);
            },
            style: {
                width: '100px'
            }
        })
    ]);

    this.saveButtonElement = $('<button />')
        .attr('class', 'btn btn-success')
        .html('Done')
        .on('click', function(){ that.modal.Close(); })
        .appendTo(this.modal.footerElement);
};
EntityForm.Fields.EntitySearch.prototype.LoadData = function(callback){
    if (!this.fullList){
        var that = this;

        Entity.Select(this.options.entity, [], function(a){
            that.fullList = a;
            if (callback) callback();
        });
    }else{
        if (callback) callback();
    }
};
EntityForm.Fields.EntitySearch.prototype.OpenModal = function(){
    var that = this;
    this.addButton.addClass('disabled');
    
    this.LoadData(function () {
        that.addButton.removeClass('disabled');
        that.FillAll();
        that.modal.Open();
    });
};
EntityForm.Fields.EntitySearch.prototype.FillAll = function(){
    if (this.fullList){
        var that = this;
        var filtered = this.fullList.filter(function (item) {
            return !(that.list.filter(function (selectedItem) {
                return selectedItem.Equals(item);
            }).length);
        });

        this.tableAll.Fill(filtered);
    }
};
EntityForm.Fields.EntitySearch.prototype.CreateFormElement = function(){
    this.formElement = $('<ul />').attr('class', 'list-group');
};
EntityForm.Fields.EntitySearch.prototype.CreateEntityElement = function(entity){
    var that = this;

    var element = $('<li />')
        .attr('class', 'list-group-item self-clear')
        .appendTo(this.formElement);

    var label = $('<span />')
        .html(this.options.entityLabel instanceof Function ? this.options.entityLabel(entity) : (this.options.entityLabel ? entity.Get(this.options.entityLabel) : entity.toString()))
        .appendTo(element);

    return element;
};
EntityForm.Fields.EntitySearch.prototype.Get = function(){
    return this.list;
};
EntityForm.Fields.EntitySearch.prototype.Set = function(val){
    if (!val) val = [];
    this.formElement.html('');
    this.list = val.concat([]);

    for(var i = 0; i < val.length; i++){
        this.CreateEntityElement(val[i], val);
    }

    this.tableChosen.Fill(this.list);
    this.FillAll();
};
EntityForm.Fields.EntitySearch.prototype.Select = function(obj){
    var r = this.list.filter(function (item) {
        return item.Equals(obj)
    });

    var idx = r.length ? this.list.indexOf(r[0]) : -1;

    if (idx === -1){
        this.list.push(obj);
        this.Set(this.list);
    }
};
EntityForm.Fields.EntitySearch.prototype.Unselect = function(obj){
    var idx = this.list.indexOf(obj);
    if (idx > -1){
        this.list.splice(idx, 1);
        this.Set(this.list);
    }
};


/**
 * Inline Entity field
 * @param {string|Object} attribute
 *
 * @param {Object} options
 * @param {string} [options.label]
 * @param {string} [options.help]
 *
 * @param {Function} options.entity
 * @param {EntityForm} [options.form]
 *
 * @param {string|Function} entity
 *
 * @property formElement
 *
 * @extends EntityForm.Fields.FormField
 * @constructor
 */
EntityForm.Fields.InlineEntity = function (attribute, options, entity){
    this.Parent(null, arguments, EntityForm.Fields.InlineEntity);
};
EntityForm.Fields.InlineEntity.inherit(EntityForm.Fields.FormField);
EntityForm.Fields.InlineEntity.prototype.CreateElement = function(){
    this.Parent('CreateElement', arguments, EntityForm.Fields.InlineEntity);

    this.element.attr('class', 'well well-sm');
};
EntityForm.Fields.InlineEntity.prototype.CreateFormElement = function(){
    var that = this;

    this.entityForm = this.options.form ? this.options.form : new EntityForm(this.options.entity);

    this.formElement = this.entityForm.element;
};
EntityForm.Fields.InlineEntity.prototype.Get = function(){

    var obj = new this.options.entity();

    this.entityForm.Read(obj);

    return obj;
};
EntityForm.Fields.InlineEntity.prototype.Set = function(val){
    if (!val) val = new this.options.entity();

    this.entityForm.Update(val);
};



// TimeInput
EntityForm.Fields.TimeInput = function (attribute, options, entity){
    this.Parent(null, arguments, EntityForm.Fields.TimeInput);
};
EntityForm.Fields.TimeInput.inherit(EntityForm.Fields.FormField);
EntityForm.Fields.TimeInput.prototype.CreateFormElement = function(){
    var that = this;

    this.formElement = $('<div></div>')
        .addClass('input-group');

    this.hourInputElement = $('<input />')
        .attr('class', 'form-control')
        .attr('type', 'number')
        .attr('placeholder', 'hh')
        .attr('title', 'hour')
        .addClass(this.options.class)
        .on('change keyup keydown', function () {
            that.emit('change');
        })
        .appendTo(this.formElement);

    $('<span></span>')
        .attr('class', 'input-group-addon')
        .html(':')
        .appendTo(this.formElement);

    this.minuteInputElement = $('<input />')
        .attr('class', 'form-control')
        .attr('type', 'number')
        .attr('placeholder', 'mm')
        .attr('title', 'minute')
        .addClass(this.options.class)
        .on('change keyup keydown', function () {
            that.emit('change');
        })
        .appendTo(this.formElement);

    $('<span></span>')
        .attr('class', 'input-group-addon')
        .html(':')
        .appendTo(this.formElement);

    this.secondInputElement = $('<input />')
        .attr('class', 'form-control')
        .attr('type', 'number')
        .attr('placeholder', 'ss')
        .attr('title', 'second')
        .addClass(this.options.class)
        .on('change keyup keydown', function () {
            that.emit('change');
        })
        .appendTo(this.formElement);
};
EntityForm.Fields.TimeInput.prototype.Get = function(){
    var hours = parseInt(this.hourInputElement.val(), 10);
    var minutes = parseInt(this.minuteInputElement.val(), 10);
    var seconds = parseInt(this.secondInputElement.val(), 10);
    if (isNaN(hours)) hours = 0;
    if (isNaN(minutes)) minutes = 0;
    if (isNaN(seconds)) seconds = 0;


    return (hours*(60*60))+(minutes*(60))+seconds;
};
EntityForm.Fields.TimeInput.prototype.Set = function(val){
    if (!val) val = 0;

    var hours = Math.floor(val/(60*60));
    val -= hours*(60*60);

    var minutes = Math.floor(val/(60));
    val -= minutes*(60);

    var seconds = val;

    this.hourInputElement.val(hours);
    this.minuteInputElement.val(minutes);
    this.secondInputElement.val(seconds);
};