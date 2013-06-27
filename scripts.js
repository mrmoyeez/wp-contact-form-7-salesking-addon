jQuery(document).ready(function() {
	try {
		if (! jQuery('#wpcf7-salesking-active').is(':checked'))
			jQuery('#cf7skdiv .mail-fields').hide();

            jQuery('#wpcf7-salesking-active').click(function() {
			if (jQuery('#cf7skdiv .mail-fields').is(':hidden')
			&& jQuery('#wpcf7-salesking-active').is(':checked')) {
				jQuery('#cf7skdiv .mail-fields').slideDown('fast');
				if (jQuery('.salesking-custom-fields').is(':hidden')
				&& jQuery('#wpcf7-salesking-cf-active').is(':checked')) {
					jQuery('.salesking-custom-fields').slideDown('fast');
				}
			} else if (jQuery('#cf7skdiv .mail-fields').is(':visible')
			&& jQuery('#wpcf7-salesking-active').not(':checked')) {
				jQuery('#cf7skdiv .mail-fields').slideUp('fast');
			}
		});
		
		if (! jQuery('#wpcf7-salesking-cf-active').is(':checked'))
			jQuery('.salesking-custom-fields').hide();

		jQuery('#wpcf7-salesking-cf-active').click(function() {
			if (jQuery('.salesking-custom-fields').is(':hidden')
			&& jQuery('#wpcf7-salesking-cf-active').is(':checked')) {
				jQuery('.salesking-custom-fields').slideDown('fast');
			} else if (jQuery('.salesking-custom-fields').is(':visible')
			&& jQuery('#wpcf7-salesking-cf-active').not(':checked')) {
				jQuery('.salesking-custom-fields').slideUp('fast');
			}
		});

	} catch (e) {
	}
});