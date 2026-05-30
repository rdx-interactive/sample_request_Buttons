(function ($) {
	'use strict';

	$(document).on('click', '.saw-upload-pdf-button', function (event) {
		event.preventDefault();

		var $wrapper = $(this).closest('.saw-product-info-pdf-field');
		var frame = wp.media({
			title: sawAdmin.mediaTitle,
			button: {
				text: sawAdmin.mediaButton
			},
			library: {
				type: 'application/pdf'
			},
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();

			if (!attachment || attachment.mime !== 'application/pdf') {
				window.alert(sawAdmin.invalidPdf);
				return;
			}

			$wrapper.find('.saw-product-info-pdf-id').val(attachment.id);
			$wrapper.find('.saw-product-info-pdf-name').text(attachment.filename || attachment.title || attachment.url);
			$wrapper.find('.saw-remove-pdf-button').show();
		});

		frame.open();
	});

	$(document).on('click', '.saw-remove-pdf-button', function (event) {
		event.preventDefault();

		var $wrapper = $(this).closest('.saw-product-info-pdf-field');

		$wrapper.find('.saw-product-info-pdf-id').val('');
		$wrapper.find('.saw-product-info-pdf-name').text(sawAdmin.noPdfSelected);
		$(this).hide();
	});
})(jQuery);
