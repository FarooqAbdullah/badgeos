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

	$('#open-badgeos-verification').on('click', function(){
		var achievement_id = $(this).data('bg');
		var entry_id = $(this).data('eid');
		var user_id = $(this).data('uid');

		//alid data format
//✔ Badge is not revoked
//✔ Badge does not expire
		tb_show( 'Verification', '#TB_inline?width=200&height=200&inlineId=open-badge-id' );
		$.ajax({
            url: badgeos_vars.assertion_url,
            type: 'POST',
            data: {
				action: 'badgeos_validate_open_badge',
				bg: achievement_id,
				eid: entry_id,
				uid: user_id,
			},
            dataType: 'json',
            success: function (returndata) {
				$.ajax({
					url: badgeos_vars.ajax_url,
					type: 'POST',
					data: {
						action: 'badgeos_validate_open_badge',
						bg: achievement_id,
						eid: entry_id,
						uid: user_id,
					},
					dataType: 'json',
					success: function (returndata) {
						$.ajax({
							url: badgeos_vars.ajax_url,
							type: 'POST',
							data: {
								action: 'badgeos_validate_open_badge',
								bg: achievement_id,
								eid: entry_id,
								uid: user_id,
							},
							dataType: 'json',
							success: function (returndata) {
								alert(badgeos_vars.ajax_url);
							}
						});
					}
				});
			}
		});

	});
});