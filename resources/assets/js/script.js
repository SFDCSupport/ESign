/*
We want to preview images, so we need to register the Image Preview plugin
*/
FilePond.registerPlugin(
	
	// encodes the file as base64 data
  FilePondPluginFileEncode,
	
	// validates the size of the file
	FilePondPluginFileValidateSize,
	
	// corrects mobile image orientation
	FilePondPluginImageExifOrientation,
	
	// previews dropped images
  FilePondPluginImagePreview
);

// Select the file input and use create() to turn it into a pond
FilePond.create(
	document.querySelector('input[type="file"]')
);


var dragTimer;
$(document).on('dragover', function(e) {
  var dt = e.originalEvent.dataTransfer;
  if (dt.types && (dt.types.indexOf ? dt.types.indexOf('Files') != -1 : dt.types.contains('Files'))) {
    $("#dropzone").show();
    window.clearTimeout(dragTimer);
  }
});
$(document).on('dragleave', function(e) {
  dragTimer = window.setTimeout(function() {
    $("#dropzone").hide();
  }, 25);
});

var sel = $('.sel'),
    txt = $('.txt'),
    options = $('.options');

sel.click(function (e) {
    e.stopPropagation();
    options.show();
});


options.children('div').click(function (e) {
    e.stopPropagation();
    txt.text($(this).text());
    $(this).addClass('selected').siblings('div').removeClass('selected');
    options.hide();
});