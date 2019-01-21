<?php
/**
 * Admin Settings Pages
 *
 * @package BadgeOS
 * @subpackage Admin
 * @author LearningTimes, LLC
 * @license http://www.gnu.org/licenses/agpl.txt GNU AGPL v3.0
 */

/**
 * Register BadgeOS Settings with Settings API.
 * @return void
 */
function badgeos_register_settings() {
	register_setting( 'badgeos_settings_group', 'badgeos_settings', 'badgeos_settings_validate' );
}
add_action( 'admin_init', 'badgeos_register_settings' );

/**
 * Grant BadgeOS manager role ability to edit BadgeOS settings.
 *
 * @since  1.4.0
 *
 * @param  string $capability Required capability.
 * @return string             Required capability.
 */
function badgeos_edit_settings_capability( $capability ) {
	return badgeos_get_manager_capability();
}
add_filter( 'option_page_capability_badgeos_settings_group', 'badgeos_edit_settings_capability' );

/**
 * BadgeOS Settings validation
 *
 * @param  string $input The input we want to validate
 * @return string        Our sanitized input
 */
function badgeos_settings_validate( $input = '' ) {

	// Fetch existing settings
	$original_settings = get_option( 'badgeos_settings' );

	// Sanitize the settings data submitted
	$input['minimum_role'] = isset( $input['minimum_role'] ) ? sanitize_text_field( $input['minimum_role'] ) : $original_settings['minimum_role'];
	$input['submission_manager_role'] = isset( $input['submission_manager_role'] ) ? sanitize_text_field( $input['submission_manager_role'] ) : $original_settings['submission_manager_role'];
	$input['debug_mode'] = isset( $input['debug_mode'] ) ? sanitize_text_field( $input['debug_mode'] ) : $original_settings['debug_mode'];
	$input['log_entries'] = isset( $input['log_entries'] ) ? sanitize_text_field( $input['log_entries'] ) : $original_settings['log_entries'];
	$input['ms_show_all_achievements'] = isset( $input['ms_show_all_achievements'] ) ? sanitize_text_field( $input['ms_show_all_achievements'] ) : $original_settings['ms_show_all_achievements'];
	$input['remove_data_on_uninstall'] = ( isset( $input['remove_data_on_uninstall'] ) && "on" == $input['remove_data_on_uninstall'] ) ? "on" : null;

	// Allow add-on settings to be sanitized
	do_action( 'badgeos_settings_validate', $input );

	// Return sanitized inputs
	return $input;

}

/**
 * BadgeOS main settings page output
 * @since  1.0.0
 * @return void
 */
function badgeos_settings_page() {
	?>
	<div class="wrap" >
        <?php settings_errors(); ?>
		<div id="icon-options-general" class="icon32"></div>
		<h2><?php _e( 'BadgeOS Settings', 'badgeos' ); ?></h2>

		<form method="post" action="options.php">
			<?php settings_fields( 'badgeos_settings_group' ); ?>
			<?php $badgeos_settings = get_option( 'badgeos_settings' ); ?>
			<?php
			//load settings
			$minimum_role = ( isset( $badgeos_settings['minimum_role'] ) ) ? $badgeos_settings['minimum_role'] : 'manage_options';
			$submission_manager_role = ( isset( $badgeos_settings['submission_manager_role'] ) ) ? $badgeos_settings['submission_manager_role'] : 'manage_options';
			$submission_email = ( isset( $badgeos_settings['submission_email'] ) ) ? $badgeos_settings['submission_email'] : '';
			$submission_email_addresses = ( isset( $badgeos_settings['submission_email_addresses'] ) ) ? $badgeos_settings['submission_email_addresses'] : '';
			$debug_mode = ( isset( $badgeos_settings['debug_mode'] ) ) ? $badgeos_settings['debug_mode'] : 'disabled';
			$log_entries = ( isset( $badgeos_settings['log_entries'] ) ) ? $badgeos_settings['log_entries'] : 'disabled';
			$ms_show_all_achievements = ( isset( $badgeos_settings['ms_show_all_achievements'] ) ) ? $badgeos_settings['ms_show_all_achievements'] : 'disabled';
			$remove_data_on_uninstall = ( isset( $badgeos_settings['remove_data_on_uninstall'] ) ) ? $badgeos_settings['remove_data_on_uninstall'] : '';
			$congrat_email_subject = ( isset( $badgeos_settings['congrat_email_subject'] ) ) ? $badgeos_settings['congrat_email_subject'] : '';
			$congrat_email_body = ( isset( $badgeos_settings['congrat_email_body'] ) ) ? $badgeos_settings['congrat_email_body'] : '';
			wp_nonce_field( 'badgeos_settings_nonce', 'badgeos_settings_nonce' );
			?>
			<table class="form-table">
				<?php if ( current_user_can( 'manage_options' ) ) { ?>
					<tr valign="top"><th scope="row"><label for="minimum_role"><?php _e( 'Minimum Role to Administer BadgeOS plugin: ', 'badgeos' ); ?></label></th>
						<td>
							<select id="minimum_role" name="badgeos_settings[minimum_role]">
								<option value="manage_options" <?php selected( $minimum_role, 'manage_options' ); ?>><?php _e( 'Administrator', 'badgeos' ); ?></option>
								<option value="delete_others_posts" <?php selected( $minimum_role, 'delete_others_posts' ); ?>><?php _e( 'Editor', 'badgeos' ); ?></option>
								<option value="publish_posts" <?php selected( $minimum_role, 'publish_posts' ); ?>><?php _e( 'Author', 'badgeos' ); ?></option>
							</select>
						</td>
					</tr>
					<tr valign="top"><th scope="row"><label for="submission_manager_role"><?php _e( 'Minimum Role to Administer Submissions/Nominations: ', 'badgeos' ); ?></label></th>
						<td>
							<select id="submission_manager_role" name="badgeos_settings[submission_manager_role]">
								<option value="manage_options" <?php selected( $submission_manager_role, 'manage_options' ); ?>><?php _e( 'Administrator', 'badgeos' ); ?></option>
								<option value="delete_others_posts" <?php selected( $submission_manager_role, 'delete_others_posts' ); ?>><?php _e( 'Editor', 'badgeos' ); ?></option>
								<option value="publish_posts" <?php selected( $submission_manager_role, 'publish_posts' ); ?>><?php _e( 'Author', 'badgeos' ); ?></option>
							</select>
						</td>
					</tr>
				<?php } /* endif current_user_can( 'manage_options' ); */ ?>
				<tr valign="top"><th scope="row"><label for="submission_email"><?php _e( 'Send email when submissions/nominations are received:', 'badgeos' ); ?></label></th>
					<td>
						<select id="submission_email" name="badgeos_settings[submission_email]">
							<option value="enabled" <?php selected( $submission_email, 'enabled' ); ?>><?php _e( 'Enabled', 'badgeos' ) ?></option>
							<option value="disabled" <?php selected( $submission_email, 'disabled' ); ?>><?php _e( 'Disabled', 'badgeos' ) ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top"><th scope="row"><label for="submission_email_addresses"><?php _e( 'Notification email addresses:', 'badgeos' ); ?></label></th>
					<td>
						<input id="submission_email_addresses" name="badgeos_settings[submission_email_addresses]" type="text" value="<?php echo esc_attr( $submission_email_addresses ); ?>" class="regular-text" />
						<p class="description"><?php _e( 'Comma-separated list of email addresses to send submission/nomination notifications, in addition to the Site Admin email.', 'badgeos' ); ?></p>
					</td>
				</tr>
				<tr valign="top"><th scope="row"><label for="remove_data_on_uninstall"><?php _e( 'Delete Data on Uninstall:', 'badgeos' ); ?></label></th>
					<td>
						<input id="remove_data_on_uninstall" name="badgeos_settings[remove_data_on_uninstall]" type="checkbox" <?php echo ( $remove_data_on_uninstall == "on" ) ? "checked" : ""; ?> class="regular-text" />
						<p class="description"><?php _e( 'It will delete all BadgeOS DB entries on uninstall including posts, setting options, usermeta', 'badgeos' ); ?></p>
					</td>
				</tr>
				<tr valign="top"><th scope="row"><label for="debug_mode"><?php _e( 'Debug Mode:', 'badgeos' ); ?></label></th>
					<td>
						<select id="debug_mode" name="badgeos_settings[debug_mode]">
							<option value="disabled" <?php selected( $debug_mode, 'disabled' ); ?>><?php _e( 'Disabled', 'badgeos' ) ?></option>
							<option value="enabled" <?php selected( $debug_mode, 'enabled' ); ?>><?php _e( 'Enabled', 'badgeos' ) ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top"><th scope="row"><label for="log_entries"><?php _e( 'Log Entries:', 'badgeos' ); ?></label></th>
					<td>
						<select id="log_entries" name="badgeos_settings[log_entries]">
							<option value="disabled" <?php selected( $log_entries, 'disabled' ); ?>><?php _e( 'Disabled', 'badgeos' ) ?></option>
							<option value="enabled" <?php selected( $log_entries, 'enabled' ); ?>><?php _e( 'Enabled', 'badgeos' ) ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="congrat_email_subject"><?php _e( 'Congratulation Email Subject:', 'badgeos' ); ?></label></th>
					<td>
						<input type="text" maxlength="200" class="regular-text" id="congrat_email_subject" name="badgeos_settings[congrat_email_subject]" value="<?php echo $congrat_email_subject; ?>" />
						<p>
							<b>Shortcodes:</b> [achievement_type], [achievement_title], [points], [user_email], [user_name]
						</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="congrat_email_body"><?php _e( 'Congratulation Email Body:', 'badgeos' ); ?></label></th>
					<td>
						<?php wp_editor( $congrat_email_body , 'badgeos_settings[congrat_email_body]', array('media_buttons' => false,'textarea_rows' => 5) ); ?>
						<p>
							<b>Shortcodes:</b> [achievement_type], [achievement_title], [points], [user_email], [user_name]
						</p>
					</td>
				</tr>
				<?php
				// check if multisite is enabled & if plugin is network activated
				if ( is_super_admin() ){
					if ( is_multisite() ) {
					?>
						<tr valign="top"><th scope="row"><label for="debug_mode"><?php _e( 'Show achievements earned across all sites on the network:', 'badgeos' ); ?></label></th>
							<td>
								<select id="debug_mode" name="badgeos_settings[ms_show_all_achievements]">
									<option value="disabled" <?php selected( $ms_show_all_achievements, 'disabled' ); ?>><?php _e( 'Disabled', 'badgeos' ) ?></option>
									<option value="enabled" <?php selected( $ms_show_all_achievements, 'enabled' ); ?>><?php _e( 'Enabled', 'badgeos' ) ?></option>
								</select>
							</td>
						</tr>
					<?php
					}
				}
				do_action( 'badgeos_settings', $badgeos_settings ); ?>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Settings', 'badgeos' ); ?>" />
			</p>
			<!-- TODO: Add settings to select WP page for archives of each achievement type.
				See BuddyPress' implementation of this idea.  -->
		</form>
	</div>
	<?php
}


/**
 * Adds additional options to the BadgeOS Settings page
 *
 * @since 1.0.0
 */
function badgeos_license_settings() {

	// Get our licensed add-ons
	$licensed_addons = apply_filters( 'badgeos_licensed_addons', array() );

	// If we have any licensed add-ons
	if ( ! empty( $licensed_addons ) ) {

		// Output the header for licenses
		echo '<tr><td colspan="2"><hr/><h2>' . __( 'BadgeOS Add-on Licenses', 'badgeos' ) . '</h2></td></tr>';

		// Sort our licenses alphabetially
		ksort( $licensed_addons );

		// Output each individual licensed product
		foreach ( $licensed_addons as $slug => $addon ) {
			$status = ! empty( $addon['license_status'] ) ? $addon['license_status'] : 'inactive';
			echo '<tr valign="top">';
			echo '<th scope="row">';
			echo '<label for="badgeos_settings[licenses][' . $slug . ']">' . urldecode( $addon['item_name'] ) . ': </label></th>';
			echo '<td>';
			echo '<input type="text" size="30" name="badgeos_settings[licenses][' . $slug . ']" id="badgeos_settings[licenses][' . $slug . ']" value="' . $addon['license'] . '" />';
			echo ' <span class="badgeos-license-status ' . $status . '">' . sprintf( __( 'License Status: %s' ), '<strong>' . ucfirst( $status ) . '</strong>' ) . '</span>';
			echo '</td>';
			echo '</tr>';
		}
	}

}
add_action( 'badgeos_settings', 'badgeos_license_settings', 0 );

/**
 * Add-ons settings page
 *
 * @since  1.0.0
 */
function badgeos_add_ons_page() {
	$image_url = $GLOBALS['badgeos']->directory_url .'images/';
	?>
	<div class="wrap badgeos-addons">
		<div id="icon-options-general" class="icon32"></div>
		<h2><?php printf( __( 'BadgeOS Add-Ons &nbsp;&mdash;&nbsp; %s', 'badgeos' ), '<a href="http://badgeos.org/add-ons/?ref=badgeos" class="button-primary" target="_blank">' . __( 'Browse All Add-Ons', 'badgeos' ) . '</a>' ); ?></h2>
		<p><?php _e( 'These add-ons extend the functionality of BadgeOS.', 'badgeos' ); ?></p>
		<?php echo badgeos_add_ons_get_feed(); ?>
	</div>
	<?php
}

/**
 * Get all add-ons from the BadgeOS catalog feed.
 *
 * @since  1.2.0
 * @return string Concatenated markup from feed, or error message
*/
function badgeos_add_ons_get_feed() {

	// Attempt to pull back our cached feed
	$feed = get_transient( 'badgeos_add_ons_feed' );

	// If we don't have a cached feed, pull back fresh data
	if ( empty( $feed ) ) {

		// Retrieve and parse our feed
		$feed = wp_remote_get( 'http://badgeos.org/?feed=addons', array( 'sslverify' => false ) );
		if ( ! is_wp_error( $feed ) ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$feed = wp_remote_retrieve_body( $feed );
				$feed = str_replace( '<html><body>', '', $feed );
				$feed = str_replace( '</body></html>', '', $feed );
				// Cache our feed for 1 hour
				set_transient( 'badgeos_add_ons_feed', $feed, HOUR_IN_SECONDS );
			}
		} else {
			$feed = '<div class="error"><p>' . __( 'There was an error retrieving the add-ons list from the server. Please try again later.', 'badgeos' ) . '</div>';
		}
	}

	// Return our feed, or error message
	return $feed;
}

/**
 * Help and Support settings page
 * @since  1.0.0
 * @return void
 */
function badgeos_help_support_page() { ?>
	<div class="wrap" >
		<div id="icon-options-general" class="icon32"></div>
		<h2><?php _e( 'BadgeOS Help and Support', 'badgeos' ); ?></h2>
		<h2><?php _e( 'About BadgeOS', 'badgeos' ); ?>:</h2>
		<p><?php __( 'BadgeOS&trade; is plugin to WordPress that allows your site\'s users to complete tasks, demonstrate achievements, and earn badges. You define the achievement types, organize your requirements any way you like, and choose from a range of options to determine whether each task or requirement has been achieved. Badges earned in BadgeOS are Mozilla OBI compatible through out-of-the-box integration of the "Open Badge" API.', 'badgeos' ); ?></p>
		<p><?php printf(
			__( "BadgeOS is extremely extensible. Check out examples of what we've built with it, and stay connected to the project site for updates, add-ins and news. Share your ideas and code improvements on %s so we can keep making BadgeOS better for everyone.", 'badgeos' ),
			'<a href="https://github.com/opencredit/BadgeOS" target="_blank">GitHub</a>'
		); ?></p>
		<?php do_action( 'badgeos_help_support_page_about' ); ?>

		<h2><?php _e( 'Help / Support', 'badgeos' ); ?>:</h2>
		<p><?php printf(
			__( 'For support on using BadgeOS or to suggest feature enhancements, visit the %1$s. The BadgeOS team does perform custom development that extends the BadgeOS platform in some incredibly powerful ways. %2$s with inquiries. See examples of %3$s.', 'badgeos' ),
			sprintf(
				'<a href="http://badgeos.org" target="_blank">%s</a>',
				__( 'BadgeOS site', 'badgeos' )
			),
			sprintf(
				'<a href="http://badgeos.org/contact/" target="_blank">%s</a>',
				__( 'Contact us', 'badgeos' )
			),
			sprintf(
				'<a href="http://badgeos.org/about/sample-sites/">%s</a>',
				__( 'enhanced BadgeOS projects', 'badgeos' )
			)
		); ?></p>
		<p><?php printf( __( 'Please submit bugs or issues to %s for the BadgeOS Project.', 'badgeos' ), '<a href="https://github.com/opencredit/BadgeOS" target="_blank">Github</a>' ); ?></p>
		<?php do_action( 'badgeos_help_support_page_help' ); ?>

		<h2><?php _e( 'Shortcodes', 'badgeos' ); ?>:</h2>
		<p><?php printf(
			__( 'With BadgeOS activated, the following shortcodes can be placed on any page or post within WordPress to expose a variety of BadgeOS functions. Visit %s for additional information on shortcodes.', 'badgeos' ),
			'<a href="http://badgeos.org/support/shortcodes/" target="_blank">BadgeOS.org</a>'
		); ?></p>
		<?php do_action( 'badgeos_help_support_page_shortcodes' ); ?>
	</div>
	<?php
}

/**
 * Save Open BadgeOS Settings.
 * @return void
 */
function badgeos_open_badge_save() {
	$screen = get_current_screen();
    if( $screen->id === "badgeos_page_badgeos-ob" ) {
		if( current_user_can( "manage_options" ) ) {
			if ( ! empty( $_POST ) && check_admin_referer( 'badgeos_ob_settings', 'ob_settings_field' ) ) {
				
				if( isset( $_POST[ 'badgeos_assertion_url' ] ) && ! empty( $_POST[ 'badgeos_assertion_url' ] ) ) {
					update_option( 'badgeos_assertion_url', sanitize_text_field( $_POST[ 'badgeos_assertion_url' ] ) );
				}

				if( isset( $_POST[ 'badgeos_issuer_url' ] ) && ! empty( $_POST[ 'badgeos_issuer_url' ] ) ) {
					update_option( 'badgeos_issuer_url', sanitize_text_field( $_POST[ 'badgeos_issuer_url' ] ) );
				}
				
				if( isset( $_POST[ 'badgeos_json_url' ] ) && ! empty( $_POST[ 'badgeos_json_url' ] ) ) {
					update_option( 'badgeos_json_url', sanitize_text_field( $_POST[ 'badgeos_json_url' ] ) );
				}

				if( isset( $_POST[ 'badgeos_evidence_url' ] ) && ! empty( $_POST[ 'badgeos_evidence_url' ] ) ) {
					update_option( 'badgeos_evidence_url', sanitize_text_field( $_POST[ 'badgeos_evidence_url' ] ) );
				}

				if( isset( $_POST[ 'badgeos_embed_url' ] ) && ! empty( $_POST[ 'badgeos_embed_url' ] ) ) {
					update_option( 'badgeos_embed_url', sanitize_text_field( $_POST[ 'badgeos_embed_url' ] ) );
				}
				
			}
		}
	}
}
add_action( 'current_screen', 'badgeos_open_badge_save' );

/**
 * BadgeOS Open Badge Integration settings page.
 * @since  1.0.0
 * @return void
 */
function badgeos_open_badge_options() {
	$badgeos_assertion_url 		= get_option( 'badgeos_assertion_url' ); 
	$badgeos_json_url 			= get_option( 'badgeos_json_url' ); 
	$badgeos_issuer_url 		= get_option( 'badgeos_issuer_url' ); 
	$badgeos_embed_url 			= get_option( 'badgeos_embed_url' ); 
	$badgeos_evidence_url 		= get_option( 'badgeos_evidence_url' ); 
	
?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"></div>
		<h2><?php _e( 'Open Badge Settings', 'badgeos' ); ?></h2>
		<form method="post">
			<table class="form-table">
				<tr valign="top">
					<td align="left" width="20%" valign="top">
                        <?php _e( 'Assertion Page', 'badgeos' ); ?>
                    </td>
                    <td width="80%" align="left">
                        <label for="badgeos_assertion_url">
                           <?php wp_dropdown_pages( array( 'show_option_none' => __( 'Select Assertion Page', 'badgeos' ), 'selected'=>$badgeos_assertion_url, 'name'=> 'badgeos_assertion_url', 'id'=>'badgeos_assertion_url' ) ); ?> 
                        </label>
						<p class="badgeos_hint">Please, select a page for assertion json data.</p>
                    </td>
				</tr>
				<tr valign="top">
					<td align="left" valign="top">
                        <?php _e( 'Issuer Page', 'badgeos' ); ?>
                    </td>
                    <td align="left">
                        <label for="badgeos_issuer_url">
                           <?php wp_dropdown_pages( array( 'show_option_none' => __( 'Select Issuer Page', 'badgeos' ), 'selected'=>$badgeos_issuer_url, 'name'=> 'badgeos_issuer_url', 'id'=>'badgeos_issuer_url' ) ); ?> 
                        </label>
						<p class="badgeos_hint">Please, select a page for issuer json data.</p>
                    </td>
				</tr>
				<tr valign="top">
					<td align="left" valign="top">
                        <?php _e( 'Badge Page', 'badgeos' ); ?>
                    </td>
                    <td align="left">
                        <label for="badgeos_json_url">
                           <?php wp_dropdown_pages( array( 'show_option_none' => __( 'Select Badge Page', 'badgeos' ), 'selected'=>$badgeos_json_url, 'name'=> 'badgeos_json_url', 'id'=>'badgeos_json_url' ) ); ?> 
                        </label>
						<p class="badgeos_hint">Please, select a page for badge json data.</p>
                    </td>
				</tr>
				<tr valign="top">
					<td align="left" valign="top">
                        <?php _e( 'Evidence Page', 'badgeos' ); ?>
                    </td>
                    <td align="left">
                        <label for="badgeos_evidence_url">
                           <?php wp_dropdown_pages( array( 'show_option_none' => __( 'Select Evidence Page', 'badgeos' ), 'selected'=>$badgeos_evidence_url, 'name'=> 'badgeos_evidence_url', 'id'=>'badgeos_evidence_url' ) ); ?> 
                        </label>
						<p class="badgeos_hint">Please, select a page for badge evidence and add [badgeos_evidence] shortcode in page description.</p>
                    </td>
				</tr>
				<tr valign="top">
					<td align="left" valign="top">
                        <?php _e( 'Embed Page', 'badgeos' ); ?>
                    </td>
                    <td align="left">
                        <label for="badgeos_embed_url">
                           <?php wp_dropdown_pages( array( 'show_option_none' => __( 'Select Embed Page', 'badgeos' ), 'selected'=>$badgeos_embed_url, 'name'=> 'badgeos_embed_url', 'id'=>'badgeos_embed_url' ) ); ?> 
                        </label>
						<p class="badgeos_hint">Please, select a page for sharing the badge with Embed badge code. This page url will be used in embeding iframe for 3rd party websites.</p>
                    </td>
				</tr>
			</table>
			<?php wp_nonce_field( 'badgeos_ob_settings', 'ob_settings_field' ); ?>
			<?php
				// We need to get our api key
				submit_button( __( 'Save Settings', 'badgeos' ) );
			?>
		</form>
	</div>
<?php

}

/**
 * Globally replace "Featured Image" text with "Achievement Image".
 *
 * @since  1.3.0
 *
 * @param  string $string Original output string.
 * @return string         Potentially modified output string.
 */
function badgeos_featured_image_metabox_title( $string = '' ) {

	// If this is a new achievement type post
	// OR this is an existing achievement type post
	// AND the text is "Featured Image"
	// ...replace the string
	if (
		(
			( isset( $_GET['post_type'] ) && in_array( $_GET['post_type'], badgeos_get_achievement_types_slugs() ) )
			|| ( isset( $_GET['post'] ) && badgeos_is_achievement( $_GET['post'] ) )
		) && 'Featured Image' == $string

	)
		$string = __( 'Achievement Image', 'badgeos' );
	elseif (
		(
			( isset( $_GET['post_type'] ) && 'achievement-type' == $_GET['post_type'] )
			|| ( isset( $_GET['post'] ) && 'achievement-type' == get_post_type( $_GET['post'] ) )
		) && 'Featured Image' == $string
	)
		$string = __( 'Default Achievement Image', 'badgeos' );

	return $string;
}
add_filter( 'gettext', 'badgeos_featured_image_metabox_title' );


/**
 * Globally replace "Featured Image" text with "Achievement Image".
 *
 * @since  1.3.0
 *
 * @param  string $string Original output string.
 * @return string         Potentially modified output string.
 */
function badgeos_open_badge_png_only_note( $html ) {
	
	$pt = get_current_screen()->post_type;
	
	//if ( $pt != 'post') return;
	$achievement_types = get_posts( array(
		'post_type'      =>	'achievement-type',
		'posts_per_page' =>	-1,
	) );

	// Loop through each achievement type post and register it as a CPT
	foreach ( $achievement_types as $achievement_type ) {
		if ( $pt == $achievement_type->post_name ) {
			return $html .= "<b>".__( 'Note', 'badgeos' ).'</b>: '.__( "If 'enabled badge baking' option is 'yes' then upload png images only here.", 'badgeos' );
		}
	}
	return $html;
}
add_filter( 'admin_post_thumbnail_html', 'badgeos_open_badge_png_only_note');

/**
 * Change "Featured Image" to "Achievement Image" in post editor metabox.
 *
 * @since  1.3.0
 *
 * @param  string  $content HTML output.
 * @param  integer $ID      Post ID.
 * @return string           Potentially modified output.
 */
function badgeos_featured_image_metabox_text( $content = '', $ID = 0 ) {
	if ( badgeos_is_achievement( $ID ) )
		$content = str_replace( 'featured image', __( 'achievement image', 'badgeos' ), $content );
	elseif ( 'achievement-type' == get_post_type( $ID ) )
		$content = str_replace( 'featured image', __( 'default achievement image', 'badgeos' ), $content );

	return $content;
}
add_filter( 'admin_post_thumbnail_html', 'badgeos_featured_image_metabox_text', 10, 2 );

/**
 * Change "Featured Image" to "Achievement Image" throughout media modal.
 *
 * @since  1.3.0
 *
 * @param  array  $strings All strings passed to media modal.
 * @param  object $post    Post object.
 * @return array           Potentially modified strings.
 */
function badgeos_media_modal_featured_image_text( $strings = array(), $post = null ) {

	if ( is_object( $post ) ) {
		if ( badgeos_is_achievement( $post->ID ) ) {
			$strings['setFeaturedImageTitle'] = __( 'Set Achievement Image', 'badgeos' );
			$strings['setFeaturedImage'] = __( 'Set achievement image', 'badgeos' );
		} elseif ( 'achievement-type' == $post->post_type ) {
			$strings['setFeaturedImageTitle'] = __( 'Set Default Achievement Image', 'badgeos' );
			$strings['setFeaturedImage'] = __( 'Set default achievement image', 'badgeos' );
		}
	}

	return $strings;
}
add_filter( 'media_view_strings', 'badgeos_media_modal_featured_image_text', 10, 2 );

/**
 * Get capability required for BadgeOS administration.
 *
 * @since  1.4.0
 *
 * @return string User capability.
 */
function badgeos_get_manager_capability() {
	$badgeos_settings = get_option( 'badgeos_settings' );
	return isset( $badgeos_settings[ 'minimum_role' ] ) ? $badgeos_settings[ 'minimum_role' ] : 'manage_options';
}

/**
 * Get capability required for Submission management.
 *
 * @since  1.4.0
 *
 * @return string User capability.
 */
function badgeos_get_submission_manager_capability() {
	$badgeos_settings = get_option( 'badgeos_settings' );
	return isset( $badgeos_settings[ 'submission_manager_role' ] ) ? $badgeos_settings[ 'submission_manager_role' ] : badgeos_get_manager_capability();
}

/**
 * Check if a user can manage submissions.
 *
 * @since  1.4.0
 *
 * @param  integer $user_id User ID.
 * @return bool             True if user can manaage submissions, otherwise false.
 */
function badgeos_user_can_manage_submissions( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	return ( user_can( $user_id, badgeos_get_submission_manager_capability() ) || user_can( $user_id, badgeos_get_manager_capability() ) );
}