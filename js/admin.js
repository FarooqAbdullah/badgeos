jQuery(document).ready(function($) {

	// Dynamically show/hide achievement meta inputs based on "Award By" selection
	$("#_badgeos_earned_by").change( function() {

		// Define our potentially unnecessary inputs
		var badgeos_sequential = $('#_badgeos_sequential').parent().parent();
		var badgeos_points_required = $('#_badgeos_points_required').parent().parent();

		// // Hide our potentially unnecessary inputs
		badgeos_sequential.hide();
		badgeos_points_required.hide();

		// Determine which inputs we should show
		if ( 'triggers' == $(this).val() )
			badgeos_sequential.show();
		else if ( 'points' == $(this).val() )
			badgeos_points_required.show();

	}).change();

	// Throw a warning on Achievement Type editor if title is > 20 characters
	$('#titlewrap').on( 'keyup', 'input[name=post_title]', function() {

		// Make sure we're editing an achievement type
		if ( 'achievement-type' == $('#post_type').val() ) {
			// Cache the title input selector
			var $title = $(this);
			if ( $title.val().length > 20 ) {
				// Set input to look like danger
				$title.css({'background':'#faa', 'color':'#a00', 'border-color':'#a55' });

				// Output a custom warning (and delete any existing version of that warning)
				$('#title-warning').remove();
				$title.parent().append('<p id="title-warning">Achievement Type supports a maximum of 20 characters. Please choose a shorter title.</p>');
			} else {
				// Set the input to standard style, hide our custom warning
				$title.css({'background':'#fff', 'color':'#333', 'border-color':'#DFDFDF'});
				$('#title-warning').remove();
			}
		}
	} );

	$( '#delete_log_entries' ).click( function() {
		var confirmation = confirm( 'It will delete all the log entries' );
		if( confirmation ) {
            var data = {
                'action': 'delete_badgeos_log_entries'
            };
            $.post( admin_js.ajax_url, data, function(response) {
                $( '#wpbody-content .wrap' ).prepend( '<div class="notice notice-warning delete-log-entries"><p><img src="'+ admin_js.loading_img +'" /> &nbsp;&nbsp;BadgeOS is deleting log entries as background process, you can continue exploring badgeos</p></div>' );

                setTimeout( function() {
                	$( '#wpbody-content .wrap .delete-log-entries' ).slideUp();
				}, 10000 );
            } );
		}
	});
});
