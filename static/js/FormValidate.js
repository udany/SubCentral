/*
 * Description: A script
 * Version: 0.1
 * Author: Daniel Andrade
 * Date: 04/11/2015
 * This code may not be reused without proper permission from its creator.
 */
var FormValidate = {
    _customValidators: [],
    validatorAdd: function(key, fn){
        this._customValidators.push({
            key: key,
            fn: fn
        });
    },
    ValidateElement: function(el){
        for (var i = 0; i < this._customValidators.length; i++){
            var v = this._customValidators[i];
            if(el.prop(v.key) || el.attr(v.key)){
                if (el.prop(v.key) != el.attr(v.key)){
                    el.attr(v.key, el.prop(v.key))
                }
                if (!v.fn(el)){
                    return false;
                }
            }
        }
        return true;
    }
};

jQuery.fn.extend({
    validate: function() {
        var hasErrors = false;

        this.validationClear();

        this.on('change',function(){
           $(this).validate();
        });

        $("input, textarea, select", this).each(function(){
            var el = $(this);
            var error = false;

            if(el.prop('required')){
                if (!el.val()){
                    error = true;
                }
            }

            if (el.prop("max")){
                var max = parseInt(el.prop("max"));
                var val = parseInt(el.val());
                if (val > max){
                    error = true;
                }
            }

            if (el.prop("min")){
                var min = parseInt(el.prop("min"));
                var val = parseInt(el.val());
                if (val < min){
                    error = true;
                }
            }

            if (el.prop("minlength")){
                var minLen = parseInt(el.prop("minlength"));
                if (el.val().length < minLen && minLen >= 0){
                    error = true;
                }
            }

            if (el.prop("maxlength")){
                var maxLen = parseInt(el.prop("maxlength"));
                if (el.val().length > maxLen && maxLen >= 0){
                    error = true;
                }
            }

            if (!FormValidate.ValidateElement(el)){
                error = true;
            }

            if (error){
                hasErrors = true;
                el.parent().removeClass("has-success").addClass("has-error");
            }else{
                if (!el.parent().hasClass('has-error'))
                    el.parent().addClass("has-success");
            }
        });
        return !hasErrors;
    },
    validationClear: function(){
        this.off('change');

        $(".has-error, .has-success", this).removeClass("has-success").removeClass("has-error");
    }
});


///Bundled validators

(function () {
    function NumbersOnly(el){
        var str = el.val();
        str = str.replace(/[^0-9]/gi, "");

        el.val(str);
    }

    FormValidate.validatorAdd('uncapitalize',function(el){
        var str = el.val();
        var words = str.split(" ");
        for(var i = 0; i < words.length; i++){
            var word = words[i];
            if (word.length > 1){
                word = word[0] + word.substr(1).toLowerCase();
                words[i] = word;
            }
        }

        el.val(words.join(" "));
    });

    function ValidateCPF(cpf) {
        cpf = cpf.replace(/[^\d]+/g,'');
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

    FormValidate.validatorAdd('cpf',function(el){
        var str = el.val();
        str = str.replace(/[^0-9]/gi, "");

        if (str.length != 11){
            return false;
        }

        if (str.length > 11){
            str = str.substr(0,11);
        }

        if (str.length == 11){
            str = str.substr(0,3) + "." + str.substr(3,3) + "." + str.substr(6,3) + "-" + str.substr(9,2);
        }else if (str.length == 14){
            str = str.substr(0,2) + "." + str.substr(2,3) + "." + str.substr(5,3) + "/" + str.substr(8,4) + "-" + str.substr(12,2);
        }

        el.val(str);

        return ValidateCPF(str);
    });

    FormValidate.validatorAdd('format-br-zip',function(el){
        NumbersOnly(el);
        var str = el.val();
        str = str.replace(/[^0-9]/gi, "");

        if (str.length > 8){
            str = str.substr(0,8);
        }
        if (str.length == 8){
            str = str.substr(0,5) + "-" + str.substr(5,3);
        }

        el.val(str);
    });


    FormValidate.validatorAdd('format-telephone',function(el){
        var original = el.val();

        NumbersOnly(el);
        var str = el.val();
        str = str.replace(/[^0-9]/gi, "");

        var optionalAreaCode = el.attr('optional-area-code') ? 1 : 0 ;
        var optionalCountryCode = el.attr('optional-country-code') ? 1 : 0 ;
        var noAreaCode = el.attr('no-area-code') ? 1 : 0 ;
        var noCountryCode = el.attr('no-country-code') ? 1 : 0 ;

        if (str.length == 8 && ((optionalAreaCode && optionalCountryCode) || noAreaCode)){
            str =  str.substr(0,4) + '-' + str.substr( 4, 4);
        } else if (str.length == 9 && ((optionalAreaCode && optionalCountryCode) || noAreaCode)){
            str =  str.substr(0,5) + '-' + str.substr( 5, 4);
        } else if (str.length == 10 && ((optionalCountryCode || noCountryCode) && !noAreaCode)){
            str =  '(' + str.substr(0,2) + ') ' + str.substr(2,4) + '-' + str.substr( 6, 4);
        } else if (str.length == 11 && ((optionalCountryCode || noCountryCode) && !noAreaCode)){
            str =  '(' + str.substr(0,2) + ') ' + str.substr(2,5) + '-' + str.substr( 7, 4);
        } else if (str.length == 12 && (!noCountryCode)){
            str =  '+' + str.substr(0,2) + ' (' + str.substr(2,2) + ') ' + str.substr(4,4) + '-' + str.substr( 8, 4);
        } else if (str.length == 13 && (!noCountryCode)){
            str =  '+' + str.substr(0,2) + ' (' + str.substr(2,2) + ') ' + str.substr(4,5) + '-' + str.substr( 9, 4);
        } else {
            el.val(original);
            return false;
        }

        el.val(str);

        return true;
    });


    FormValidate.validatorAdd('regex-validation',function(el){
        var original = el.val();

        var regex = el.attr('regex-validation');
        var regexFormat = el.attr('regex-format');

        regex = regex.split('/');
        if (regex.length == 1){
            regex = new RegExp(regex[0])
        }else if (regex.length == 3){
            regex = new RegExp(regex[1], regex[2]);
        }else{
            throw 'WTF IS THIS REGEX? '+regex.join("/");
        }
        
        var result = regex.test(original);
        
        if (result && regexFormat){
            var newStr = original.format(original.match(regex));
            el.val(newStr);
        }
        
        return result;
    });
})();