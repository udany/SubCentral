/*
 * Description: A script
 * Version: 0.1
 * Author: Daniel Andrade
 * Date: 12/06/2016
 * This code may not be reused without proper permission from its creator.
 */

/**
 *
 * @param {object}          options
 * @param {string|Function} options.entity
 * @param {string}          [options.panelTitle]
 * @param {Object}          [options.saveOptions]
 * @param {Object}          [options.selectOptions]
 * @param {Object}          [options.deleteOptions]
 *
 * @extends Emitter
 * @extends Templatable
 * @constructor
 */
function EntityCrud(options) {
    this.options = new OptionsReceiver(this.constructor.Options);
    this.options.applyOptions(options);
    
    if (typeof this.options.entity === 'string'){
        if (window[this.options.entity]){
            this.options.entity = window[this.options.entity];
        }else{
            throw "Couldn't locate entity";
        }
        
        if (!Entity.IsParent(this.options.entity)){
            throw "You must use a class that inherits from Entity";
        }
    }

    var modal = EntityModal.Get(this.options.entity, {clone: true});
    var that = this;
    modal.on('save', function(){
        that.FillTable();
    });

    this.CreateElement();
}
EntityCrud.inherit(Emitter);
EntityCrud.inherit(Templatable);

EntityCrud.Options = {
    entity: null,
    panelTitle: "List",
    saveOptions: {},
    selectOptions: {},
    deleteOptions: {},
    form: null
};
EntityCrud.templates = {
    main: '<div class="panel panel-primary"><div class="panel-heading">{{panelTitle}}</div> <div class="panel-body" id="tbl"></div><div class="panel-footer self-clear"><a href="#" class="btn btn-success pull-right" id="btn-new">New</a></div></div>'
};

EntityCrud.prototype.CreateElement = function () {
    var that = this;

    this.element = this.Template('main', {
        panelTitle: this.options.panelTitle
    });
    
    this.dataTable = new DataTable(this.options.entity);
        
    this.dataTable.AddColumns([
        new DataTable.EntityEditColumn({
            modalOptions: this.getModalOptions()
        }),
        new DataTable.EntityDeleteColumn({
            deleteOptions: this.options.deleteOptions
        })
    ]);

    this.dataTable.element.appendTo($('#tbl', this.element));
    
    this.FillTable();

    $("#btn-new", this.element).on('click', function () {
        that.New();
    });
};
EntityCrud.prototype.ClearTable = function () {
    this.dataTable.Fill([]);
};
EntityCrud.prototype.FillTable = function () {
    var that = this;
    
    Entity.Select(this.options.entity, this.options.selectOptions, function(a){
        that.dataTable.Fill(a);
    });    
};
EntityCrud.prototype.getModalOptions = function () {
    var modalOptions = {
        clone: true,
        saveOptions: this.options.saveOptions
    };

    if(this.options.form){
        modalOptions.form = this.options.form;
    }
    
    return modalOptions
};

EntityCrud.prototype.New = function () {
    var that = this;
    var newObject = new this.options.entity([]);

    this.emit('beforeNew', [newObject]);
    
    EntityModal.Open(newObject, this.getModalOptions());
};