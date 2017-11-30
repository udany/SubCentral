if (isModule()){
    var Templatable = require('./General').Templatable;
    require("./../css/Loading.css");
}

/**
 * @extends Templatable
 * @private
 */
function _loading(){
    this.element = this.Template('loader');
    this.textElement = $('.load-text', this.element);

    var that = this;

    $(function () {
        that.element.appendTo('body');
    });

    this.fadeTime = 100;
}
_loading.inherit(Templatable);
_loading.templates = {
    loader: '<div class="loader-overlay"><div class="loader"></div><div class="load-text"></div></div>'
};
_loading.prototype.Show = function (t, text) {
    if (typeof t === 'string') text = t;
    if (!text) text = '';
    this.textElement.html(text);
    this.element.fadeIn(t ? t : this.fadeTime);
};
_loading.prototype.Hide = function (t) {
    this.element.fadeOut(t ? t : this.fadeTime);
};
_loading.prototype.FadeTime = function () {
    if (arguments.length === 1){
        this.fadeTime = arguments[0];
    }else{
        return this.fadeTime;
    }
};

var Loading = new _loading();

if (isModule()){
    module.exports.Loading = Loading;
    module.exports._loading = _loading;
}