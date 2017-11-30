/*
 * Description: EntityModal class
 * Version: 0.1
 * Author: Daniel Andrade
 * Date: 19/11/2015
 * This code may not be reused without proper permission from its creator.
 */

if (isModule()){
    var Templatable = require('./../General').Templatable;
    var Emitter = require('./../General').Emitter;
}

/**
 * Creates stackable modals
 *
 * @param {Object} [options]
 * @param {string} [options.title]
 * @param {Object} [options.modal]
 * @param {Object} [options.dialog]
 * @param {Object} [options.header]
 * @param {Object} [options.body]
 * @param {Object} [options.footer]
 * @param {Object} [options.close] If false makes it undismissable via user input
 *
 * @extends Emitter
 * @extends Templatable
 * @constructor
 */
function StackingModal(options) {
    this.options = options ? options : {};
    this._currentData = [];
    this._currentOptions = [];
    this._scrollStack = [];
    this.CreateElement();
}
StackingModal.inherit(Templatable);
StackingModal.inherit(Emitter);

/** @type StackingModal[] */
StackingModal.stack = [];

StackingModal.templates = {
    modal: '<div class="modal fade" aria-hidden="false"><div class="modal-dialog"><div class="modal-content"></div></div></div>',
    header: '<div class="modal-header self-clear"> {{#if close}}<button class="close" aria-label="Close"><span aria-hidden="true">×</span></button>{{/if}} <h4 class="modal-title">{{title}}</h4></div>',
    body: '<div class="modal-body"></div>',
    footer: '<div class="modal-footer"></div>'
};

StackingModal.prototype.title = function(){
    return this.options.title ? this.options.title : '';
};

StackingModal.prototype.ApplyOptionsToElement = function(obj, element){
    if (!obj) return;

    if (obj.attr){
        element.attr(obj.attr)
    }
    if (obj.addClass){
        element.addClass(obj.addClass)
    }
    if (obj.css){
        element.css(obj.css)
    }
    if (obj.html){
        if (obj.html.detach) obj.html.detach();

        element.html(obj.html);
    }
};
StackingModal.prototype.CreateElement = function(){
    var that = this;

    this.element = this.Template('modal').modal({
        show: false,
        backdrop: 'static',
        keyboard: false
    });

    this.dialogElement = $('.modal-dialog', this.element);
    this.contentElement = $('.modal-content', this.element);

    this.headerElement = this.Template('header', {
            title: this.title instanceof Function ? this.title() : this.title,
            close: this.options.close !== false
        })
        .appendTo(this.contentElement);
    if (this.options.header === false) this.headerElement.css('display', 'none');

    this.closeButtonElement = $('.close', this.headerElement)
        .click(function(){ that.Close() });

    this.titleElement =  $('h4', this.headerElement);

    this.bodyElement = this.Template('body').appendTo(this.contentElement);

    this.footerElement = this.Template('footer').appendTo(this.contentElement);
    if (this.options.footer === false) this.footerElement.css('display', 'none');

    this.element.on('hidden.bs.modal', function (e) {
        setTimeout(function(){
            that.emit('hidden');
        }, 1);
    });
    this.element.on('shown.bs.modal', function (e) {
        setTimeout(function(){
            that.emit('shown');
            that.emit('opened');
        }, 1);
    });

    $(document.body).append(this.element);

    this.ApplyOptionsToElement(this.options.modal, this.element);
    this.ApplyOptionsToElement(this.options.dialog, this.dialogElement);
    this.ApplyOptionsToElement(this.options.header, this.headerElement);
    this.ApplyOptionsToElement(this.options.body, this.bodyElement);
    this.ApplyOptionsToElement(this.options.footer, this.footerElement);
};

StackingModal.prototype.SetBody = function(content){
    if (content.detach) content.detach();

    this.bodyElement.html(content);
};
StackingModal.prototype.SetTitle = function(content){
    if (content.detach) content.detach();

    this.titleElement.html(content);
};
StackingModal.prototype.SetFooter = function(content){
    if (content.detach) content.detach();

    this.footerElement.html(content);
};

/**
 * @private
 */
StackingModal.prototype.Show = function(){
    var width = $(window).width();

    this.element.modal('show');

    var newWidth = $(window).width();
    if (newWidth > width){
        var diff = newWidth - width;
        $('body').css({marginRight: diff});
    }
};
/**
 * @private
 */
StackingModal.prototype.Hide = function(){
    this.element.modal('hide');
    this.once('hidden', function () {
        $('body').css({marginRight: ''});
    })
};

StackingModal.prototype.Push = function(callback){
    if (StackingModal.stack.length){
        var top = StackingModal.stack[StackingModal.stack.length-1];
        StackingModal.stack.push(this);
        top._pushScroll();
        top.once('hidden', callback);
        top.Hide();
    }else{
        StackingModal.stack.push(this);
        if (callback) callback();
    }
};
StackingModal.prototype.Pop = function(){
    var top;

    if (StackingModal.stack.length){
        top = StackingModal.stack[StackingModal.stack.length-1];
        if (top != this) return;
    }
    StackingModal.stack.pop();

    if (StackingModal.stack.length){
        top = StackingModal.stack[StackingModal.stack.length-1];
        top.once('shown', function () {
            top._popScroll();
        });
        top.Show();
    }
};
StackingModal.prototype._pushScroll = function(){
    this._scrollStack.push(this.element.scrollTop());
};
StackingModal.prototype._popScroll = function(){
    var scroll = this._scrollStack.pop();
    if (scroll){
        this.element.animate({
            scrollTop: scroll
        }, 100);
    }
};

StackingModal.prototype.CurrentData = function(){
    return this._currentData.length ? this._currentData[this._currentData.length-1] : null;
};
StackingModal.prototype.CurrentOptions = function(){
    return this._currentOptions.length ? this._currentOptions[this._currentOptions.length-1] : null;
};
StackingModal.prototype.CurrentDataPush = function(obj, options){
    this._currentData.push(obj);
    this._currentOptions.push(options);
    this.emit('dataPush');
};
StackingModal.prototype.CurrentDataPop = function(){
    this._currentData.pop();
    this._currentOptions.pop();
    this.emit('dataPop');
};

/**
 *
 * @param [data]
 * @param [options]
 * @constructor
 */
StackingModal.prototype.Open = function(data, options){
    var that = this;

    this.Push(function(){
        that.CurrentDataPush(data, options);
        that.emit('open');
        that.Show();
    });

    if (typeof HistoryManager !== typeof undefined){
        that.__historyState = HistoryManager.push('', '', function () {
            that.Close();
        });
    }
};
StackingModal.prototype.Close = function(){
    var that = this;

    if (this.__historyState){
        HistoryManager.discard(this.__historyState);
    }

    this.once('hidden', function(){
        that.CurrentDataPop();
        that.Pop();
        
        that.emit('closed');
    });

    that.emit('close');

    this.Hide();
};

$(window).on('keydown', function (e) {
    if (e.which == 27){ // Esc key
        if (StackingModal.stack.length){
            var top = StackingModal.stack[StackingModal.stack.length-1];

            if (top.options.close !== false) top.Close();
        }
    }
});

if (isModule()) {
    module.exports = StackingModal;
}