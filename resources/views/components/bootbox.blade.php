<script>
    (function (root, factory) {
        'use strict';
        if (typeof define === 'function' && define.amd) {
            define(['jquery'], factory);
        } else if (typeof exports === 'object') {
            module.exports = factory(require('jquery'));
        } else {
            root.bootbox = factory(root.jQuery);
        }
    }(this, function init($, undefined) {
        'use strict';

        let exports = {};

        let VERSION = '6.0.0';
        exports.VERSION = VERSION;

        let locales = {
            'en': {
                OK: '{{ __('survey::survey.ok') }}',
                CANCEL: '{{ __('survey::survey.cancel') }}',
                CONFIRM: '{{ __('survey::survey.ok') }}'
            }
        };

        let templates = {
            dialog: '<div class="bootbox modal" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="bootbox-body"></div></div></div></div></div>',
            header: '<div class="modal-header"><h5 class="modal-title"></h5></div>',
            footer: '<div class="modal-footer"></div>',
            closeButton: '<button type="button" class="bootbox-close-button close" aria-hidden="true" aria-label="Close"><span aria-hidden="true">×</span></button>',
            button: '<button type="button" class="btn"></button>',
            option: '<option value=""></option>',
        };


        let defaults = {
            backdrop: 'static',
            animate: true,
            className: null,
            closeButton: true,
            show: true,
            container: 'body',
            value: '',
            inputType: 'text',
            errorMessage: null,
            swapButtonOrder: false,
            centerVertical: false,
            multiple: false,
            scrollable: false,
            reusable: false,
            relatedTarget: null,
            size: null,
            id: null
        };


        exports.setDefaults = function () {
            let values = {};

            if (arguments.length === 2) {
                values[arguments[0]] = arguments[1];
            } else {
                values = arguments[0];
            }

            $.extend(defaults, values);

            return exports;
        };


        exports.hideAll = function () {
            $('.bootbox').modal('hide');

            return exports;
        };


        exports.init = function (_$) {
            return init(_$ || $);
        };


        exports.dialog = function (options) {
            if ($.fn.modal === undefined) {
                throw new Error(
                    '"$.fn.modal" is not defined; please double check you have included the Bootstrap JavaScript library. See https://getbootstrap.com/docs/5.1/getting-started/introduction/ for more details.'
                );
            }

            options = sanitize(options);

            if ($.fn.modal.Constructor.VERSION) {
                options.fullBootstrapVersion = $.fn.modal.Constructor.VERSION;
                let i = options.fullBootstrapVersion.indexOf('.');
                options.bootstrap = options.fullBootstrapVersion.substring(0, i);
            } else {
                options.bootstrap = '2';
                options.fullBootstrapVersion = '2.3.2';
                console.warn('Bootbox will *mostly* work with Bootstrap 2, but we do not officially support it. Please upgrade, if possible.');
            }

            let dialog = $(templates.dialog);
            let innerDialog = dialog.find('.modal-dialog');
            let body = dialog.find('.modal-body');
            let header = $(templates.header);
            let footer = $(templates.footer);
            let buttons = options.buttons;

            let callbacks = {
                onEscape: options.onEscape
            };

            body.find('.bootbox-body').html(options.message);

            if (getKeyLength(options.buttons) > 0) {
                each(buttons, function (key, b) {
                    let button = $(templates.button);
                    button.data('bb-handler', key);
                    button.addClass(b.className);

                    switch (key) {
                        case 'ok':
                        case 'confirm':
                            button.addClass('bootbox-accept');
                            break;

                        case 'cancel':
                            button.addClass('bootbox-cancel');
                            break;
                    }

                    button.html(b.label);

                    if (b.id) {
                        button.attr({'id': b.id});
                    }

                    if (b.disabled === true) {
                        button.prop({disabled: true});
                    }

                    footer.append(button);

                    callbacks[key] = b.callback;
                });

                body.after(footer);
            }

            if (options.animate === true) {
                dialog.addClass('fade');
            }

            if (options.className) {
                dialog.addClass(options.className);
            }

            if (options.id) {
                dialog.attr({'id': options.id});
            }

            if (options.size) {
                if (options.fullBootstrapVersion.substring(0, 3) < '3.1') {
                    console.warn('"size" requires Bootstrap 3.1.0 or higher. You appear to be using ' + options.fullBootstrapVersion + '. Please upgrade to use this option.');
                }

                switch (options.size) {
                    case 'small':
                    case 'sm':
                        innerDialog.addClass('modal-sm');
                        break;

                    case 'large':
                    case 'lg':
                        innerDialog.addClass('modal-lg');
                        break;

                    case 'extra-large':
                    case 'xl':
                        innerDialog.addClass('modal-xl');

                        if (options.fullBootstrapVersion.substring(0, 3) < '4.2') {
                            console.warn('Using size "xl"/"extra-large" requires Bootstrap 4.2.0 or higher. You appear to be using ' + options.fullBootstrapVersion + '. Please upgrade to use this option.');
                        }
                        break;
                }
            }

            if (options.scrollable) {
                innerDialog.addClass('modal-dialog-scrollable');

                if (options.fullBootstrapVersion.substring(0, 3) < '4.3') {
                    console.warn('Using "scrollable" requires Bootstrap 4.3.0 or higher. You appear to be using ' + options.fullBootstrapVersion + '. Please upgrade to use this option.');
                }
            }

            if (options.title || options.closeButton) {
                if (options.title) {
                    header.find('.modal-title').html(options.title);
                } else {
                    header.addClass('border-0');
                }

                if (options.closeButton) {
                    let closeButton = $(templates.closeButton);
                    if (options.bootstrap < 5) {
                        closeButton.html('&times;');
                    }

                    if (options.bootstrap < 4) {
                        header.prepend(closeButton);
                    } else {
                        header.append(closeButton);
                    }
                }

                body.before(header);
            }

            if (options.centerVertical) {
                innerDialog.addClass('modal-dialog-centered');

                if (options.fullBootstrapVersion < '4.0.0') {
                    console.warn('"centerVertical" requires Bootstrap 4.0.0-beta.3 or higher. You appear to be using ' + options.fullBootstrapVersion + '. Please upgrade to use this option.');
                }
            }

            if (!options.reusable) {
                dialog.one('hide.bs.modal', {dialog: dialog}, unbindModal);
                dialog.one('hidden.bs.modal', {dialog: dialog}, destroyModal);
            }

            if (options.onHide) {
                if ($.isFunction(options.onHide)) {
                    dialog.on('hide.bs.modal', options.onHide);
                } else {
                    throw new Error('Argument supplied to "onHide" must be a function');
                }
            }

            if (options.onHidden) {
                if ($.isFunction(options.onHidden)) {
                    dialog.on('hidden.bs.modal', options.onHidden);
                } else {
                    throw new Error('Argument supplied to "onHidden" must be a function');
                }
            }

            if (options.onShow) {
                if ($.isFunction(options.onShow)) {
                    dialog.on('show.bs.modal', options.onShow);
                } else {
                    throw new Error('Argument supplied to "onShow" must be a function');
                }
            }

            dialog.one('shown.bs.modal', {dialog: dialog}, focusPrimaryButton);

            if (options.onShown) {
                if ($.isFunction(options.onShown)) {
                    dialog.on('shown.bs.modal', options.onShown);
                } else {
                    throw new Error('Argument supplied to "onShown" must be a function');
                }
            }

            if (options.backdrop === true) {
                let startedOnBody = false;

                dialog.on('mousedown', '.modal-content', function (e) {
                    e.stopPropagation();

                    startedOnBody = true;
                });

                dialog.on('click.dismiss.bs.modal', function (e) {
                    if (startedOnBody || e.target !== e.currentTarget) {
                        return;
                    }

                    dialog.trigger('escape.close.bb');
                });
            }

            dialog.on('escape.close.bb', function (e) {
                if (callbacks.onEscape) {
                    processCallback(e, dialog, callbacks.onEscape);
                }
            });

            dialog.on('click', '.modal-footer button:not(.disabled)', function (e) {
                let callbackKey = $(this).data('bb-handler');

                if (callbackKey !== undefined) {
                    processCallback(e, dialog, callbacks[callbackKey]);
                }
            });

            dialog.on('click', '.bootbox-close-button', function (e) {
                processCallback(e, dialog, callbacks.onEscape);
            });

            dialog.on('keyup', function (e) {
                if (e.which === 27) {
                    dialog.trigger('escape.close.bb');
                }
            });

            $(options.container).append(dialog);

            dialog.modal({
                backdrop: options.backdrop,
                keyboard: false,
                show: false
            });

            if (options.show) {
                dialog.modal('show', options.relatedTarget);
            }

            return dialog;
        };


        exports.alert = function () {
            let options;

            options = mergeDialogOptions('alert', ['ok'], ['message', 'callback'], arguments);

            if (options.callback && !$.isFunction(options.callback)) {
                throw new Error('alert requires the "callback" property to be a function when provided');
            }

            options.buttons.ok.callback = options.onEscape = function () {
                if ($.isFunction(options.callback)) {
                    return options.callback.call(this);
                }

                return true;
            };

            return exports.dialog(options);
        };


        exports.confirm = function () {
            let options;

            options = mergeDialogOptions('confirm', ['cancel', 'confirm'], ['message', 'callback'], arguments);

            if (!$.isFunction(options.callback)) {
                throw new Error('confirm requires a callback');
            }

            options.buttons.cancel.callback = options.onEscape = function () {
                return options.callback.call(this, false);
            };

            options.buttons.confirm.callback = function () {
                return options.callback.call(this, true);
            };

            if (typeof options.message === 'object') {
                options.size = options.message.size ?? 'sm';
                options.title = options.message.title;
                options.message = options.message.message;
            }

            return exports.dialog(options);
        };

        function mapArguments(args, properties) {
            let argsLength = args.length;
            let options = {};

            if (argsLength < 1 || argsLength > 2) {
                throw new Error('Invalid argument length');
            }

            if (argsLength === 2 || typeof args[0] === 'string') {
                options[properties[0]] = args[0];
                options[properties[1]] = args[1];
            } else {
                options = args[0];
            }

            return options;
        }


        function mergeArguments(defaults, args, properties) {
            return $.extend(
                true,
                {},
                defaults,
                mapArguments(args, properties)
            );
        }


        function mergeDialogOptions(className, labels, properties, args) {
            let locale;
            if (args && args[0]) {
                locale = args[0].locale || defaults.locale;
                let swapButtons = args[0].swapButtonOrder || defaults.swapButtonOrder;

                if (swapButtons) {
                    labels = labels.reverse();
                }
            }

            let baseOptions = {
                className: 'bootbox-' + className,
                buttons: args[0].buttons || createLabels(labels, locale)
            };

            return validateButtons(
                mergeArguments(
                    baseOptions,
                    args,
                    properties
                ),
                labels
            );
        }


        function validateButtons(options, buttons) {
            let allowedButtons = {};
            each(buttons, function (key, value) {
                allowedButtons[value] = true;
            });

            each(options.buttons, function (key) {
                if (allowedButtons[key] === undefined) {
                    throw new Error('button key "' + key + '" is not allowed (options are ' + buttons.join(' ') + ')');
                }
            });

            return options;
        }


        function createLabels(labels, locale) {
            let buttons = {};

            for (let i = 0, j = labels.length; i < j; i++) {
                let argument = labels[i];
                let key = argument.toLowerCase();
                let value = argument.toUpperCase();

                buttons[key] = {
                    label: getText(value, locale)
                };
            }

            return buttons;
        }


        function getText(key, locale) {
            let labels = locales[locale];

            return labels ? labels[key] : locales.en[key];
        }


        function sanitize(options) {
            let buttons;
            let total;

            if (typeof options !== 'object') {
                throw new Error('Please supply an object of options');
            }

            if (!options.message) {
                throw new Error('"message" option must not be null or an empty string.');
            }

            options = $.extend({}, defaults, options);

            if (!options.backdrop) {
                options.backdrop = (options.backdrop === false || options.backdrop === 0) ? false : 'static';
            } else {
                options.backdrop = typeof options.backdrop === 'string' && options.backdrop.toLowerCase() === 'static' ? 'static' : true;
            }

            if (!options.buttons) {
                options.buttons = {};
            }

            buttons = options.buttons;

            total = getKeyLength(buttons);

            each(buttons, function (key, button, index) {
                if ($.isFunction(button)) {
                    button = buttons[key] = {
                        callback: button
                    };
                }

                if ($.type(button) !== 'object') {
                    throw new Error('button with key "' + key + '" must be an object');
                }

                if (!button.label) {
                    button.label = key;
                }

                if (!button.className) {
                    let isPrimary = false;
                    if (options.swapButtonOrder) {
                        isPrimary = index === 0;
                    } else {
                        isPrimary = index === total - 1;
                    }

                    if (total <= 2 && isPrimary) {
                        button.className = 'btn-primary';
                    } else {
                        button.className = 'btn-secondary btn-default';
                    }
                }
            });

            return options;
        }


        function getKeyLength(obj) {
            return Object.keys(obj).length;
        }


        function each(collection, iterator) {
            let index = 0;
            $.each(collection, function (key, value) {
                iterator(key, value, index++);
            });
        }


        function focusPrimaryButton(e) {
            e.data.dialog.find('.bootbox-accept').first().trigger('focus');
        }


        function destroyModal(e) {
            if (e.target === e.data.dialog[0]) {
                e.data.dialog.remove();
            }
        }


        function unbindModal(e) {
            if (e.target === e.data.dialog[0]) {
                e.data.dialog.off('escape.close.bb');
                e.data.dialog.off('click');
            }
        }


        function processCallback(e, dialog, callback) {
            e.stopPropagation();
            e.preventDefault();

            let preserveDialog = $.isFunction(callback) && callback.call(dialog, e) === false;

            if (!preserveDialog) {
                dialog.modal('hide');
            }
        }

        return exports;
    }));
</script>
