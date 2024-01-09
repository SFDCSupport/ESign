import $ from 'jquery';
import * as Popper from '@popperjs/core';
import * as bootstrap from 'bootstrap'
import './common';

window.jQuery = window.$ = $;
window.Popper = Popper;

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': _().getCSRFToken(),
    },
});

export function _() {
    return {
        $: $,
        popper: Popper,
        bootstrap: bootstrap,
        getCSRFToken: function () {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        },
    };
}
