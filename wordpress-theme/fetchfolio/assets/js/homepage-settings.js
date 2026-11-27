(function ($) {
  'use strict';

  $(document).on('click', '.fetchfolio-select-icon', function (event) {
    event.preventDefault();

    var input = $(this).siblings('.fetchfolio-icon-url');
    var frame = wp.media({
      title: 'Choose an icon image',
      button: { text: 'Use this image' },
      library: { type: 'image' },
      multiple: false
    });

    frame.on('select', function () {
      var image = frame.state().get('selection').first().toJSON();
      input.val(image.url);
    });

    frame.open();
  });
}(jQuery));
