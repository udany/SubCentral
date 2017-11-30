/*
 * Description: EntityModal class
 * Version: 0.1
 * Author: Daniel Andrade
 * Date: 19/11/2015
 * This code may not be reused without proper permission from its creator.
 */
/**
 * @param {Object}      options
 * @param {Function}    options.entity
 * @param {Object}      [options.saveOptions]
 * @param {Boolean}     [options.clone]
 * @extends StackingModal
 * @constructor
 */
function EntityModal(options){
    this.Parent(null, arguments, EntityModal);

    this.options = new OptionsReceiver(EntityModal.Options);
    this.options.applyOptions(options);
    if (!this.options.form){
        if (!this.options.entity.Form){
            options.entity.Form = new EntityForm(options.entity);
        }

        this.options.form = this.options.entity.Form;
    }

    this.entity = this.options.entity;

    var that = this;
    this.title = function () {
        return that.entity.Name ? that.entity.Name : that.entity.name
    };

    this.on('shown', function () {
        that.GetOptionsMesh().form.emit('show');
        that.GetOptionsMesh().form.Focus();
    });
    this.on('dataPush', function () {
        that.GetOptionsMesh().form.Update(that.CurrentData());
    });
    this.on('dataPop', function () {
        if (that.CurrentData()) that.GetOptionsMesh().form.Update(that.CurrentData());
    });
    this.on('open', function () {
        var that = this;

        var options = this.GetOptionsMesh();

        this.saveButtonElement = this.Template('SaveButton', {clone: options.clone ? 1 : 0});
        this.SetFooter(this.saveButtonElement);

        $('.save-action', this.saveButtonElement)
            .on('click', function(){ that.Save() });

        $('.clone-action', this.saveButtonElement)
            .on('click', function(){ that.Clone() });


        that.GetOptionsMesh().form.emit('beforeShow');
        that.SetBody(that.GetOptionsMesh().form.element);
    });
}
EntityModal.inherit(StackingModal);
EntityModal.Options = {
    saveOptions: {}
};
window.onbeforeunload = function(e) {
    var ems = StackingModal.stack.filter(function (w) {
        return EntityModal.IsInstance(w);
    });
    if (ems.length){
        return "Your edits haven't been saved, close anyway?"
    }
};


EntityModal.templates.SaveButton = `<div class="btn-group">
  <button type="button" class="btn btn-success save-action">Save</button>
  {{#if clone}}
  <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
    <span class="caret"></span>
  </button>
  <ul class="dropdown-menu">
    <li><a href="#" class="clone-action">Clone</a></li>
  </ul>
  {{/if}}
</div>`;

EntityModal.prototype.GetOptionsMesh = function(){
    var options = new OptionsReceiver(this.options);
    return options.applyOptions(this.CurrentOptions());
};
EntityModal.prototype.Read = function(obj){
    if (!obj) obj = this.CurrentData();

    this.GetOptionsMesh().form.Read(obj);

    this.emit('read');
};
EntityModal.prototype.Save = function(){
    this.Read();

    this.emit('beforeSave');

    var obj = this.CurrentData();
    var that = this;

    obj.once('save', function () {
        that.emit('save');
    });

    obj.Save(this.GetOptionsMesh().saveOptions);
    this.Close();
};
EntityModal.prototype.Clone = function(){
    var obj = this.CurrentData();
    var opts = this.CurrentOptions();

    var data = obj.Serialize();
    var newObj = new obj.constructor(data);
    this.Read(newObj);

    newObj.Id = 0;

    this.CurrentDataPop();
    this.CurrentDataPush(newObj, opts);

    this.Save();
};

/**
 *
 * @param {Function} entity
 * @param {Object} [options]
 * @return EntityModal
 */
EntityModal.Get = function(entity, options){
    if (!options) options = {};
    options.entity = entity;

    if(!Entity.IsParent(entity)) return null;

    if (!entity.Modal) entity.Modal = new EntityModal(options);

    return entity.Modal;
};
/**
 *
 * @param {Entity} obj
 * @param {Object} [options]
 */
EntityModal.Open = function(obj, options){
    var modal = this.Get(obj.constructor);

    if (modal instanceof EntityModal) modal.Open(obj, options);
};