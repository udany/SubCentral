/*
 * Description: A script
 * Version: 0.1
 * Author: Daniel Andrade
 * Date: 07/08/2016
 * This code may not be reused without proper permission from its creator.
 */
function EntityViewer(options) {
    this.options = new OptionsReceiver();

    this.options.applyOptions({
        entity: null,
        include: [],
        exclude: []
    });
    this.options.applyOptions(options);

    /**
     * @type EntityForm.Fields.BaseField[]
     */
    this.fields = [];

    var fields = this.options.entity.Fields;
    for (var i = 0, c = fields.length; i < c; i++) {
        var field = fields[i];
        if (
            (!this.options.include.length || this.options.include.indexOf(field.attributeName) >= 0) &&
            (!this.options.exclude.length || this.options.exclude.indexOf(field.attributeName) === -1)
        ){
            this.fields.push(field);
        }
    }
}
EntityViewer.inherit(Emitter);

EntityViewer.prototype.GetTable = function (obj) {

    var table = $('<table>').addClass('table table-hover table-condensed');
    var tableBody = $("<tbody>").appendTo(table);

    for (var i = 0, c = this.fields.length; i < c; i++){
        var field = this.fields[i];
        var label;

        if (field.options.label){
            label = field.options.label;
        }else {
            label = field.attributeName;
        }

        var row = $('<tr>').appendTo(tableBody);

        $('<td>').appendTo(row).html(label);
        $('<td>').appendTo(row).html(field.attribute.Get(obj));
    }
    
    return table;
};