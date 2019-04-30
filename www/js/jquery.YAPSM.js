/*!
 * jQuery YAPSM Plugin v1.0.0
 *
 * Copyright 2011, 2013 Kjel Delaey
 * Released under the MIT license
 * https://raw.github.com/trimentor/YAPSM/master/LICENSE
 */
(function ($) {
    $.extend($.fn, {
        yapsm: function (options) {
            var passwordStrengthMeter, calculatePasswordComplexity;
            passwordStrengthMeter = new $.yapsm(options);

            calculatePasswordComplexity = function () {
                this.complexity = passwordStrengthMeter.strength(this.value);
            };
            $(this).keyup(calculatePasswordComplexity);
            return this;
        }
    });

    $.yapsm = function (options) {
        this.settings = $.extend({}, $.yapsm.defaults, options);
        this.entropyMap = $.extend({}, $.yapsm.entropyMap);
        this.field = $(this);
        this.init();
    };

    $.extend($.yapsm, {
        defaults: {
            fieldClass: 'yapsm',
            shortClass: 'příliš krátké heslo',//'too-short',
            weakClass: 'slabé heslo',
            fairClass: 'přijatelné heslo',
            goodClass: 'dobré heslo',
            strongClass: 'silné heslo',
            veryStrongClass: 'velmi silné heslo',
            commonWordClass: 'příliš běžné heslo'
        },

        entropyMap: {
            decimal: {
                count: 10,
                entropy: 3.32
            },
            hexadecimal: {
                count: 16,
                entropy: 4.00
            },
            mixedCaseLettersOnly: {
                count: 26,
                entropy: 4.70
            },
            mixedCaseLettersAndNumbers: {
                count: 36,
                entropy: 5.17
            },
            caseSensitiveLettersOnly: {
                count: 52,
                entropy: 5.70
            },
            caseSensitiveLettersAndNumbers: {
                count: 62,
                entropy: 5.95
            },
            otherCharacters: {
                count: 1,
                entropy: 6.55
            }
        },

        prototype: {
            init: function () {
                this.commonWords(this.settings.dictionary);
            },

            commonWords: function (dictionary) {
                var commonList;

                if (typeof dictionary === "function") {
                    commonList = dictionary();
                } else if (typeof dictionary === "object") {
                    commonList = dictionary;
                } else {
                    commonList = [];
                }
                this.dictionary = commonList;
            },

            strength: function (password) {
                var dictionaryWord, bits;
                dictionaryWord = this.commonWord(password);
                bits = !dictionaryWord ? this.passwordStrength(password) : null;
                return this.strengthClass(bits);
            },

            charSetEntropy: function (charSet) {
                return Math.log(charSet) / Math.log(2);
            },

            passwordStrength: function (password) {
                var charSet = this.numberOfPossibleSymbols(password);
                return this.charSetEntropy(charSet) * password.length;
            },

            commonWord: function (password) {
                var lPassword, dictionaryWord, i;
                lPassword = password.toLowerCase();
                dictionaryWord = false;

                for (i = 0; i < this.dictionary.length; i += 1) {
                    if (lPassword === this.dictionary[i].toLowerCase()) {
                        dictionaryWord = true;
                        break;
                    }
                }
                return dictionaryWord;
            },

            numberOfPossibleSymbols: function (password) {
                var character, score, inclDecimal, inclLowerCase, inclUpCase, inclOther, i;
                score = 0;

                for (i = 0; i < password.length; i += 1) {
                    character = password.charAt(i);

                    if (typeof inclDecimal === "undefined" && /[0-9]/.test(character)) {
                        inclDecimal = true;
                        score += this.entropyMap.decimal.count;
                    }
                    if (typeof inclUpCase === "undefined" && /[A-Z]/.test(character)) {
                        inclUpCase = true;
                        score += this.entropyMap.mixedCaseLettersOnly.count;
                    }
                    if (typeof inclLowerCase === "undefined" && /[a-z]/.test(character)) {
                        inclLowerCase = true;
                        score += this.entropyMap.mixedCaseLettersOnly.count;
                    }
                    if (typeof inclOther === "undefined" && "`~-_=+[{]}\\|;:'\",<.>/?!@#$%^&*()".indexOf(character) >= 0) {
                        inclOther = true;
                        score += this.entropyMap.otherCharacters.count;
                    }
                }
                return score;
            },

            strengthClass: function (bits) {
                var cssClass;

                if (bits === null) {
                    cssClass = this.settings.commonWordClass;
                } else {
                    if (bits >= 28 && bits <= 35) {
                        cssClass = this.settings.fairClass;
                    } else if (bits >= 36 && bits <= 59) {
                        cssClass = this.settings.goodClass;
                    } else if (bits >= 60 && bits <= 127) {
                        cssClass = this.settings.strongClass;
                    } else if (bits >= 128) {
                        cssClass = this.settings.veryStrongClass;
                    } else {
                        cssClass = this.settings.weakClass;
                    }
                }
                return cssClass;
            },

            passwordGuesses: function (bits, feelingLucky) {
                var nBits = !!feelingLucky ? bits - 1 : bits;
                return Math.pow(2, nBits);
            },

            timeToGuess: function (passwordGuesses) {
                var averagePassPerSec, seconds;
                averagePassPerSec = Math.pow(10, 9) * 3;
                seconds = Math.floor(passwordGuesses) / averagePassPerSec;
                return [seconds, seconds * 60, seconds * 3600];
            }
        }
    });
})(jQuery);
