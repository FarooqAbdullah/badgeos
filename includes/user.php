<?php
/**
 * User-related Functions
 *
 * @package BadgeOS
 * @subpackage Admin
 * @author LearningTimes, LLC
 * @license http://www.gnu.org/licenses/agpl.txt GNU AGPL v3.0
 */

/**
 * Get a user's badgeos achievements
 *
 * @since  1.0.0
 * @param  array $args An array of all our relevant arguments
 * @return array       An array of all the achievement objects that matched our parameters, or empty if none
 */
function badgeos_get_user_achievements( $args = array() ) {
	
	// Setup our default args
	$defaults = array(
		'entry_id'          => false,     // The given user's ID
		'user_id'          => 0,     // The given user's ID
		'site_id'          => get_current_blog_id(), // The given site's ID
		'achievement_id'   => false, // A specific achievement's post ID
		'achievement_type' => false, // A specific achievement type
		'since' => 0,
		'display' => false
	);
	$args = wp_parse_args( $args, $defaults );

    if( $args['user_id'] == 0 ) {
        $args['user_id'] = get_current_user_id();
    }
	
    $where = 'user_id = ' . $args['user_id'];
	if( $args['since'] > 1 ) {
		$sincedate = date("Y-m-d H:i:s", $args['since']);
		$where .= " AND dateadded > '".$sincedate."'";
    }

    if( $args['entry_id'] != false ) {
        $where .= ' AND entry_id = ' . $args['entry_id'];
    }
	if( $args['achievement_id'] != false ) {
        $where .= ' AND ID = ' . $args['achievement_id'];
    }
    if( $args['achievement_type'] != false ) {
        if( is_array( $args['achievement_type'] ) ) {
            $loop_count = 1;
            foreach( $args['achievement_type'] as $achievement_type ) {
                if( $loop_count == 1 ) {
                    $condition = 'AND';
                } else {
                    $condition = 'OR';
                }
                $where .= ' ' . $condition . ' achievement_type = "' . $achievement_type . '"';

                $loop_count++;
            }
        } else {
            $where .= ' AND achievement_type = "' . $args['achievement_type'] . '"';
        }
    }

	global $wpdb;

	badgeos_run_database_script();

    $table_name = $wpdb->prefix . 'badgeos_achievements';
	$user_achievements = $wpdb->get_results( "SELECT * FROM $table_name WHERE $where order by dateadded" );

    return $user_achievements;
}

/**
 * Updates the user's earned achievements
 *
 * We can either replace the achievement's array, or append new achievements to it.
 *
 * @since  1.0.0
 * @param  array        $args An array containing all our relevant arguments
 * @return integer|bool       The updated umeta ID on success, false on failure
 */
function badgeos_update_user_achievements( $args = array() ) {
	
	global $wpdb;
	
	// Setup our default args
	$defaults = array(
		'user_id'          => 0,     // The given user's ID
		'site_id'          => get_current_blog_id(), // The given site's ID
		'all_achievements' => false, // An array of ALL achievements earned by the user
		'new_achievements' => false, // An array of NEW achievements earned by the user
	);
	$args = wp_parse_args( $args, $defaults );

	// Use current user's ID if none specified
	if ( ! $args['user_id'] )
		$args['user_id'] = wp_get_current_user()->ID;

	// Grab our user's achievements
	$achievements = badgeos_get_user_achievements( array( 'user_id' => absint( $args['user_id'] ), 'site_id' => 'all' ) );
	$new_rows = array();
	foreach( $achievements as $achievement ) {
		$object            		= new stdClass;
		$object->ID        		= $achievement->ID;
		$object->post_type 		= $achievement->achievement_type;
		$object->points    		= $achievement->points;
		$object->trigger   		= $achievement->this_trigger;
		$object->date_earned 	= strtotime( $achievement->dateadded );
		$new_rows[] = $object;
	}
	
	// If we don't already have an array stored for this site, create a fresh one
	if ( !isset( $achievements[$args['site_id']] ) )
		$achievements[$args['site_id']] = array();

	// Determine if we should be replacing or appending to our achievements array
	if ( is_array( $args['all_achievements'] ) )
		$achievements[$args['site_id']] = $args['all_achievements'];
	elseif ( is_array( $args['new_achievements'] ) && ! empty( $args['new_achievements'] ) )
		$achievements[$args['site_id']] = array_merge( $new_rows, $args[ 'new_achievements' ] );
	$new_achievements = $args[ 'new_achievements' ];
    if( $new_achievements !== false ) {
		$new_achievement = $new_achievements[0];
		
		badgeos_run_database_script();

		$wpdb->insert($wpdb->prefix.'badgeos_achievements', array(
			'ID'        			=> $new_achievement->ID,
			'achievement_type'      => $new_achievement->post_type,
			'achievement_title'     => get_the_title( $new_achievement->ID ),
			'points'             	=> $new_achievement->points,
			'this_trigger'         	=> $new_achievement->trigger,
			'user_id'               => absint( $args['user_id'] ),
			'site_id'               => $args['site_id'],
			'baked_image'           => '',
			// 'dateadded'             => date("Y-m-d H:i:s")
		));
		update_user_meta( absint( $args['user_id'] ), '_badgeos_achievements', $achievements);
		return $wpdb->insert_id;
	} else {

		// Finally, update our user meta
		return update_user_meta( absint( $args['user_id'] ), '_badgeos_achievements', $achievements);
	}
}

/**
 * Display achievements for a user on their profile screen
 *
 * @since  1.0.0
 * @param  object $user The current user's $user object
 * @return void
 */
function badgeos_user_profile_data( $user = null ) {

	wp_enqueue_script('thickbox');
	wp_enqueue_style('thickbox');
	wp_enqueue_script( 'badgeos-openjs' ); 
	
	$achievement_ids = array();

		echo '<h2>' . __( 'BadgeOS Email Notifications', 'badgeos' ) . '</h2>';
		echo '<table class="form-table">';
		echo '<tr>';
			echo '<th scope="row">' . __( 'Email Preference', 'badgeos' ) . '</th>';
			echo '<td>';
				echo '<label for="_badgeos_can_notify_user"><input type="checkbox" name="_badgeos_can_notify_user" id="_badgeos_can_notify_user" value="1" ' . checked( badgeos_can_notify_user( $user->ID ), true, false ) . '/>' . __( 'Enable BadgeOS Email Notifications', 'badgeos' ) . '</label>';
			echo '</td>';
		echo '</tr>';
		echo '</table>';

	//verify uesr meets minimum role to view earned badges
	
		
	$achievements = badgeos_get_user_achievements( array( 'user_id' => absint( $user->ID ) ) );
	
	echo '<h2>' . __( 'Earned Achievements', 'badgeos' ) . '</h2>';

	echo '<input type="hidden" name="badgeoscp" id="badgeoscp" value="'.badgeos_get_users_points( $user->ID ).'" /><table class="form-table">';
	echo '<tr>';
		echo '<th><label for="user_points">' . __( 'Earned Points', 'badgeos' ) . '</label></th>';
		echo '<td>';
			echo '<input type="text" name="user_points" id="badgeos_user_points" value="' . badgeos_get_users_points( $user->ID ) . '" class="regular-text" /><br />';
			echo '<span class="description">' . __( "The user's points total. Entering a new total will automatically log the change and difference between totals.", 'badgeos' ) . '</span>';
		echo '</td>';
	echo '</tr>';

	echo '<tr><td colspan="2">';
	
	// List all of a user's earned achievements
	if ( $achievements ) {
		echo '<table class="widefat badgeos-table">';
		echo '<thead><tr>';
			echo '<th>'. __( 'Image', 'badgeos' ) .'</th>';
			echo '<th>'. __( 'Name', 'badgeos' ) .'</th>';
			echo '<th>'. __( 'Share', 'badgeos' ) .'</th>';
			if ( current_user_can( badgeos_get_manager_capability() ) ) {
				echo '<th>'. __( 'Action', 'badgeos' ) .'</th>';
			}
		echo '</tr></thead>';
		$badgeos_embed_url       = get_permalink( get_option( 'badgeos_embed_url' ) ); 
		$badgeos_evidence_page_id		= get_option( 'badgeos_evidence_url' );
		foreach ( $achievements as $achievement ) {
			if( $achievement->achievement_type != 'step' ) {
				$sharedTitle = $achievement->achievement_title;
				$new_badgeos_embed_url 		= add_query_arg( 'bg', $achievement->ID, $badgeos_embed_url );
				$new_badgeos_embed_url  	= add_query_arg( 'eid', $achievement->entry_id, $new_badgeos_embed_url );
				$new_badgeos_embed_url  	= add_query_arg( 'uid', $user->ID, $new_badgeos_embed_url );
				
				$sharedURL  = get_permalink( $achievement->ID );
				$sharedURL  = add_query_arg( 'bg', $achievement->ID, $sharedURL );
				$sharedURL  = add_query_arg( 'eid', $achievement->entry_id, $sharedURL );
				$sharedURL  = add_query_arg( 'uid', $user->ID, $sharedURL );
				$sharedURL  = add_query_arg( 'tn', time(), $sharedURL );
				$sharedURL  = urlencode( $sharedURL );     
		
				$twitterURL = 'https://twitter.com/intent/tweet?text='.$sharedTitle.'&amp;url='.$sharedURL;
				$facebookURL = 'https://www.facebook.com/sharer/sharer.php?u='.$sharedURL;
				$linkedInURL = 'https://www.linkedin.com/shareArticle?mini=true&url='.$sharedURL.'&amp;title='.$sharedTitle;

				$popup = '<h2>Share</h2><a class="close" href="javascript:;">&times;</a><div class="content">';
				$sharelinks = '<div class="social_buttons">';
				$sharelinks .= '<ul>';
				$sharelinks .= '<li><a href="'.$facebookURL.'" onclick="window.open(this.href, \'facebookwindow\',\'left=20,top=20,width=700,height=500,toolbar=0,resizable=1\'); return false;"><i class="btm_facebook"></i></a></li>';
				$sharelinks .= '<li><a href="'.$twitterURL.'" onclick="window.open(this.href, \'twitterwindow\',\'left=20,top=20,width=600,height=300,toolbar=0,resizable=1\'); return false;"><i class="btm_twitter"></i></a></li>';
				$sharelinks .= '<li><a href="'.$linkedInURL.'" onclick="window.open(this.href, \'linkedInwindow\',\'left=20,top=20,width=600,height=550,toolbar=0,resizable=1\'); return false;"><i class="btm_linkedin"></i></a></li>';
				$sharelinks .= '</ul></div>';
				$iframe = ' <iframe src="'.htmlentities( $new_badgeos_embed_url ).'"></iframe> ';
				$popup .= '<div class="link-column"><label>'.__( 'Embed Link', 'badgeos' ).':</label> <textarea class="badgeos-share-card">'.$iframe.'</textarea></div>';
				$popup .= '<div class="share-column">'.$sharelinks.'</div>';
				$popup .= '</div>';

				// Setup our revoke URL
				$revoke_url = add_query_arg( array(
					'action'         	=> 'revoke',
					'user_id'        	=> absint( $user->ID ),
					'achievement_id' 	=> absint( $achievement->ID ),
					'entry_id' 			=> absint( $achievement->entry_id ),
				) );

				$dirs = wp_upload_dir();
				$baseurl = trailingslashit( $dirs[ 'baseurl' ] );
				$basedir = trailingslashit( $dirs[ 'basedir' ] );
				$badge_directory = trailingslashit( $basedir.'user_badges/'.$user->ID );
				$badge_url = trailingslashit( $baseurl.'user_badges/'.$user->ID );
				
				echo '<tr>';
					if( ! empty( $achievement->baked_image ) && file_exists( $badge_directory.$achievement->baked_image ) ) {
						echo '<td><img src="'.$badge_url.$achievement->baked_image.'" height="50" with="50" />';
					} else {
						echo '<td>'. badgeos_get_achievement_post_thumbnail( $achievement->ID, array( 50, 50 ) ) .'</td>';
					}
					if ( current_user_can( badgeos_get_manager_capability() ) ) {
						echo '<td>';
							edit_post_link( $sharedTitle, '', '', $achievement->ID );
						echo '</td>';
					} else {
						
						$evidence_url 	= get_permalink( $badgeos_evidence_page_id );
						$evidence_url   = add_query_arg( 'bg', $achievement->ID, $evidence_url );
						$evidence_url   = add_query_arg( 'eid', $achievement->entry_id, $evidence_url );
						$evidence_url   = add_query_arg( 'uid', $achievement->user_id, $evidence_url );

						echo '<td><a href="'.$evidence_url.'">'.$sharedTitle.'</a></td>';
					}
					echo '<td><a class="badgeos_share_popup" data-bg="'.$achievement->ID.'" data-eid="'.$achievement->entry_id.'" data-uid="'.$user->ID.'" href="javascript:;">Share</a><div id="open_badge_share_box_id'.$achievement->entry_id.'" class="open_badge_share_box_id" style="display:none; position:absolute;">'.$popup.'</div></td>';
					if ( current_user_can( badgeos_get_manager_capability() ) ) {
						echo '<td> <span class="delete"><a class="error" href="'.esc_url( wp_nonce_url( $revoke_url, 'badgeos_revoke_achievement' ) ).'">' . __( 'Revoke Award', 'badgeos' ) . '</a></span></td>';
					}
				echo '</tr>';

				$achievement_ids[] = $achievement->ID;
			}
		}
		echo '</table>';
		//}

		echo '</td></tr>';
		echo '</table>';

		// If debug mode is on, output our achievements array
		if ( badgeos_is_debug_mode() ) {

			echo __( 'DEBUG MODE ENABLED', 'badgeos' ) . '<br />';
			echo __( 'Metadata value for:', 'badgeos' ) . ' _badgeos_achievements<br />';

			var_dump ( $achievements );

		}

		echo '<br/>';

		

	}
	// Output markup for awarding achievement for user
	badgeos_profile_award_achievement( $user, $achievement_ids );

}
add_action( 'show_user_profile', 'badgeos_user_profile_data' );
add_action( 'edit_user_profile', 'badgeos_user_profile_data' );


/**
 * Save extra user meta fields to the Edit Profile screen
 *
 * @since  1.0.0
 * @param  int  $user_id      User ID being saved
 * @return mixed			  false if current user can not edit users, void if can
 */
function badgeos_save_user_profile_fields( $user_id = 0 ) {

	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	$can_notify = isset( $_POST['_badgeos_can_notify_user'] ) ? 'true' : 'false';
	update_user_meta( $user_id, '_badgeos_can_notify_user', $can_notify );

	// Update our user's points total, but only if edited
	if ( isset( $_POST['user_points'] ) && $_POST['user_points'] != $_POST['badgeoscp'] ) {
		badgeos_update_users_points( $user_id, absint( $_POST['user_points'] ), get_current_user_id() );
	}

}
add_action( 'personal_options_update', 'badgeos_save_user_profile_fields' );
add_action( 'edit_user_profile_update', 'badgeos_save_user_profile_fields' );

/**
 * Generate markup for awarding an achievement to a user
 *
 * @since  1.0.0
 * @param  object $user         The current user's $user object
 * @param  array  $achievements array of user-earned achievement IDs
 * @return string               concatenated markup
 */
function badgeos_profile_award_achievement( $user = null, $achievement_ids = array() ) {

	// Grab our achivement types
	$achievement_types = badgeos_get_achievement_types();
	?>

	<h2><?php _e( 'Award an Achievement', 'badgeos' ); ?></h2>

	<table class="form-table">
		<tr>
			<th><label for="thechoices"><?php _e( 'Select an Achievement Type to Award:', 'badgeos' ); ?></label></th>
			<td>
				<select id="thechoices">
				<option></option>
				<?php
				foreach ( $achievement_types as $achievement_slug => $achievement_type ) {
					echo '<option value="'. $achievement_slug .'">' . ucwords( $achievement_type['single_name'] ) .'</option>';
				}
				?>
				</select>
			</td>
		</tr>
		<tr><td id="boxes" colspan="2">
			<?php foreach ( $achievement_types as $achievement_slug => $achievement_type ) : ?>
				<table id="<?php echo esc_attr( $achievement_slug ); ?>" class="widefat badgeos-table">
					<thead><tr>
						<th><?php _e( 'Image', 'badgeos' ); ?></th>
						<th><?php echo ucwords( $achievement_type['single_name'] ); ?></th>
						<th><?php _e( 'Action', 'badgeos' ); ?></th>
						<th><?php _e( 'Awarded', 'badgeos' ); ?></th>
					</tr></thead>
					<tbody>
					<?php
					// Load achievement type entries
					$the_query = new WP_Query( array(
						'post_type'      => $achievement_slug,
						'posts_per_page' => '999',
						'post_status'    => 'publish'
					) );

					if ( $the_query->have_posts() ) : ?>

						<?php while ( $the_query->have_posts() ) : $the_query->the_post();

							// Setup our award URL
							$award_url = add_query_arg( array(
								'action'         => 'award',
								'achievement_id' => absint( get_the_ID() ),
								'user_id'        => absint( $user->ID )
							) );
							?>
							<tr>
								<td><?php the_post_thumbnail( array( 50, 50 ) ); ?></td>
								<td>
									<?php echo edit_post_link( get_the_title() ); ?>
								</td>
								<td>
									<a href="<?php echo esc_url( wp_nonce_url( $award_url, 'badgeos_award_achievement' ) ); ?>"><?php printf( __( 'Award %s', 'badgeos' ), ucwords( $achievement_type['single_name'] ) ); ?></a>
									<?php if ( in_array( get_the_ID(), (array) $achievement_ids ) ) :
										// Setup our revoke URL
										$revoke_url = add_query_arg( array(
											'action'         => 'revoke',
											'user_id'        => absint( $user->ID ),
											'achievement_id' => absint( get_the_ID() ),
										) );
										?>
										<span class="delete"><a class="error" href="<?php echo esc_url( wp_nonce_url( $revoke_url, 'badgeos_revoke_achievement' ) ); ?>"><?php _e( 'Revoke Award', 'badgeos' ); ?></a></span>
									<?php endif; ?>

								</td>
							</tr>
						<?php endwhile; ?>

					<?php else : ?>
						<tr>
							<th><?php printf( __( 'No %s found.', 'badgeos' ), $achievement_type['plural_name'] ); ?></th>
						</tr>
					<?php endif; wp_reset_postdata(); ?>

					</tbody>
					</table><!-- #<?php echo esc_attr( $achievement_slug ); ?> -->
			<?php endforeach; ?>
		</td><!-- #boxes --></tr>
	</table>

	<script type="text/javascript">
		jQuery(document).ready(function($){
			<?php foreach ( $achievement_types as $achievement_slug => $achievement_type ) { ?>
				$('#<?php echo $achievement_slug; ?>').hide();
			<?php } ?>
			$("#thechoices").change(function(){
				if ( 'all' == this.value )
					$("#boxes").children().show();
				else
					$("#" + this.value).show().siblings().hide();
			}).change();
		});
	</script>
	<?php
}

/**
 * Process the adding/revoking of achievements on the user profile page
 *
 * @since  1.0.0
 * @return void
 */
function badgeos_process_user_data() {

	//verify uesr meets minimum role to view earned badges
	if ( current_user_can( badgeos_get_manager_capability() ) ) {

		// Process awarding achievement to user
		if ( isset( $_GET['action'] ) && 'award' == $_GET['action'] &&  isset( $_GET['user_id'] ) && isset( $_GET['achievement_id'] ) ) {

			// Verify our nonce
			check_admin_referer( 'badgeos_award_achievement' );

			// Award the achievement
			badgeos_award_achievement_to_user( absint( $_GET['achievement_id'] ), absint( $_GET['user_id'] ) );

			// Redirect back to the user editor
			wp_redirect( add_query_arg( 'user_id', absint( $_GET['user_id'] ), admin_url( 'user-edit.php' ) ) );
			exit();
		}

		// Process revoking achievement from a user
		if ( isset( $_GET['action'] ) && 'revoke' == $_GET['action'] && isset( $_GET['user_id'] ) && isset( $_GET['achievement_id'] ) ) {

			// Verify our nonce
			check_admin_referer( 'badgeos_revoke_achievement' );

			// Revoke the achievement
			badgeos_revoke_achievement_from_user( absint( $_GET['entry_id'] ), absint( $_GET['achievement_id'] ), absint( $_GET['user_id'] ) );

			// Redirect back to the user editor
			wp_redirect( add_query_arg( 'user_id', absint( $_GET['user_id'] ), admin_url( 'user-edit.php' ) ) );
			exit();

		}

	}

}
add_action( 'init', 'badgeos_process_user_data' );

/**
 * Returns array of achievement types a user has earned across a multisite network
 *
 * @since  1.2.0
 * @param  integer $user_id  The user's ID
 * @return array             An array of post types
 */
function badgeos_get_network_achievement_types_for_user( $user_id ) {
	global $blog_id;

	// Store a copy of the original ID for later
	$cached_id = $blog_id;

	// Assume we have no achievement types
	$all_achievement_types = array();

	// Loop through all active sites
	$sites = badgeos_get_network_site_ids();
	foreach( $sites as $site_blog_id ) {

		// If we're polling a different blog, switch to it
		if ( $blog_id != $site_blog_id ) {
			switch_to_blog( $site_blog_id );
		}

		// Merge earned achievements to our achievement type array
		$achievement_types = badgeos_get_user_earned_achievement_types( $user_id );
		if ( is_array($achievement_types) ) {
			$all_achievement_types = array_merge($achievement_types,$all_achievement_types);
		}
	}

	if ( is_multisite() ) {
		// Restore the original blog so the sky doesn't fall
		switch_to_blog( $cached_id );
	}

	// Pare down achievement type list so we return no duplicates
	$achievement_types = array_unique( $all_achievement_types );

	// Return all found achievements
	return $achievement_types;
}

/**
 * Check if a user has disabled email notifications.
 *
 * @since  1.4.0
 *
 * @param  int   $user_id User ID.
 * @return bool           True if user can be emailed, otherwise false.
 */
function badgeos_can_notify_user( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	return 'false' !== get_user_meta( $user_id, '_badgeos_can_notify_user', true );
}
