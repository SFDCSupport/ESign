import {_} from './../_';

const $ = _().$;

$(document).on('click', '#createDocumentBtn', function(){
    const form = $('#createDocumentForm');

    $.post(
        form.attr('action'),
        form.serialize()
    ).done(function(r) {
        if(r.redirect) {
            window.location.assign(r.redirect);
        }
    }).fail(function(x) {

    });
});
