/**
 * Created by Daniel on 04/04/2016.
 */

if (isModule()){
    var OptionsReceiver = require('./../General').OptionsReceiver;
    var Emitter = require('./../General').Emitter;

    require("./dropmenu.css");
}

/**
 *
 * @param o
 *
 * @extends OptionsReceiver
 * @extends Emitter
 * @constructor
 */
function DropMenu(o) {
    this.visible = false;

    this.applyOptions(o, {
        position: 'center',
        items: []
    });

    if (this.targetElement) {
        this.Bind(this.targetElement);
    }
}
DropMenu.inherit(OptionsReceiver);
DropMenu.inherit(Emitter);
DropMenu.prototype.GetElement = function () {
    if (!this.element){
        this.element = $('<div>')
            .addClass('drop-menu no-select')
            .append($('<div>').attr('class', 'triangle up'));
        this.list = $('<ul>')
            .appendTo(this.element);

        if (this.style){
            this.element.css(this.style);
        }
        if (this.class){
            this.element.addClass(this.class);
        }
        if (this.maxHeight){
            this.list.addClass('scrollable').css('maxHeight', this.maxHeight);
        }
        if (this.fixedLastOption){
            this.element.addClass('fixed-last-option');
        }
        if (this.fixed){
            this.element.css('position','fixed');
        }
    }
    return this.element;
};
DropMenu.prototype.Fill = function () {
    this.GetElement();

    $("li", this.element).tooltip('hide');

    this.list.html('');
    for (var i = 0; i < this.items.length; i++){
        var item = this.items[i];
        if (!(item instanceof DropMenuItem)){
            item = new DropMenuItem(item);
            this.items[i] = item;
        }

        this.list.append(item.GetElement());
    }
};
DropMenu.prototype.Show = function (target) {
    var that = this;

    if (DropMenu._current){
        DropMenu._current.Hide();
    }

    this.emit('beforeShow');

    target = $(target);
    var offset = target.offset();
    var size = {
        width: target.outerWidth(),
        height: target.outerHeight()
    };

    this.Fill();

    var menu = this.GetElement();

    menu.appendTo('body');

    var pos = {
        top: offset.top + size.height + 15 + (this.fixed ? -$(window).scrollTop() : 0)
    };


    if (this.position === 'left'){
        pos.left = offset.left + size.width - menu.width();
        $('.triangle', this.element).css('left', 'auto').css('right', '5px');
    }else if (this.position === 'center'){
        pos.left = offset.left + (size.width/2) - (menu.width()/2);
        $('.triangle', this.element).css('left', '50%').css('right', 'auto');
    }else if (this.position === 'right'){
        pos.left = offset.left;
        $('.triangle', this.element).css('left', '5px').css('right', 'auto');
    }

    menu
        .css(pos)
        .fadeIn(250, function(){
            that.emit('shown');
        });

    this.visible = true;

    this.emit('show');

    DropMenu._current = this;
};
DropMenu.prototype.Hide = function () {
    var that = this;
    this.element.fadeOut(150, function(){
        that.emit('hidden');
    });

    this.visible = false;
    this.emit('hide');
};

DropMenu.prototype.Bind = function (target) {
    target = $(target);
    target.css('cursor', 'pointer');

    var that = this;
    target.on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();

        if (!that.visible){
            that.Show(target);
            $(document).one('click', function () {
                that.Hide();
            });
        }else{
            that.Hide();
        }
    });

    this.boundElement = target;

    this.emit('bind');
};


function DropMenuItem(o) {
    this.applyOptions(o, {
        content: '',
        action: function () {}
    })
}
DropMenuItem.inherit(OptionsReceiver);
DropMenuItem.prototype.GetElement = function () {
    if (!this.element){
        var that = this;
        this.element = $('<li>');
        this.link = $('<a>')
            .attr('href', this.href ? this.href : '#')
            .on('mousedown', function (e) {
                if (that.action instanceof Function){
                    that.action(e);
                }
                if (!that.href){
                    e.preventDefault();
                    e.stopPropagation();
                }else{
                    return true;
                }
            })
            .on('click', function (e) {
                if (!that.href){
                    e.preventDefault();
                    e.stopPropagation();
                }else{
                    return true;
                }
            })
            .html(this.content)
            .appendTo(this.element);


        if (this.style){
            this.element.css(this.style);
        }
        if (this.linkStyle){
            this.link.css(this.linkStyle);
        }
        if (this.class){
            this.element.addClass(this.class);
        }
        if (this.tooltip){
            this.element.tooltip(this.tooltip);
        }
    }
    return this.element;
};
DropMenuItem.prototype.remove = function () {
    this.element.tooltip('hide');
    this.element.remove();
};


jQuery.fn.extend({
    dropMenu: function (o) {
        var el = $(this);
        var dm = new DropMenu(o);
        dm.Bind(el);
        el.data('dm-obj', dm);
        return this;
    }
});


if (isModule()) {
    module.exports.default = DropMenu;
    module.exports.DropMenu = DropMenu;
    module.exports.DropMenuItem = DropMenuItem;
}