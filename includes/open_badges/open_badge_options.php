<?php
/**
 * Open Badge Integration
 *
 * @package BadgeOS
 * @subpackage Open Badge
 * @author Learning Times
 * @license http://www.gnu.org/licenses/agpl.txt GNU AGPL v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class open_badge_options {
    
    /**
	 * Instantiate the Opne Badge.
	 */
	public function __construct() {

        // Badge Metabox
        add_action( 'add_meta_boxes', array( $this, 'open_badge_metabox_add' ) );
        add_action( 'save_post', array( $this, 'open_badge_metabox_save' ) );

        add_action ( 'wp_ajax_badgeos_validate_open_badge', array( $this, 'badgeos_validate_open_badge' ) );
        add_action ( 'wp_ajax_nopriv_badgeos_validate_open_badge', array( $this, 'badgeos_validate_open_badge' ) );
        
        add_action ( 'wp_ajax_badgeos_validate_revoked', array( $this, 'badgeos_validate_revoked' ) );
        add_action ( 'wp_ajax_nopriv_badgeos_validate_revoked', array( $this, 'badgeos_validate_revoked' ) );
        
        add_action ( 'wp_ajax_badgeos_validate_expiry', array( $this, 'badgeos_validate_expiry' ) );
        add_action ( 'wp_ajax_nopriv_badgeos_validate_expiry', array( $this, 'badgeos_validate_expiry' ) );
   }
    
    /**
     * Validate badgeos data
     */
    public function badgeos_validate_open_badge() {
        
        global $wpdb;
        
        $achievement_id = 0;
        if( ! empty( $_REQUEST['bg'] ) ) {
            $achievement_id 	= sanitize_text_field( $_REQUEST['bg'] );
        }
        
        $entry_id = 0;
        if( ! empty( $_REQUEST['eid'] ) ) {
            $entry_id  	        = sanitize_text_field( $_REQUEST['eid'] );
        }
        
        $user_id = 0;
        if( ! empty( $_REQUEST['uid'] ) ) {
            $user_id  	        = sanitize_text_field( $_REQUEST['uid'] );
        }

        badgeos_run_database_script();
        $recs = $wpdb->get_results( "select * from ".$wpdb->prefix."badgeos_achievements where entry_id='".$entry_id."'" );
        $msg = array( 'type' => 'failed', 'message' => __( 'In-valid data format.', 'badgeos' ) );
        if( count( $recs ) > 0 ) {
            $msg = array( 'type' => 'success', 'message' => __( 'Valid data format.', 'badgeos' ) );
        }  else {
            $msg = array( 'type' => 'notfound', 'message' => __( 'Badge is not found.', 'badgeos' ) );
        }
        
        wp_send_json( $msg );
    }
    
    /**
     * Check if badge is not revoked
     */
    public function badgeos_validate_revoked() {
        
        $achievement_id = 0;
        if( ! empty( $_REQUEST['bg'] ) ) {
            $achievement_id 	= sanitize_text_field( $_REQUEST['bg'] );
        }
        
        $entry_id = 0;
        if( ! empty( $_REQUEST['eid'] ) ) {
            $entry_id  	        = sanitize_text_field( $_REQUEST['eid'] );
        }
        
        $user_id = 0;
        if( ! empty( $_REQUEST['uid'] ) ) {
            $user_id  	        = sanitize_text_field( $_REQUEST['uid'] );
        }
        
        $mypost = get_post( $achievement_id );

        if( $mypost ) {
            wp_send_json(array( 'type' => 'success', 'message' => __( 'Badge is not revoked', 'badgeos' ) ));
        } else {
            wp_send_json(array( 'type' => 'error', 'message' => __( 'Badge is revoked', 'badgeos' ) ));
        }
   }

    /**
     * Check if badge is not expired
     */
    public function badgeos_validate_expiry() {
        
        global $wpdb;
        
        $achievement_id = 0;
        if( ! empty( $_REQUEST['bg'] ) ) {
            $achievement_id 	= sanitize_text_field( $_REQUEST['bg'] );
        }
        
        $entry_id = 0;
        if( ! empty( $_REQUEST['eid'] ) ) {
            $entry_id  	        = sanitize_text_field( $_REQUEST['eid'] );
        }
        
        $user_id = 0;
        if( ! empty( $_REQUEST['uid'] ) ) {
            $user_id  	        = sanitize_text_field( $_REQUEST['uid'] );
        }
 
        $open_badge_expiration       = ( get_post_meta( $post->ID, '_open_badge_expiration', true ) ? get_post_meta( $post->ID, '_open_badge_expiration', true ) : '0' );
        $open_badge_expiration_type  = ( get_post_meta( $post->ID, '_open_badge_expiration_type', true ) ? get_post_meta( $post->ID, '_open_badge_expiration_type', true ) : '0' );

        if( intval( $open_badge_expiration ) > 0 ) {
            $recs = $wpdb->get_results( "select * from ".$wpdb->prefix."badgeos_achievements where entry_id='".$entry_id."'" );
            if( count( $recs ) > 0 ) {
                $badge_date = strtotime( $recs[ 0 ]->dateadded );
                $badge_expiry = strtotime( '+'.$open_badge_expiration.' '.$open_badge_expiration_type, $badge_date );
                if( $badge_expiry > time() ){
                    $msg = array( 'type' => 'success', 'message' => __( 'Badge is not expired', 'badgeos' ) );
                } else {
                    $msg = array( 'type' => 'failed', 'message' => __( 'Badge is expired', 'badgeos' ) );
                }
            } else {
                $msg = array( 'type' => 'notfound', 'message' => __( 'Badge is not found', 'badgeos' ) );
            }
        } else {
            wp_send_json(array( 'type' => 'success', 'message' => __( 'Badge is not expired', 'badgeos' ) ));
        }

        wp_send_json($_REQUEST);
    }

    /**
     * Add a Open Badge Settings metabox on the badge CPT
     *
     * @return void
     */
    public function open_badge_metabox_add() { 

        foreach ( badgeos_get_achievement_types_slugs() as $achievement_type ) {

            add_meta_box( 'badgeos_open_badge_meta_box', __( 'Open Badge Options', 'badgeos' ), array( $this, 'open_badge_metabox_show' ), $achievement_type, 'advanced', 'default' );

        }
    }

    /**
     * Output a Open Badge Settings metabox on the badge CPT
     *
     * @return void
     */
    public function open_badge_metabox_show() {

        global $post;

        //Check existing post meta
        $open_badge_enable_baking       = ( get_post_meta( $post->ID, '_open_badge_enable_baking', true ) ? get_post_meta( $post->ID, '_open_badge_enable_baking', true ) : 'false' );
        $open_badge_criteria            = ( get_post_meta( $post->ID, '_open_badge_criteria', true ) ? get_post_meta( $post->ID, '_open_badge_criteria', true ): '' );
        $open_badge_include_evidence    = ( get_post_meta( $post->ID, '_open_badge_include_evidence', true ) ? get_post_meta( $post->ID, '_open_badge_include_evidence', true ) : 'false' );
        $open_badge_expiration          = ( get_post_meta( $post->ID, '_open_badge_expiration', true ) ? get_post_meta( $post->ID, '_open_badge_expiration', true ) : '0' );
        $open_badge_expiration_type     = ( get_post_meta( $post->ID, '_open_badge_expiration_type', true ) ? get_post_meta( $post->ID, '_open_badge_expiration_type', true ) : '0' );
        
    ?>
        <input type="hidden" name="open_badge_nonce" value="<?php echo wp_create_nonce( 'open_badge' ); ?>" />
        <table class="form-table">
            <tr valign="top">
                <td colspan="2"><?php _e( "This setting makes the earned badge for this achievement sharable on social networks, such as Facebook, Twitter, LinkedIn, Mozilla Backpack, or the badge earner's own blog or site.", 'badgeos' ); ?></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="open_badge_enable_baking"><?php _e( 'Enable Badge Baking', 'badgeos' ); ?></label></th>
                <td>
                    <select id="open_badge_enable_baking" name="open_badge_enable_baking">
                        <option value="1" <?php selected( $open_badge_enable_baking, 'true' ); ?>><?php _e( 'Yes', 'badgeos' ) ?></option>
                        <option value="0" <?php selected( $open_badge_enable_baking, 'false' ); ?>><?php _e( 'No', 'badgeos' ) ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <div id="open-badge-setting-section">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="open_badge_criteria"><?php _e( 'Criteria', 'badgeos' ); ?></label></th>
                    <td>
                        <input type="text" id="open_badge_criteria" readonly="readonly" name="open_badge_criteria" value="<?php echo $open_badge_criteria; ?>" class="widefat" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="open_badge_include_evidence"><?php _e( 'Include Evidence', 'badgeos' ); ?></label></th>
                    <td>
                        <select id="open_badge_include_evidence" name="open_badge_include_evidence">
                            <option value="1" <?php selected( $open_badge_include_evidence, 'true' ); ?>><?php _e( 'Yes', 'badgeos' ) ?></option>
                            <option value="0" <?php selected( $open_badge_include_evidence, 'false' ); ?>><?php _e( 'No', 'badgeos' ) ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="open_badge_expiration"><?php _e( 'Expiration', 'badgeos' ); ?></label></th>
                    <td>
                        <input type="number" id="open_badge_expiration" name="open_badge_expiration" value="<?php echo $open_badge_expiration; ?>" class="date_picker_class" />
                        <select id="open_badge_expiration_type" name="open_badge_expiration_type">
                            <option value="Day" <?php selected( $open_badge_expiration_type, 'Day' ); ?>><?php _e( 'Day(s)', 'badgeos' ) ?></option>
                            <option value="Month" <?php selected( $open_badge_expiration_type, 'Month' ); ?>><?php _e( 'Month(s)', 'badgeos' ) ?></option>
                            <option value="Year" <?php selected( $open_badge_expiration_type, 'Year' ); ?>><?php _e( 'Year(s)', 'badgeos' ) ?></option>
                        </select>
                        <p><?php _e( 'Enter zero or leave empty for no expiry limit.', 'badgeos' ); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th colspan="2"><b><?php _e( 'Note', 'badgeos' ); ?></b>: <?php _e( "If enabled badge baking is 'yes' then upload png images only on featured image option.", 'badgeos' ); ?></th>
                </tr>
            </table>
        </div>
    <?php
    }

    /**
     * Save our open Badge Settings metabox
     *
     * @param  int     $post_id The ID of the given post
     * 
     * @return int     Return the post ID of the post we're running on
     */
    public function open_badge_metabox_save( $post_id = 0 ) {

        // Verify nonce
        if ( ! isset( $_POST['open_badge_nonce'] ) || ! wp_verify_nonce( $_POST['open_badge_nonce'], 'open_badge' ) )
            return $post_id;

        // Make sure we're not doing an autosave
        if ( defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        // Make sure this isn't a post revision
        if ( wp_is_post_revision( $post_id ) )
            return $post_id;

        // Check user permissions
        if ( !current_user_can( 'edit_post', $post_id ) )
            return $post_id;

        // Sanitize our fields
        $fields = $this->open_badge_metabox_sanitize_fields();

        // Save our meta
        $meta = $this->open_badge_metabox_save_meta( $post_id, $fields );

        return $post_id;
    }

    /**
     * Save the meta fields from our metabox
     *
     * @param  int  $post_id   Post ID
     * @param  array  $fields  An array of fields in the metabox
     * 
     * @return bool            Return true
     */
    private function open_badge_metabox_save_meta( $post_id = 0, $fields = array() ) {

        update_post_meta( $post_id, '_open_badge_enable_baking', $fields['open_badge_enable_baking'] );
        update_post_meta( $post_id, '_open_badge_criteria', get_permalink( $post_id ) ); //$fields['open_badge_criteria']
        update_post_meta( $post_id, '_open_badge_include_evidence', $fields['open_badge_include_evidence'] );
        update_post_meta( $post_id, '_open_badge_expiration', $fields['open_badge_expiration'] );
        update_post_meta( $post_id, '_open_badge_expiration_type', $fields['open_badge_expiration_type'] );

        return true;
    }

    /**
     * Sanitize our metabox fields
     *
     * @return array  An array of sanitized fields from our metabox
     */
    private function open_badge_metabox_sanitize_fields() {

        $fields = array();

        // Sanitize our input fields
        $fields['open_badge_enable_baking']         = ( $_POST['open_badge_enable_baking'] ? 'true' : 'false' );
        $fields['open_badge_criteria']              = sanitize_text_field( $_POST['open_badge_criteria'] );
        $fields['open_badge_include_evidence']      = ( $_POST['open_badge_include_evidence'] ? 'true' : 'false' );
        $fields['open_badge_expiration']            = sanitize_text_field( $_POST['open_badge_expiration'] );
        $fields['open_badge_expiration_type']       = sanitize_text_field( $_POST['open_badge_expiration_type'] );

        return $fields;
    }
}

new open_badge_options();
?>