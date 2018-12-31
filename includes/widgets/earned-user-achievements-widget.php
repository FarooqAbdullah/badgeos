<?php
//widget displays achievements earned for the logged in user
class earned_user_achievements_widget extends WP_Widget {

	//process the new widget
	function __construct() {
		$widget_ops = array(
			'classname' => 'earned_user_achievements_class',
			'description' => __( 'Displays all achievements earned by the logged in user', 'badgeos' )
		);
		parent::__construct( 'earned_user_achievements_widget', __( 'BadgeOS Earned User Achievements', 'badgeos' ), $widget_ops );
	}

	//build the widget settings form
	function form( $instance ) {
		$defaults = array( 'title' => __( 'My Achievements', 'badgeos' ), 'number' => '10', 'point_total' => '', 'set_achievements' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults );
		$title = $instance['title'];
		$number = $instance['number'];
		$point_total = $instance['point_total'];
		$set_achievements = ( isset( $instance['set_achievements'] ) ) ? (array) $instance['set_achievements'] : array();
		?>
            <p><label><?php _e( 'Title', 'badgeos' ); ?>: <input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"  type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
			<p><label><?php _e( 'Number to display (0 = all)', 'badgeos' ); ?>: <input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>"  type="text" value="<?php echo absint( $number ); ?>" /></label></p>
			<p><label><input type="checkbox" id="<?php echo esc_attr( $this->get_field_name( 'point_total' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'point_total' ) ); ?>" <?php checked( $point_total, 'on' ); ?> /> <?php _e( 'Display user\'s total points', 'badgeos' ); ?></label></p>
			<p><?php _e( 'Display only the following Achievement Types:', 'badgeos' ); ?><br />
				<?php
				//get all registered achievements
				$achievements = badgeos_get_achievement_types();

				//loop through all registered achievements
				foreach ( $achievements as $achievement_slug => $achievement ) {

					//hide the step CPT
					if ( $achievement['single_name'] == 'step' ) {
						continue;
					}

					//if achievement displaying exists in the saved array it is enabled for display
					$checked = checked( in_array( $achievement_slug, $set_achievements ), true, false );

					echo '<label for="' . esc_attr( $this->get_field_name( 'set_achievements' ) ) . '_' . esc_attr( $achievement_slug ) . '">'
						 . '<input type="checkbox" name="' . esc_attr( $this->get_field_name( 'set_achievements' ) ) . '[]" id="' . esc_attr( $this->get_field_name( 'set_achievements' ) ) . '_' . esc_attr( $achievement_slug ) . '" value="' . esc_attr( $achievement_slug ) . '" ' . $checked . ' />'
						 . ' ' . esc_html( ucfirst( $achievement[ 'plural_name' ] ) )
						 . '</label><br />';

				}
				?>
			</p>
        <?php
	}

	//save and sanitize the widget settings
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );
		$instance['point_total'] = ( ! empty( $new_instance['point_total'] ) ) ? sanitize_text_field( $new_instance['point_total'] ) : '';
		$instance['set_achievements'] = array_map( 'sanitize_text_field', $new_instance['set_achievements'] );

		return $instance;
	}

	//display the widget
	function widget( $args, $instance ) {
		global $user_ID;

		echo $args['before_widget'];

		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( !empty( $title ) ) { echo $args['before_title'] . $title . $args['after_title']; };

		//user must be logged in to view earned badges and points
		if ( is_user_logged_in() ) {

			//display user's points if widget option is enabled
			if ( $instance['point_total'] == 'on' ) {
				echo '<p class="badgeos-total-points">' . sprintf( __( 'My Total Points: %s', 'badgeos' ), '<strong>' . number_format( badgeos_get_users_points() ) . '</strong>' ) . '</p>';
			}

			$achievements = badgeos_get_user_achievements(array('display'=>true));
			
			if ( is_array( $achievements ) && ! empty( $achievements ) ) {

				$number_to_show = absint( $instance['number'] );
				$thecount = 0;

				wp_enqueue_script( 'badgeos-achievements' );
				wp_enqueue_style( 'badgeos-widget' );

				//load widget setting for achievement types to display
				$set_achievements = ( isset( $instance['set_achievements'] ) ) ? $instance['set_achievements'] : '';

				//show most recently earned achievement first
				$achievements = array_reverse( $achievements );

				echo '<ul class="widget-achievements-listing">';
				foreach ( $achievements as $achievement ) {

					//verify achievement type is set to display in the widget settings
					//if $set_achievements is not an array it means nothing is set so show all achievements
					if ( ! is_array( $set_achievements ) || in_array( $achievement->achievement_type, $set_achievements ) ) {

						//exclude step CPT entries from displaying in the widget
						if ( $achievement->achievement_type != 'step' ) {

							$permalink  = get_permalink( $achievement->ID );
							if( empty( $permalink ) ) {
								$permalink  = 'javascript:;';
							}
							$title      = $achievement->achievement_title;
							
							$dirs = wp_upload_dir();
							$baseurl = trailingslashit( $dirs[ 'baseurl' ] );
							$basedir = trailingslashit( $dirs[ 'basedir' ] );
							$badge_directory = trailingslashit( $basedir.'user_badges/'.$achievement->user_id );
							$badge_url = trailingslashit( $baseurl.'user_badges/'.$achievement->user_id );
		
							if( ! empty( $achievement->baked_image ) && file_exists( $badge_directory.$achievement->baked_image ) ) {
								$img = '<img src="'.$badge_url.$achievement->baked_image.'" height="50" with="50" />';
							} else {
								$img = badgeos_get_achievement_post_thumbnail( $achievement->ID, array( 50, 50 ), 'wp-post-image' );
							}
							

							$thumb      = $img ? '<a class="badgeos-item-thumb" href="'. esc_url( $permalink ) .'">' . $img .'</a>' : '';
							$class      = 'widget-badgeos-item-title';
							$item_class = $thumb ? ' has-thumb' : '';

							echo '<li id="widget-achievements-listing-item-'. absint( $achievement->ID ) .'" class="widget-achievements-listing-item'. esc_attr( $item_class ) .'">';
							echo $thumb;
							echo '<a class="widget-badgeos-item-title '. esc_attr( $class ) .'" href="'. esc_url( $permalink ) .'">'. esc_html( $title ) .'</a>';
							echo '</li>';

							$thecount++;

							if ( $thecount == $number_to_show && $number_to_show != 0 ) {
								break;
							}

						}

					}
				}

				echo '</ul><!-- widget-achievements-listing -->';

			}

		} else {

			//user is not logged in so display a message
			_e( 'You must be logged in to view earned achievements', 'badgeos' );

		}

		echo $args['after_widget'];
	}

}
