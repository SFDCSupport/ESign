$(() => {
    $(document).on('click', '[data-toggle="section"]', function(){
        let target = $(this).data('target');
        $(target).toggleClass('d-none');
    })
});
