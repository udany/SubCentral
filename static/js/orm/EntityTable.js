/*
 * Description: EntityModal class
 * Version: 0.1
 * Author: Daniel Andrade
 * Date: 19/11/2015
 * This code may not be reused without proper permission from its creator.
 */
/**
 * DataTable class
 * @param columns
 * @param data
 * @extends Emitter
 * @constructor
 */
function DataTable(columns, data){
    if (Entity.IsParent(columns)){
        this.entity = columns;
        columns = columns.TableColoumns;
    }
    if (!data) data = [];

    this.columns = columns.concat([]);
    /** @type Object[] */
    this.data = data;
    /** @type DataTable.SortData[] */
    this.sorts = [];

    this.CreateElement();

    var sorts = [];

    for (var i = 0, c = this.columns.length; i < c; i ++){
        var col = this.columns[i];
        if (col.defaultSort){
            if (col.defaultSort instanceof Object){
                sorts[col.defaultSort.index] = {direction: col.defaultSort.direction, column: i};
            }else{
                sorts.push({direction: col.defaultSort, column: i})
            }
        }
    }
    for (i = 0, c = sorts.length; i < c; i ++){
        var sort = sorts[i];

        this.SortChange(sort.column, sort.direction);
    }

}
DataTable.inherit(Emitter);
DataTable.prototype.AddColumns = function(col){
    if (!(col instanceof Array)){
        col = [col];
    }

    for (var i = 0, c = col.length; i < c; i++){
        this.columns.push(col[i]);
    }

    this.CreateHeader();
    this.Fill();
};
DataTable.prototype.CreateElement = function(){
    var that = this;

    this.element = $('<div />');

    this.filterElement = $('<div />').appendTo(this.element);

    this.tableElement = $('<table />').attr('class', 'table table-hover').appendTo(this.element);
    this.tableHeader = $('<thead>').appendTo(this.tableElement);
    this.tableBody = $('<tbody>').appendTo(this.tableElement);

    this.footerElement = $('<div />').appendTo(this.element);

    this.CreateHeader();
    this.Fill();
};
DataTable.prototype.CreateHeader = function(){
    if (this.header) this.header.remove();

    this.header = $('<tr />');

    this.columnHeaders = [];

    var that = this;

    for (var i = 0; i < this.columns.length; i++){
        var column = this.columns[i];


        this.columnHeaders[i] = $('<th />')
            .append(column.label)
            .append(' <span class="glyphicon"></span>')
            .appendTo(this.header)
            .css('cursor', column.orderable ? 'pointer' : 'default')
            .attr('data-index', i)
            .on('click', function (e) {
                that.SortChange(parseInt($(this).attr('data-index')));
            })
            .css(column.style);
    }

    this.UpdateSortUI();

    this.tableHeader.append(this.header);
};
DataTable.prototype.CreateRow = function(rowData){
    var row = $('<tr />').appendTo(this.tableBody);
    var colElements = [];

    for (var j = 0; j < this.columns.length; j++){
        colElements[j] = $('<td />').appendTo(row);
    }

    this.FillRow(colElements, rowData);

    this.emit('fillRow', [rowData, row]);
};
DataTable.prototype.FillRow = function(colElements, rowData){
    for (var j = 0; j < this.columns.length; j++){
        var column = this.columns[j];
        var el = colElements[j];

        column.FillCell(rowData, el);
    }
};
DataTable.prototype.Fill = function(data){
    var that = this;
    if (data) this.data = data.concat([]);

    // Clear the body
    this.tableBody.html('');

    var saveEventId = this.GetUId()+'save';
    var deleteEventId = this.GetUId()+'save';

    var sortedData = this.data.concat([]).sort(function (a, b) {
        return that.Compare(a, b);
    });
    
    for (var i = 0; i < sortedData.length; i++){
        var rowData = sortedData[i];
        this.CreateRow(rowData);
        if (rowData.on){
            rowData.off('save', saveEventId).on('save', function(){
                that.Fill();
            }, saveEventId);
            rowData.off('delete', deleteEventId).on('delete', function(){
                var idx = that.data.indexOf(this);
                if (idx >= 0){
                    that.data.splice(idx, 1);
                    that.Fill();
                }
            }, null);
        }
    }
};
DataTable.prototype.SortChange = function(index, direction){
    var column = this.columns[index], sort;

    if (!column.orderable) return;

    var result = this.sorts.filter(function (item) {
        return item.column === column;
    });

    if (result.length){
        sort = result[0];
    }else{
        sort = new DataTable.SortData(this.columns[index]);
    }

    if (direction!==null && typeof direction != typeof undefined){
        sort.direction = direction;
    }else{
        sort.NextState();
    }
    
    if (sort.direction !== 0 && !result.length){
        this.sorts.push(sort);
    }else if (sort.direction == 0 && result.length){
        var currentIdx = this.sorts.indexOf(sort);
        this.sorts.splice(currentIdx, 1);
    }

    this.UpdateSortUI();

    this.Fill();
};
DataTable.prototype.UpdateSortUI = function(){
    $('.glyphicon', this.header).attr('class', 'glyphicon').html('');

    for (var i = 0, c = this.sorts.length; i < c; i ++){
        var sort = this.sorts[i];
        var colIdx = this.columns.indexOf(sort.column);
        var el = this.columnHeaders[colIdx];

        var cl = sort.direction === 1 ? 'triangle-top' : 'triangle-bottom';

        $('.glyphicon', el).attr('class', 'glyphicon glyphicon-'+cl).html(i+1);
    }
};
/**
 * Compares two objects returning 1, 0 or -1 for GT, EQ and LT
 * based on all sorting parameters in effect
 * @param a Object
 * @param b Object
 * @return {number}
 */
DataTable.prototype.Compare = function (a, b) {
    for (var i = 0, c = this.sorts.length; i < c; i ++){
        var sort = this.sorts[i];
        var r = sort.Compare(a, b);
        if (r !== 0) return r;
    }

    return 0;
};

/**
 * SOrt data for DataTable
 * @param column DataTable.Column
 * @constructor
 */
DataTable.SortData = function(column){
    this.column = column;
    this.direction = 0;
};
DataTable.SortData.prototype.NextState = function () {
    if (this.direction === 0){
        this.direction = 1;
    }else if (this.direction === 1){
        this.direction = -1;
    }else{
        this.direction = 0;
    }
};
/**
 * Compares two objects returning 1, 0 or -1 for GT, EQ and LT
 * GT and LT are flipped if sorting direction is DESC
 * @param a Object
 * @param b Object
 * @return {number}
 */
DataTable.SortData.prototype.Compare = function (a, b) {
    return this.column.Compare(a, b) * this.direction;
};


/**
 * @param {Object} o
 * @param {string}                      o.label
 * @param {string | Object | Function}  [o.attribute]
 * @param {Object}                      [o.style]
 * @param {boolean}                     [o.html]
 * @param {boolean | Function}          [o.orderable]
 * @param {number | Object}             [o.defaultSort]
 * @param {number}                      [o.defaultSort.index]
 * @param {number}                      [o.defaultSort.direction]
 *
 * @extends Emitter
 * @constructor
 */
DataTable.Column = function(o){
    this.options = o;

    this.label = o.label;
    this.attribute = o.attribute ? o.attribute : o.label;
    this.style = o.style ? o.style : {};
    this.html = o.html !== false;
    this.orderable = o.orderable ? o.orderable : null;
    this.defaultSort = o.defaultSort !== null ? o.defaultSort : 0;
};
DataTable.Column.inherit(Emitter);
DataTable.Column.prototype.GetValue = function(obj){
    if (this.attribute.Get){
        return this.attribute.Get(obj);
    }else if (this.attribute instanceof Function){
        return this.attribute.bind(this)(obj);
    }else{
        return obj[this.attribute];
    }
};
DataTable.Column.prototype.FillCell = function(obj, cell){
    var val = this.GetValue(obj);

    if (this.html){
        cell.html(val);
    }else{
        cell.text(val);
    }

    if (this.style){
        cell.css(this.style);
    }

    /**
     * @event DataTable.Column#fillCell
     */
    this.emit('fillCell', [obj, cell]);
};
DataTable.Column.prototype.Compare = function(a, b){
    if (this.orderable){
        if (this.orderable instanceof Function){
            return this.orderable(a, b);
        }else{
            var valA = this.GetValue(a);
            var valB = this.GetValue(b);

            if (valA < valB){
                return -1;
            }else if (valA > valB){
                return 1;
            }
        }
    }

    return 0;
};


/**
 * @param {Object} o
 * @param {string}                      o.label
 * @param {string | Object | Function}  [o.attribute]
 * @param {Object}                      [o.style]
 * @param {boolean}                     [o.html]
 * @param {boolean | Function}          [o.orderable]
 * @param {number | Object}                [o.defaultSort]
 * @param {number}                         [o.defaultSort.index]
 * @param {number}                         [o.defaultSort.direction]
 *
 * @extends DataTable.Column
 * @constructor
 */
DataTable.BooleanColumn = function(o){
    this.Parent(null, arguments, DataTable.BooleanColumn);
};
DataTable.BooleanColumn.inherit(DataTable.Column);
DataTable.BooleanColumn.prototype.FillCell = function(obj, cell){
    this.Parent('FillCell', arguments, DataTable.BooleanColumn);

    var val = this.GetValue(obj);
    cell.html($('<span />').attr('class','glyphicon glyphicon-'+(val ? 'ok' : 'remove')));
};


/**
 * @param {Object} o
 * @param {string}                      o.label
 * @param {string}                      o.format
 * @param {string | Object | Function}  [o.attribute]
 * @param {Object}                      [o.style]
 * @param {boolean}                     [o.html]
 * @param {boolean | Function}          [o.orderable]
 * @param {number | Object}                [o.defaultSort]
 * @param {number}                         [o.defaultSort.index]
 * @param {number}                         [o.defaultSort.direction]
 *
 * @extends DataTable.Column
 * @constructor
 */
DataTable.DateColumn = function(o){
    this.Parent(null, arguments, DataTable.DateColumn );
};
DataTable.DateColumn .inherit(DataTable.Column);
DataTable.DateColumn.prototype.GetValue = function(obj){
    var val = this.Parent('GetValue', arguments, DataTable.DateColumn);
    if (val instanceof Date){
        return val.format(this.options.format)
    }

    return val;
};


/**
 * @param {Object} o
 * @param {string}                      o.label
 * @param {string | Object | Function}  o.attribute
 * @param {Object}                      [o.style]
 * @param {boolean}                     [o.html]
 * @param {boolean | Function}          [o.orderable]
 * @param {number | Object}                [o.defaultSort]
 * @param {number}                         [o.defaultSort.index]
 * @param {number}                         [o.defaultSort.direction]
 *
 * @param {string | Function}           o.entity
 * @param {string | Function}           o.entityAttribute
 * @param {string | Function}           o.entityLabel
 *
 * @extends DataTable.Column
 * @constructor
 */
DataTable.EntityColumn = function(o){
    this.Parent(null, arguments, DataTable.EntityColumn);

    this.entity = o.entity;
    this.entityAttribute = o.entityAttribute;
    this.entityLabel = o.entityLabel;
    this.loaded = false;
    this.loading = false;
    this.data = [];
};
DataTable.EntityColumn.inherit(DataTable.Column);
DataTable.EntityColumn.prototype.FillCell = function(obj, cell){
    var that = this;
    this.Parent('FillCell', arguments, DataTable.EntityColumn);

    if (!this.loaded){
        this.once('load', function(){
            that.FillCell(obj, cell);
        }, null, true);

        if (!this.loading)
            this.Load();
    }else{
        var val = this.GetValue(obj);
        var entity = this.data.filter(function(e){return e.Get(that.entityAttribute) === val});

        if (entity.length){
            entity = entity[0];
            cell.html(entity.Get(that.entityLabel));
        }
    }
};
DataTable.EntityColumn.prototype.Load = function(){
    var that = this;

    this.loading = true;

    Entity.Select(this.entity, [], function(data){
        that.data = data;
        that.loaded = true;
        that.emit('load');
    });
};


/**
 *
 * @param o Object
 * @param o.label string
 * @param o.actionLabel string The text within the button, defaults to o.label
 * @param o.btnClass string
 * @param o.style Object
 * @param o.action Function fn(obj){}
 * @constructor
 */
DataTable.ActionColumn = function(o){
    this.Parent(null, arguments, DataTable.ActionColumn);
    this.actionLabel = o.actionLabel || o.label;
    this.btnClass = 'btn-default';

    for (var i in o){
        if (o.hasOwnProperty(i)){
            this[i] = o[i];
        }
    }
};
DataTable.ActionColumn.inherit(DataTable.Column);
DataTable.ActionColumn.prototype.GetValue = function(obj){
    var that = this;
    var btn = $('<a />')
        .addClass('btn')
        .addClass('btn-xs')
        .addClass(this.btnClass)
        .html(this.actionLabel);

    if (this.action instanceof Function){
        btn.click(function(){
            if (that.action && that.action instanceof Function){
                that.emit('action', [obj]);
                that.action.bind(this)(obj);
            }
        });
    }

    if (this.url){
        btn.attr('href', this.url instanceof Function ? this.url(obj) : this.url);
    }
    if (this.target){
        btn.attr('target', this.target);
    }

    return btn;
};



DataTable.EntityEditColumn = function(o){
    var def = new OptionsReceiver(this.constructor.Options);
    
    def.action = function(obj){ 
        EntityModal.Open(obj, def.modalOptions);
    };
    
    arguments[0] = def.applyOptions(o);
    this.Parent(null, arguments, DataTable.EntityEditColumn);
};
DataTable.EntityEditColumn.inherit(DataTable.ActionColumn);
DataTable.EntityEditColumn.Options = {
    label: 'Edit',
    btnClass: 'btn-primary',
    style: {
        width: '60px'
    },
    modalOptions: {}
};

DataTable.EntityDeleteColumn = function(o){
    var def = new OptionsReceiver(this.constructor.Options);
    
    def.action = function(obj){
        if (confirm('Are you sure you want to delete this?')){
            obj.Delete(def.deleteOptions);
        }
    };

    arguments[0] = def.applyOptions(o);
    this.Parent(null, arguments, DataTable.EntityDeleteColumn);
};
DataTable.EntityDeleteColumn.inherit(DataTable.ActionColumn);
DataTable.EntityDeleteColumn.Options = {
    label: 'Remove',
    btnClass: 'btn-danger',
    style: {
        width: '100px'
    },
    deleteOptions: {}
};



/**
 * @param o Object
 * @param o.fieldType string | Function
 * @param o.fieldOptions Object
 * @param o.fieldEntity Function
 * @param o.autoUpdateObject boolean Defaults to true 
 * 
 * @param o.label string
 * @param o.attribute string | Object
 * @param o.style Object
 * @param o.html boolean
 * @param o.orderable boolean | Function
 * @param o.defaultSort integer
 * 
 * @extends DataTable.Column
 * @constructor
 */
DataTable.InlineFormColumn = function(o){
    this.Parent(null, arguments, DataTable.InlineFormColumn);

    if (o.fieldType instanceof Function){
        this.fieldType = o.fieldType;
    }else{
        this.fieldType = EntityForm.Fields[o.fieldType];
    }
    this.fieldOptions = o.fieldOptions;
    this.fieldEntity = o.fieldEntity;
    this.autoUpdateObject = o.autoUpdateObject !== false;

    this.html = true;
};
DataTable.InlineFormColumn.inherit(DataTable.Column);

DataTable.InlineFormColumn.prototype.GetValue = function(obj){
    var that = this;

    if (!obj[this.GetUId()]){
        obj[this.GetUId()] = new this.fieldType(this.attribute, this.fieldOptions, this.fieldEntity);
    }

    /** @type EntityForm.Fields.FormField **/
    var field = obj[this.GetUId()];

    field.Update(obj);

    field.on('change', function () {
        if (that.autoUpdateObject) field.Read(obj);
        
        that.emit('change', [obj]);
    });

    return field.formElement.detach();
};
