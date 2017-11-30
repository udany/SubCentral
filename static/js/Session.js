if (isModule()){
    var OptionsReceiver = require('./General').OptionsReceiver;
}

var Session = new OptionsReceiver({
    URL: '',
    Token: '',

    actionPrefix: null,
    actionSuffix: null,
    controllerMethodPattern: '{0}/{1}',

    _getQueryString: function (params) {
        var queryStringParts = [];
        for (var i in params){
            if (params.hasOwnProperty(i)){
                queryStringParts.push(
                    encodeURIComponent(i)+'='+encodeURIComponent(params[i])
                )
            }
        }

        return (queryStringParts.length ? '?' : '')+queryStringParts.join('&');
    },
    GetActionUrl: function(controller, method,params){
        if (!method) method = controller;
        if (!params) params = [];
        
        var url = this.URL.split("/");

        if (!url[url.length-1]) url.splice(url.length-1, 1);

        if(this.actionPrefix !== null) url.push(this.actionPrefix);

        url.push(this.controllerMethodPattern.format([controller, method]));

        if(this.actionSuffix !== null) url.push(this.actionSuffix);

        return url.join('/')+this._getQueryString(params);
    },
    GetPageUrl: function(Page, params){
        return this.URL+Page+"/"+this._getQueryString(params);
    },
    Do: function(controller, action, data, success, method){
        if (!method) method = "POST";
        if (!data) {
            data = {}
        }
        data._token = this.Token;

        $.ajax({
            type: method,
            url: Session.GetActionUrl(controller, action),
            data: data,
            success: success,
            dataType: 'json'
        });
    }
}).applyOptions(window['Session']);


function InlineJsonToObject(name) {
    var data = $("script#"+name+"-json").html();
    return JSON.parse(data);
}
function InlineJsonToEntity(name) {
    return Entity.FromArray(InlineJsonToObject(name));
}
function InlineJsonToEntityList(name) {
    return InlineJsonToObject(name).ToEntityArray();
}

if (Vue){
    Vue.component('vue-link', {
        template: '<a :href="href" ref="input" :target="target"><slot></slot></a>',
        props: {
            target: {type: String, default: ''},
            page: {type: String, default: ""},
            controller: {type: String, default: ''},
            method: {type: String, default: ''},
            params: {type: Object, default: null}
        },
        computed: {
            href: function () {
                if (this.page){
                    return Session.GetPageUrl(this.page, this.params)
                }
                if (this.controller){
                    return Session.GetActionUrl(this.controller, this.method, this.params)
                }
            }
        },
        mounted: function () {
        }
    });
}


if (isModule()){
    module.exports.Session = Session;
    module.exports.InlineJsonToObject = InlineJsonToObject;
    module.exports.InlineJsonToEntity = InlineJsonToEntityList;
    module.exports.InlineJsonToEntityList = InlineJsonToEntityList;
}