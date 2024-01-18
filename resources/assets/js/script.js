$(() => {
    $(document).on("click", ".dropdown_click .selecteddropdown", function() {
        $(".dropdown_click .drop-content ul").slideToggle();
    }).on("click", ".dropdown_click .drop-content ul li a", function() {
        $(".dropdown_click .selecteddropdown  span").html($(this).html());
        $(".dropdown_click .drop-content ul").slideUp(100);
    });
});
