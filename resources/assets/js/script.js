$(() => {
    $(document).on('click', '[data-toggle="section"]', function(){
        let target = $(this).attr('data-target');
        $(target).toggleClass('d-none');
    });
});

$(document).on("click", "#expand-form-button", () => {
    $('#form-container').removeClass('d-none');
    $(this).addClass('d-none');
});

$(document).on("click", "#minimize-form-button", () => {
    $('#form-container').addClass('d-none');
    $('#expand-form-button').removeClass('d-none');
});
