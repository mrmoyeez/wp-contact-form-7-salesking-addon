jQuery(document).ready(function() {

    var fields = ['sk_password', 'sk_username', 'sk_url'];

    jQuery('#wpcf7-sk-test-credentials').on('click', function(e){
      e.preventDefault();
      $('.sk-errors').remove();
      // collect post data by form field name .. ids will change
      var data = {};
      jQuery.each(fields, function(i, field_name) {
        data[field_name] = jQuery('input[name*="'+field_name+'"]').val();
        //reset css
        jQuery('input[name*="'+field_name+'"]').css("background-color", 'none');
      });

      jQuery.ajax({
        url: jQuery(this).attr('data-url'),
        data: data,
        success: function(data, status, xhr){  //String textStatus, jqXHR jqXHR
            //insert result below btn
          var color = (data['errors'].length>0) ? '#F5AAAA' : '#A8F794' ;  //red / green
          jQuery.each(fields, function(i, field_name) {
            jQuery('input[name*="'+field_name+'"]').css("background-color", color);
          });
          if(data['errors'].length>0){
            $('#wpcf7-sk-test-credentials').after('<div class="sk-errors">'+data['errors']+'</div>');
          }
        },
        dataType: 'json'
      });
    });


	try {
		if (! jQuery('#wpcf7-sk-active').is(':checked'))
			jQuery('#cf7skdiv .mail-fields').hide();

      jQuery('#wpcf7-sk-active').click(function() {
			if (jQuery('#cf7skdiv .mail-fields').is(':hidden')
			&& jQuery('#wpcf7-sk-active').is(':checked')) {
				jQuery('#cf7skdiv .mail-fields').slideDown('fast');
				if (jQuery('.salesking-custom-fields').is(':hidden')
				&& jQuery('#wpcf7-sk-cf-active').is(':checked')) {
					jQuery('.salesking-custom-fields').slideDown('fast');
				}
			} else if (jQuery('#cf7skdiv .mail-fields').is(':visible')
			&& jQuery('#wpcf7-sk-active').not(':checked')) {
				jQuery('#cf7skdiv .mail-fields').slideUp('fast');
			}
		});
		
		if (! jQuery('#wpcf7-sk-cf-active').is(':checked'))
			jQuery('.salesking-custom-fields').hide();

		jQuery('#wpcf7-sk-cf-active').click(function() {
			if (jQuery('.salesking-custom-fields').is(':hidden')
			&& jQuery('#wpcf7-sk-cf-active').is(':checked')) {
				jQuery('.salesking-custom-fields').slideDown('fast');
			} else if (jQuery('.salesking-custom-fields').is(':visible')
			&& jQuery('#wpcf7-sk-cf-active').not(':checked')) {
				jQuery('.salesking-custom-fields').slideUp('fast');
			}
		});

	} catch (e) {
	}
});