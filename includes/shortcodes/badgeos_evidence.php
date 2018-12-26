<?php

/**
 * Adding the Open Graph in the Language Attributes
 */
function add_opengraph_doctype( $output ) {
    return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
}
add_filter('language_attributes', 'add_opengraph_doctype');

/**
 * Change the document title
 */
function badgeos_update_document_title( $old_title ){
    global $post, $wpdb;

    /**
     * if it is not a post or a page
     */
    if ( !is_singular())
        return;
    
    $achievement_id 	= sanitize_text_field( $_REQUEST['bg'] );
    $entry_id  	        = sanitize_text_field( $_REQUEST['eid'] );
    $user_id  	        = sanitize_text_field( $_REQUEST['uid'] );
    badgeos_run_database_script();

    $recs = $wpdb->get_results( "select * from ".$wpdb->prefix."badgeos_achievements where entry_id='".$entry_id."'" );
    if( count( $recs ) > 0 ) {
        $rec = $recs[0];
        return $rec->achievement_title;
    } 
}
add_filter("pre_get_document_title", "badgeos_update_document_title");

/**
 * Lets add Open Graph Meta Info
 */
function insert_fb_in_head() {
    global $post, $wpdb;

    /**
     * if it is not a post or a page
     */
    if ( !is_singular())
        return;
    
    $achievement_id 	= sanitize_text_field( $_REQUEST['bg'] );
    $entry_id  	        = sanitize_text_field( $_REQUEST['eid'] );
    $user_id  	        = sanitize_text_field( $_REQUEST['uid'] );
    badgeos_run_database_script();

    $recs = $wpdb->get_results( "select * from ".$wpdb->prefix."badgeos_achievements where ID='".$achievement_id."' and  entry_id='".$entry_id."' and  user_id='".$user_id."'" );
    if( count( $recs ) > 0 ) {

        $rec = $recs[0];

        $sharedURL = get_permalink( $achievement_id );
        $badge  = get_post( $achievement_id );
        
        $sharedURL  = add_query_arg( 'bg', $achievement_id, $sharedURL );
        $sharedURL  = add_query_arg( 'eid', $entry_id, $sharedURL );
        $sharedURL  = add_query_arg( 'uid', $user_id, $sharedURL );
        $sharedURL  = add_query_arg( 'tn', time(), $sharedURL );     
        $from_title = get_bloginfo( 'name' );
        
        $user_to = get_user_by( 'ID', $rec->user_id );
		if( $user_to ) {
			$user_email = $user_to->user_email;
		}

        /**
         * Get current page title
         */
        $sharedTitle = $rec->achievement_title;
        echo '<meta property="fb:admins" content="'.$user_email.'"/>';
        echo '<title>' . $sharedTitle . '</title>';
        echo '<meta property="og:title" content="' . $sharedTitle . '"/>';
        echo '<meta property="og:description" content="' . $badge->post_content . '"/>';
        echo '<meta property="og:type" content="article"/>';
        echo '<meta property="og:url" content="' . $sharedURL . '"/>';
        echo '<meta property="og:site_name" content="'.$from_title.'"/>';

        $dirs = wp_upload_dir();
        $baseurl = trailingslashit( $dirs[ 'baseurl' ] );
        $basedir = trailingslashit( $dirs[ 'basedir' ] );
        $badge_directory = trailingslashit( $basedir.'user_badges/'.$user_id );
        $badge_url = trailingslashit( $baseurl.'user_badges/'.$user_id );
        if( ! empty( $rec->baked_image ) && file_exists( $badge_directory.$rec->baked_image ) ) {
            echo '<meta property="og:image" content="' . $badge_url.$rec->baked_image . '"/>';
        } else {
            $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $achievement_id ), 'full' );
            echo '<meta property="og:image" content="' . esc_attr( $thumbnail_src[0] ) . '"/>';
        }
        echo "";

    }
}
add_action( 'wp_head', 'insert_fb_in_head', 5 );

/**
 * Register the [badgeos_achievement] shortcode.
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
 * @param  array $atts Shortcode attributes.
 * @return string 	   HTML markup.
 */
function badgeos_achievement_evidence_shortcode( $atts = array() ) {

    global $wpdb;

    /**
     * get the post id
     */
	$atts = shortcode_atts( array(
	  'show_sharing_opt' => 'Yes',
	), $atts, 'badgeos_evidence' );
    
    $achievement_id 	= sanitize_text_field( $_REQUEST['bg'] );
    $entry_id  	        = sanitize_text_field( $_REQUEST['eid'] );
    $user_id  	        = sanitize_text_field( $_REQUEST['uid'] );
    
    /**
     * return if entry_id not specified
     */
	if ( empty( $entry_id ) )
      return;
    
    /**
     * return if user_id not specified
     */
    if ( empty( $user_id ) )
      return;
    
    $output = '';
    
    badgeos_run_database_script();

    $recs = $wpdb->get_results( "select * from ".$wpdb->prefix."badgeos_achievements where ID='".$achievement_id."' and  entry_id='".$entry_id."' and  user_id='".$user_id."'" );
    if( count( $recs ) > 0 ) {

        $rec = $recs[0];

        $sharedURL = get_permalink( $achievement_id );
        $sharedURL  = add_query_arg( 'bg', $achievement_id, $sharedURL );
        $sharedURL  = add_query_arg( 'eid', $entry_id, $sharedURL );
        $sharedURL  = add_query_arg( 'uid', $user_id, $sharedURL );
        $sharedURL  = add_query_arg( 'tn', time(), $sharedURL );
        $sharedURL = urlencode( $sharedURL );     

        /**
         * Get current page title
         */
        $sharedTitle = $rec->achievement_title;

        $user = get_user_by( 'ID', $user_id );
        $achievement = get_post( $rec->ID );
        wp_enqueue_style( 'badgeos-front' );
        wp_enqueue_script( 'badgeos-achievements' );
        
        $dirs = wp_upload_dir();
        $baseurl = trailingslashit( $dirs[ 'baseurl' ] );
        $basedir = trailingslashit( $dirs[ 'basedir' ] );
        $badge_directory = trailingslashit( $basedir.'user_badges/'.$user_id );
        $badge_url = trailingslashit( $baseurl.'user_badges/'.$user_id );

        $twitterURL = 'https://twitter.com/intent/tweet?text='.$sharedTitle.'&amp;url='.$sharedURL;
        $facebookURL = 'https://www.facebook.com/sharer/sharer.php?u='.$sharedURL;
        $linkedInURL = 'https://www.linkedin.com/shareArticle?mini=true&url='.$sharedURL.'&amp;title='.$sharedTitle;
        ?>
            <div class="evidence_main">
                <div class="left_col">
                    <?php if( ! empty( $rec->baked_image ) && file_exists( $badge_directory.$rec->baked_image ) ) { ?>
                        <img src="<?php echo $badge_url.$rec->baked_image;?>" with="100%" />
                    <?php } else { ?>
                        <?php echo badgeos_get_achievement_post_thumbnail( $achievement_id, 'full' ); ?>
                    <?php  } ?>
                    <div class="user_name"><?php echo $user->display_name;?></div>
                    <div class="social_buttons">
                        <ul> 
                            <li><a href="<?php echo $facebookURL;?>" onclick="window.open(this.href, 'facebookwindow','left=20,top=20,width=700,height=500,toolbar=0,resizable=1'); return false;"><i class="btm_facebook"></i></a></li>
                            <li><a href="<?php echo $twitterURL;?>" onclick="window.open(this.href, 'twitterwindow','left=20,top=20,width=600,height=300,toolbar=0,resizable=1'); return false;"><i class="btm_twitter"></i></a></li>
                            <li><a href="<?php echo $linkedInURL;?>" onclick="window.open(this.href, 'linkedInwindow','left=20,top=20,width=600,height=550,toolbar=0,resizable=1'); return false;"><i class="btm_linkedin"></i></a></li>
                        </ul>
                    </div>
                    <div class="verification"> 
                        <input id="open-badgeos-verification" href="javascript:;" data-bg="<?php echo $achievement_id;?>" data-eid="<?php echo $entry_id;?>" data-uid="<?php echo $user_id;?>" class="verify-open-badge" value="<?php echo _e( 'Verify', 'badgeos' );?>" type="button" /> 
                    </div>
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
            <div id="open-badge-id" style="display:none">
                <div class="verification-results">
                    <ul id="verification-res-list">
                    </ul>
                </div>
            </div>
        <?php
    }
    
    /**
     * Return our rendered achievement
     */
	return $output;
}