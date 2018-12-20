jQuery(document).ready(function($) {

	// Show badge sharing options only if "Enable Badge Baking" is enabled
	$('#open_badge_enable_baking').change( function(){
		if ( '0' == $(this).val() )
			$('#open-badge-setting-section').hide();
		else
			$('#open-badge-setting-section').show();
    }).change();

    $('.date_picker_class').datepicker({
        dateFormat : 'yy-mm-dd'
    });
});