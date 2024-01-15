$(() => {
    $(".dropdown_click .selecteddropdown").on("click", function() {
        $(".dropdown_click .drop-content ul").slideToggle();
    });

    $(".dropdown_click .drop-content ul li a").on("click", function() {
        $(".dropdown_click .selecteddropdown  span").html($(this).html());
        $(".dropdown_click .drop-content ul").slideUp(100);
    });
});
