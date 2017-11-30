/* Keys global object script
 *
 * Description: Keys global object script
 * Version: 0.1
 * Author: Daniel Andrade
 *
 * This code may not be reused without proper permission from its creator.
 */

if (isModule()){
    var Emitter = require('./General').Emitter;
}

/**
 * Maps most commonly used key codes to a friendly object
 * @type {Object}
 */
var Keys = {
    Any: -1,
    Backspace: 8,
    Tab: 9,
    Enter: 13,
    Shift: 16,
    Ctrl: 17,
    Alt: 18,
    PauseBreak: 19,
    CapsLock: 20,
    Esc: 27,
    Space: 32,
    PageUp: 33,
    PageDown: 34,
    End: 35,
    Home: 36,
    ArrowLeft: 37,
    ArrowUp: 38,
    ArrowRight: 39,
    ArrowDown: 40,
    Insert: 45,
    Delete: 46,
    Key0: 48,
    Key1: 49,
    Key2: 50,
    Key3: 51,
    Key4: 52,
    Key5: 53,
    Key6: 54,
    Key7: 55,
    Key8: 56,
    Key9: 57,
    KeyMinus: 189,
    KeyPlus: 187,
    A: 65,
    B: 66,
    C: 67,
    D: 68,
    E: 69,
    F: 70,
    G: 71,
    H: 72,
    I: 73,
    J: 74,
    K: 75,
    L: 76,
    M: 77,
    N: 78,
    O: 79,
    P: 80,
    Q: 81,
    R: 82,
    S: 83,
    T: 84,
    U: 85,
    V: 86,
    W: 87,
    X: 88,
    Y: 89,
    Z: 90,
    NumPad0: 96,
    NumPad1: 97,
    NumPad2: 98,
    NumPad3: 99,
    NumPad4: 100,
    NumPad5: 101,
    NumPad6: 102,
    NumPad7: 103,
    NumPad8: 104,
    NumPad9: 105,
    F1: 112,
    F2: 113,
    F3: 114,
    F4: 115,
    F5: 116,
    F6: 117,
    F7: 118,
    F8: 119,
    F9: 120,
    F10: 121,
    F11: 122,
    F12: 123,
    Comma: 188,
    Period: 190,
    Semicolon: 191,
    Apostrophe: 192,
    QuestionMark: 193
};

var KeyNames = [];
for(var key in Keys){
    KeyNames[Keys[key]] = key;
}

function KeyName(key){
    if (KeyNames[key]){
        return KeyNames[key];
    }
    return key;
}


var Keyboard = new Emitter();

Keyboard.on('keydown', function (key) {
    Keyboard[key] = true;
});
Keyboard.on('keyup', function (key) {
    Keyboard[key] = false;
});

Keyboard.Clear = function(){
    for (var i in Keys){
        if (Keys.hasOwnProperty(i)){
            if (Keyboard[Keys[i]]){
                Keyboard.emit('keyup', [Keys[i]]);
            }
        }
    }
};

window.onkeydown = function (e){ Keyboard.emit('keydown', [e.which, e]); };
window.onkeyup = function (e){ Keyboard.emit('keyup', [e.which, e]); };
window.onblur = function () { Keyboard.Clear(); };

Keyboard.keys = Keys;
Keyboard.keyNames = KeyNames;
Keyboard.getKeyName = function (key){
    if (KeyNames[key]){
        return KeyNames[key];
    }
    return key;
};

if (isModule()){
    module.exports = Keyboard;
}