<?php

/**
 * Register the [badgeos_achievement] shortcode.
 *
 * @since 1.4.0
 */
function badgeos_register_evidence_shortcode() {
	badgeos_register_shortcode( array(
		'name'            => __( 'Achievement Evidence', 'badgeos' ),
		'slug'            => 'badgeos_evidence',
		'output_callback' => 'badgeos_achievement_evidence_shortcode',
		'description'     => __( "Render a single achievement's evidence.", 'badgeos' ),
		'attributes'      => array(
			'show_sharing_opt' => array(
				'name'        => __( 'All Share?', 'badgeos' ),
				'description' => __( 'Display filter controls.', 'badgeos' ),
				'type'        => 'select',
				'values'      => array(
					'Yes'  => __( 'Yes', 'badgeos' ),
					'No' => __( 'No', 'badgeos' )
					),
				'default'     => 'true',
				)
		),
	) );
}
add_action( 'init', 'badgeos_register_evidence_shortcode' );

/**
 * Single Achievement Shortcode.
 *
 * @since  1.0.0
 *
 * @param  array $atts Shortcode attributes.
 * @return string 	   HTML markup.
 */
function badgeos_achievement_evidence_shortcode( $atts = array() ) {

    global $wpdb;

	// get the post id
	$atts = shortcode_atts( array(
	  'show_sharing_opt' => 'Yes',
	), $atts, 'badgeos_evidence' );
    
    $achievement_id 	= sanitize_text_field( $_REQUEST['bg'] );
    $entry_id  	    = sanitize_text_field( $_REQUEST['eid'] );
    $user_id  	        = sanitize_text_field( $_REQUEST['uid'] );
    
    // return if entry_id not specified
	if ( empty( $entry_id ) )
      return;
    
    // return if user_id not specified  
    if ( empty( $user_id ) )
      return;
    
    $output = '';

    $recs = $wpdb->get_results( "select * from ".$wpdb->prefix."badgeos_achievements where ID='".$achievement_id."' and  entry_id='".$entry_id."' and  user_id='".$user_id."'" );
    if( count( $recs ) > 0 ) {

        $rec = $recs[0];

        $user = get_user_by( 'ID', $user_id );
        $achievement = get_post( $rec->ID );
        wp_enqueue_style( 'badgeos-front' );
        wp_enqueue_script( 'badgeos-achievements' );
        
        $dirs = wp_upload_dir();
        $baseurl = trailingslashit( $dirs[ 'baseurl' ] );
        $basedir = trailingslashit( $dirs[ 'basedir' ] );
        $badge_directory = trailingslashit( $basedir.'user_badges/'.$user_id );
        $badge_url = trailingslashit( $baseurl.'user_badges/'.$user_id );
        ?>
            <div class="evidence_main">
                <div class="left_col">
                    <?php if( ! empty( $rec->baked_image ) && file_exists( $badge_directory.$rec->baked_image ) ) { ?>
                        <img src="<?php echo $badge_url.$rec->baked_image;?>" with="100%" />
                    <?php } else { ?>
                        <?php echo badgeos_get_achievement_post_thumbnail( $achievement_id, 'full' ); ?>
                    <?php  } ?>
                    <div class="user_name"><?php echo $user->display_name;?></div>
                    <div class="social-share">
                        <ul> 
                            <li>Share on</li> 
                            <li><a href="#" class="facebook fb share">Facebook</a></li> 
                            <li><a href="https://twitter.com/intent/tweet?url=https%3A%2F%2Ft.cred.ly%2F04fNRpRiNBqlErKWi5VWIw%3D%3D%24%24%24CCyk1tk4w-vixVMJEFwDHWc0OBIfeRQHQG4kGbcdWLCUBQLyAnm86qrtlDUatEGG%3Fr%3Dhttp%253A%252F%252Fcred.ly%252Fc%252F13581706%26t%3D1545242570%26c%3Dtw&amp;text=U.S.+Election+2016+-+I+Voted%3A+I+received+credit+from+Credly" class="twitter share">Twitter</a></li> 
                            <li><a href="https://www.linkedin.com/cws/share?url=https%3A%2F%2Ft.cred.ly%2FMo_VzjHf7blWozqD0y_cfA%2C%2C%24%24%24KV7Zm7jp1zFNMKWLikDVSAPrQKY3qEh1-KXwS-CEVuApolpON6x6c7Zfz4x4ech6%3Fr%3Dhttps%253A%252F%252Fcredly.com%252Fcredit%252F13581706%26t%3D1545242570%26c%3Dli" class="linkedin share ">LinkedIn</a></li> 
                        </ul>
                    </div>
                    <ul class="assertion-verification"> 
                        <li><a id="assertion-verification-trigger" href="#assertion-verification" class="blue-btn dialog-trigger" data-member-badge-id="13581706">Verify</a></li> 
                    </ul>
                </div>
                <div class="right_col">
                    <h3 class="title"><?php echo $rec->achievement_title;?></h3>        
                    <p>
                        <?php echo $achievement->post_content;?>
                    </p>
                    <div class="issue_date"><?php echo _e( 'Issue Date', 'badgeos' );?>: <?php echo date( get_option('date_format'), strtotime( $rec->dateadded ) );?></div>
                    <div class="evidence"><?php echo _e( 'Evidence', 'badgeos' );?>: <a href="javascript:;" onclick="alert('hello')"><?php echo _e( 'View Evidence', 'badgeos' );?></a></div>
                </div>
            </div>
        <?php

        // get the post content and format the badge display
        $achievement = get_post( $atts['id'] );
        $output = '';

        // If we're dealing with an achievement post
        if ( badgeos_is_achievement( $achievement ) ) {
            $output .= '<div id="badgeos-single-achievement-container" class="badgeos-single-achievement">';  // necessary for the jquery click handler to be called
            $output .= badgeos_render_achievement( $achievement );
            $output .= '</div>';
        }
    }
	// Return our rendered achievement
	return $output;
}