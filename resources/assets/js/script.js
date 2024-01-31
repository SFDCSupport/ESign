$(() => {
    $(document).on('click', '[data-toggle="section"]', function(){
        let target = $(this).attr('data-target');
        $(target).toggleClass('d-none');
    });
});
