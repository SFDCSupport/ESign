//dropdown on  click //
$(".dropdown_click .selecteddropdown").on('click', function() {
  $(".dropdown_click .drop-content ul").slideToggle();
});

$(".dropdown_click .drop-content ul li a").on('click', function() {
  // var bindText = $(this).html();
  $(".dropdown_click .selecteddropdown  span").html($(this).html());
  $(".dropdown_click .drop-content ul").slideUp();
}); 


$(document).bind('click', function(e) {
  var $clickhide = $(e.target);
  if (! $clickhide.parents().hasClass("dropdown_c"))
      $(".dropdown_c .drop-content ul").slideUp('1000');
});