/*
 * Description: A script
 * Version: 0.1
 * Author: Daniel Andrade
 * Date: 27/01/2017
 * This code may not be reused without proper permission from its creator.
 */
Vue.directive('unhide', {
    bind: function (el) {
        $(el).css('display', '');
    },
    update: null,
    unbind: null
});

var _bootsvuePopover = function (el, binding) {
    var val = binding.value;
    var modifiers = binding.modifiers;
    var jqEl = $(el);

    if (val){
        var opts = {
            container: modifiers.body ? 'body' : false,
            trigger: modifiers.hover ? 'hover' : (modifiers.focus ? 'focus' : (modifiers.always ? 'manual' : 'click') ),
            content: val,
            html: modifiers.html ? true : false,
            placement: modifiers.top ? 'top' : (modifiers.left ? 'left' : (modifiers.bottom ? 'bottom' : 'right'))
        };

        var oldOpts = jqEl.data('vue-popover');

        if (JSON.stringify(oldOpts) == JSON.stringify(opts)) return;

        try{
            jqEl.popover('destroy');
        }catch (e){
            jqEl.popover('dispose');
        }
        jqEl.popover(opts);
        jqEl.data('vue-popover', opts);

        if (modifiers.focus && el === document.activeElement || modifiers.always){
            jqEl.popover('show');
        }
    }else{
        try{
            jqEl.popover('destroy');
        }catch (e){
            jqEl.popover('dispose');
        }

        jqEl.data('vue-popover', '');
    }
};
Vue.directive('popover', {
    bind: _bootsvuePopover,
    update: _bootsvuePopover,
    unbind: function (el, binding) {
        var jqEl = $(el);

        try{
            jqEl.popover('destroy');
        }catch (e){
            jqEl.popover('dispose');
        }

        jqEl.data('vue-popover', '');
    }
});

Vue.component('date-input', {
    template: '<input type="text" ref="input">',
    data: function () {
        return {
            currentValue: null
        }
    },
    props: {
        value: {type: Date, default: null},
        format: {
             default: 'DD/MM/YYYY'
        },
        minDate: {type: Date, default: null},
        maxDate: {type: Date, default: null},
        sideBySide: {type: String, default: ''},
        current: {type: String, default: ''},
        inline: {type: String, default: ''},
        debug: {type: String, default: ''},
        tooltips: {type: Object, default: null}
    },
    watch: {
        value: function (newVal) {
            if (newVal && (!this.currentValue || newVal.getTime() != this.currentValue.getTime())){
                this.currentValue = new Date(newVal.getTime());
                this.updateInput();
            }else if (!newVal){
                this.currentValue = null;
                this.updateInput();
            }
        }
    },
    methods: {
        updateValue: function (value) {
            this.$emit('input', value);
        },
        updateInput: function () {
            $(this.$refs.input).data("DateTimePicker").date(this.currentValue);
        }
    },
    mounted: function () {
        var input = $(this.$refs.input);

        var settings = {
            format: this.format,
            locale: 'pt-br',
            showTodayButton: true,
            showClear: true,
            toolbarPlacement: 'bottom',
            debug: !!this.debug,
            tooltips: {
                today: 'Hoje',
                clear: 'Limpar',
                close: 'Fechar',
                selectMonth: 'Selecione o mês',
                prevMonth: 'Mês anterior',
                nextMonth: 'Mês seguinte',
                selectYear: 'Selecione o ano',
                prevYear: 'Ano anterior',
                nextYear: 'Ano seguinte',
                selectDecade: 'Selecione a década',
                prevDecade: 'Década anteriora',
                nextDecade: 'Década seguinte',
                prevCentury: 'Século anterior',
                nextCentury: 'Século seguinte',
                selectTime: 'Selecione a hora'
            },
            icons: {
                time: 'fa fa-clock',
                date: 'fa fa-calendar',
                up: 'fa fa-chevron-up',
                down: 'fa fa-chevron-down',
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-crosshairs',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            },
            useCurrent: false
        };

        if (this.tooltips){
            settings.tooltips = this.tooltips;
        }

        if (this.minDate){
            settings.minDate = this.minDate;
        }
        if (this.maxDate){
            settings.minDate = this.maxDate;
        }
        if (this.sideBySide){
            settings.sideBySide = true;
        }
        if (this.current){
            settings.useCurrent = true;
        }
        if (this.inline){
            settings.inline = true;
            input.css('display', 'none');
        }

        input
            .datetimepicker(settings)
            .on('dp.change', (function (e) {
                this.updateValue(e.date ? e.date.toDate() : null);
            }).bind(this));


        if (this.value){
            this.currentValue = new Date(this.value.getTime());
            this.updateInput();
        }
    }
});

Vue.component('time-input', {
    template: '<input type="time" ref="input" :disabled="!currentValue" v-on:input="updateValue($event.target.value)">',
    data: function () {
        return {
            currentValue: null
        }
    },
    props: [
        'value'
    ],
    watch: {
        value: function (newVal) {
            if (newVal && (!this.currentValue || newVal.getTime() != this.currentValue.getTime())){
                this.currentValue = new Date(newVal.getTime());
                this.updateInput();
            }else if (!newVal){
                this.currentValue = null;
                this.updateInput();
            }
        }
    },
    methods: {
        updateValue: function (value) {
            value = value.split(':');

            var hour = parseInt(value[0]);
            var minute = parseInt(value[1]);

            if (!isNaN(hour) && !isNaN(minute)){
                this.currentValue.setHours(hour);
                this.currentValue.setMinutes(minute);

                this.$emit('input', new Date(this.currentValue.getTime()))
            }
        },
        updateInput: function () {
            if (this.currentValue){
                this.$refs.input.value = this.currentValue.format('H:i');
            }else{
                this.$refs.input.value = '';
            }
        }
    },
    mounted: function () {
        if (this.value){
            this.currentValue = this.value;
            this.updateInput();
        }
    }
});


Vue.component('cpf-input', {
    template: '<input type="tel" ref="input" v-on:input="updateValue($event.target.value)" v-popover.focus.top="popover">',
    data: function () {
        return {
            currentValue: null
        }
    },
    props: [
        'value'
    ],
    watch: {
        value: function (newVal) {
            if (newVal != this.currentValue){
                this.currentValue = newVal;
                this.updateInput();
            }
        }
    },
    computed: {
        popover: function () {
            if (this.valid){
                return '';
            }else{
                if (!this.currentValue){
                    return 'xxx.xxx.xxx-xx'
                }else{
                    return 'CPF inválido'
                }
            }
        },
        valid: function () {
            if (!this.currentValue) return null;

            var cpf = this.currentValue.replace(/[^\d]+/g,'');

            if(cpf == '') return false;
            // Elimina CPFs invalidos conhecidos
            if (cpf.length != 11 ||
                cpf == "00000000000" ||
                cpf == "11111111111" ||
                cpf == "22222222222" ||
                cpf == "33333333333" ||
                cpf == "44444444444" ||
                cpf == "55555555555" ||
                cpf == "66666666666" ||
                cpf == "77777777777" ||
                cpf == "88888888888" ||
                cpf == "99999999999")
                return false;
            // Valida 1o digito
            var add = 0;
            for (var i=0; i < 9; i ++)
                add += parseInt(cpf.charAt(i)) * (10 - i);
            var rev = 11 - (add % 11);
            if (rev == 10 || rev == 11)
                rev = 0;
            if (rev != parseInt(cpf.charAt(9)))
                return false;
            // Valida 2o digito
            add = 0;
            for (var i = 0; i < 10; i ++)
                add += parseInt(cpf.charAt(i)) * (11 - i);
            rev = 11 - (add % 11);
            if (rev == 10 || rev == 11)
                rev = 0;
            if (rev != parseInt(cpf.charAt(10)))
                return false;

            return true;
        }
    },
    methods: {
        updateValue: function (value) {
            var str = value.replace(/[^0-9]/gi, "");

            if (str.length > 11){
                str = str.substr(0,11);
            }

            if (str.length != 11){
                this.currentValue = '';

                this.$emit('input', this.currentValue);
            }else{
                str = str.substr(0,3) + "." + str.substr(3,3) + "." + str.substr(6,3) + "-" + str.substr(9,2);

                if (this.currentValue != str){
                    this.currentValue = str;
                }

                this.updateInput();

                if (this.valid){
                    this.$emit('input', this.currentValue);
                }else{
                    this.$emit('input', '');
                }
            }
        },
        updateInput: function () {
            if (this.currentValue){
                this.$refs.input.value = this.currentValue;
            }else{
                this.$refs.input.value = '';
            }
        }
    },
    mounted: function () {
        if (this.value){
            this.currentValue = this.value;

            this.updateInput();
        }
    }
});


Vue.component('telephone-input', {
    template: '<input type="tel" ref="input" v-on:input="updateValue($event.target.value)" v-popover.focus.top="popover">',
    data: function () {
        return {
            currentValue: null
        }
    },
    props: [
        'value'
    ],
    computed: {
        popover: function () {
            if (this.currentValue){
                return '';
            }else{
                return 'Lembre de incluir o DDD, o DDI é opcional.'
            }
        }
    },
    watch: {
        value: function (newVal) {
            if (newVal != this.currentValue){
                this.currentValue = newVal;
                this.updateInput();
            }
        }
    },
    methods: {
        updateValue: function (value) {
            var str = value.replace(/[^0-9]/gi, "");

            if (str.length == 14){
                // 999 99 99999-9999
                str = "+" + str.substr(0,3) + " " + str.substr(3,2) + " " + str.substr(5,5) + "-" + str.substr(10,4);

            }else if (str.length == 13){
                // 99 99 99999-9999
                str = "+" + str.substr(0,2) + " " + str.substr(2,2) + " " + str.substr(4,5) + "-" + str.substr(9,4);

            }else if (str.length == 12){
                // 99 99 9999-9999
                str = "+" + str.substr(0,2) + " " + str.substr(2,2) + " " + str.substr(4,4) + "-" + str.substr(8,4);

            }else if (str.length == 11){
                // 99 99999-9999
                str = str.substr(0,2) + " " + str.substr(2,5) + "-" + str.substr(7,4);

            }else if (str.length == 10){
                // 99 9999-9999
                str = str.substr(0,2) + " " + str.substr(2,4) + "-" + str.substr(6,4);
            }else{
                str = '';
            }

            this.currentValue = str;

            if (this.currentValue){
                this.updateInput();
            }

            this.$emit('input', this.currentValue);
        },
        updateInput: function () {
            if (this.currentValue){
                this.$refs.input.value = this.currentValue;
            }else{
                this.$refs.input.value = '';
            }
        }
    },
    mounted: function () {
        if (this.value){
            this.currentValue = this.value;

            this.updateInput();
        }
    }
});


Vue.component('postalcode-input', {
    template: '<input type="tel" ref="input" v-on:input="updateValue($event.target.value)" maxlength="9">',
    data: function () {
        return {
            currentValue: null
        }
    },
    props: [
        'value'
    ],
    computed: {
    },
    watch: {
        value: function (newVal) {
            if (newVal != this.currentValue){
                this.currentValue = newVal;
                this.updateInput();
            }
        }
    },
    methods: {
        updateValue: function (value) {
            var str = value.replace(/[^0-9]/gi, "");

            if (str.length == 8){
                // 999 99 99999-9999
                str = str.substr(0,5) + "-" + str.substr(5,3);
            }

            this.currentValue = str;

            if (this.currentValue){
                this.updateInput();
            }

            this.$emit('input', this.currentValue);
        },
        updateInput: function () {
            if (this.currentValue){
                this.$refs.input.value = this.currentValue;
            }else{
                this.$refs.input.value = '';
            }
        }
    },
    mounted: function () {
        if (this.value){
            this.currentValue = this.value;

            this.updateInput();
        }
    }
});


Vue.component('place-input', {
    template: '<input type="text" ref="input" placeholder="Escolha um local...">',
    data: function () {
        return {
            placesSearch: null,
            place: null
        }
    },
    props: [
        'value'
    ],
    computed: {
    },
    watch: {
        value: function (newVal) {
            this.updateInput(newVal);
        }
    },
    methods: {
        updateValue: function (value) {
            this.$emit('input', value);
        },
        updateInput: function (val) {
            if (val != this.place.formatted_address){
                this.$refs.input.value = val;

                this.place = null;
            }
        }
    },
    mounted: function () {
        if (this.value)
            this.updateInput(this.value);

        var vm = this;

        var input = this.$refs.input;

        GoogleMaps.OnLoad(function () {
            vm.placesSearch = new google.maps.places.Autocomplete(input,{
                //types: ['address']
            });

            vm.placesSearch.addListener('place_changed', function() {
                var place = vm.placesSearch.getPlace();

                if (!place.geometry) {
                    vm.place = null;
                    vm.updateValue('');
                }else{
                    vm.place = place;
                    vm.updateValue(place.formatted_address);
                }
            });
        });
    }
});


Vue.component('object-select', {
    template: '<select ref="input" v-on:input="updateValue($event.target.value)"><option :value="idx" v-for="(item, idx) in data">{{getLabel(item)}}</option></select>',
    data: function () {
        return {
            currentValue: null
        }
    },
    props: {
        itemId: {type: [String, Function], default: 'Id'},
        label: {type: [String, Function], default: 'Name'},
        value: {type: Object, default: null},
        data: {type: Array},
    },
    computed: {
    },
    watch: {
        value: function (newVal) {
            this.currentValue = newVal;
            this.updateInput();
        }
    },
    methods: {
        getKey: function (o) {
            return this.itemId instanceof Function ? this.itemId(o) : o[this.itemId];
        },
        getLabel: function (o) {
            return this.label instanceof Function ? this.label(o) : o[this.label];
        },
        updateValue: function (value) {
            this.currentValue = this.data[value];

            this.$emit('input', this.currentValue);
        },
        updateInput: function () {
            if (this.currentValue){
                let key = this.getKey(this.currentValue);
                let idx = this.data.map(this.getKey).indexOf(key);

                this.$refs.input.value = idx;
            }else{
                this.$refs.input.value = '';
            }
        }
    },
    mounted: function () {
        if (this.value){
            this.currentValue = this.value;
            this.updateInput();
        }
    }
});

Vue.component('drop-textarea', {
    template: `<textarea ref="field" v-on:input="updateValue($event.target.value)" :class="{'bg-primary': dragOver}"></textarea>`,
    data: function () {
        return {
            el: null,
            currentValue: null,
            dragOver: false
        }
    },
    props: {
        value: {type: String, default: ""},
    },
    computed: {
    },
    watch: {
        value: function (newVal) {
            this.currentValue = newVal;
            this.updateInput();
        }
    },
    methods: {
        updateValue: function (value) {
            this.currentValue = value;
            this.$emit('input', this.currentValue);
        },
        updateInput: function () {
            this.el.value = this.currentValue;
        },

        drop_handler: function (ev) {
            ev.preventDefault();

            this.dragOver = false;

            // If dropped items aren't files, reject them
            var dt = ev.dataTransfer;

            let f;

            if (dt.items && dt.items.length) {
                if (dt.items[0].kind == "string"){
                    let that = this;

                    dt.items[0].getAsString(function (v) {
                        that.updateValue(v);
                    });
                    return;
                }else{
                    f = dt.items[0].getAsFile();
                }
            } else if (dt.files && dt.files.length){
                f = dt.files[0];
            }

            if (f instanceof File){
                let that = this;
                let reader = new FileReader();

                reader.readAsText(f);

                reader.onload=function(){
                    that.updateValue(reader.result);
                }
            }
        },
        dragover_handler: function (ev) {
            if (this.dragOver){
                clearTimeout(this.dragOver);
            }

            let that = this;
            this.dragOver = setTimeout(function () {
                that.dragOver = false;
            }, 1000);

            ev.preventDefault();
        },
        dragend_handler: function (ev) {
            this.dragOver = false;
        }
    },
    mounted: function () {
        this.el =this.$refs.field;

        this.el.ondrop = this.drop_handler;
        this.el.ondragover = this.dragover_handler;
        this.el.ondragend = this.dragend_handler;

        if (this.value){
            this.currentValue = this.value;

            this.updateInput();
        }
    }
});


Vue.component('modal', {
    template: `<div class="modal" ref="modal" :class="classList"><div class="modal-dialog" :class="size ? 'modal-'+size : ''"><div class="modal-content">
  <div class="modal-header">
    <slot name="header"><h5 class="modal-title">{{title}}</h5></slot>
    <button type="button" class="close" v-if="headerClose" v-on:click="hide">&times;</button>
  </div>
  <div class="modal-body">
    <slot name="body"></slot>
  </div>
  <div class="modal-footer">
    <slot name="footer">
        <button type="button" class="btn btn-primary" v-for="(action, label) in actions" v-on:click="action">{{label}}</button>    
    </slot>
  </div>
</div></div></div>`,
    data: function () {
        return {
            el: null
        }
    },
    props: {
        title: {type: String, default: ""},
        headerClose: {type: Boolean, default: true},
        actions: {type: Object, default: ()=>{} },


        fade: {type: Boolean, default: true},
        size: {type: String, default: ""},
    },
    computed: {
        classList: function(){
            return {
                fade: this.fade
            }
        }
    },
    watch: {
    },
    methods: {
        show: function () {
            this.el.modal('show');
            this.$emit("show");
        },
        hide: function () {
            this.el.modal('hide');
            this.$emit("hide");
        },
    },
    mounted: function () {
        this.el = $(this.$refs.modal);
        this.el.modal({
            show: false
        });
    }
});