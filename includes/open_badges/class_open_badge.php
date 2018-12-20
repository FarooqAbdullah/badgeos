<?php
/**
 * Opne Badge Class
 *
 * @package BadgeOS
 * @subpackage Classes
 * @author Learning Times
 * @license http://www.gnu.org/licenses/agpl.txt GNU AGPL v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Open_Badge {

	public $badgeos_assertion_page_id 	= 0; 		
	public $badgeos_json_page_id 		= 0; 
	public $badgeos_issuer_page_id 		= 0;
	public $badgeos_evidence_page_id 	= 0;

    /**
	 * Instantiate the Opne Badge.
	 */
	public function __construct() { 

		$this->badgeos_assertion_page_id	= get_option( 'badgeos_assertion_url' );
		$this->badgeos_json_page_id 		= get_option( 'badgeos_json_url' );
		$this->badgeos_evidence_page_id		= get_option( 'badgeos_evidence_url' );
		$this->badgeos_issuer_page_id       = get_option( 'badgeos_issuer_url' );
		
		add_filter('template_include',array( $this,'badgeos_template_pages' ) );
    }
	
	/**
	 * Override the current displayed template.
	 * 
	 * @param $page_template
	 * 
	 * return $page_template
	 */ 
	function badgeos_template_pages( $page_template ) {
		
		global $post;
		$achievement_id 	= isset( $_REQUEST[ 'bg' ] ) ? $_REQUEST[ 'bg' ]:0;
		$entry_id 			= isset( $_REQUEST[ 'eid' ] ) ? $_REQUEST[ 'eid' ]:0;  
		$user_id 			= isset( $_REQUEST[ 'uid' ] ) ? $_REQUEST[ 'uid' ]:0;
		
		if( $post->ID == $this->badgeos_assertion_page_id ) {
			$this->badgeos_generate_assertion( $user_id, $entry_id, $achievement_id );
			exit;
		} else if( $post->ID == $this->badgeos_json_page_id ) {
			$this->badgeos_generate_badge( $user_id, $entry_id, $achievement_id );
			exit;
		} else if( $post->ID == $this->badgeos_issuer_page_id ) {
			$this->badgeos_generate_issuer( $user_id, $entry_id, $achievement_id );
			exit;
		}
		
		return $page_template;
	}

	/**
	 * Generates assertion json.
	 * 
	 * @param $user_id
	 * @param $entry_id
	 * @param $achievement_id
	 * 
	 * @return none
	 */ 
	public function badgeos_generate_assertion( $user_id, $entry_id, $achievement_id ) {
		
		$badge = get_post( $achievement_id );
		if( $badge ) {

			$post_content = $badge->post_content;
			$post_title = $badge->post_title;
			
			$date = new DateTime();

			$thumbnail_url = get_the_post_thumbnail_url( $achievement_id, 'full' );

			$badgeos_assertion_url 	= get_permalink( $this->badgeos_assertion_page_id );
			$badgeos_assertion_url  = add_query_arg( 'bg', $achievement_id, $badgeos_assertion_url );
			$badgeos_assertion_url  = add_query_arg( 'eid', $entry_id, $badgeos_assertion_url );
			$badgeos_assertion_url  = add_query_arg( 'uid', $user_id, $badgeos_assertion_url );

			$badgeos_json_url 		= get_permalink( $this->badgeos_json_page_id );
			$badgeos_json_url  		= add_query_arg( 'bg', $achievement_id, $badgeos_json_url );
			$badgeos_json_url  		= add_query_arg( 'eid', $entry_id, $badgeos_json_url );
			$badgeos_json_url  		= add_query_arg( 'uid', $user_id, $badgeos_json_url );

			$badgeos_evidence_url 	= get_permalink( $this->badgeos_evidence_page_id );
			$badgeos_evidence_url   = add_query_arg( 'bg', $achievement_id, $badgeos_evidence_url );
			$badgeos_evidence_url   = add_query_arg( 'eid', $entry_id, $badgeos_evidence_url );
			$badgeos_evidence_url   = add_query_arg( 'uid', $user_id, $badgeos_evidence_url );

			$identity_id = $this->get_identity_id( $user_id, $entry_id, $achievement_id );

			$result = array(
				'@context'	=> 'https://w3id.org/openbadges/v2',
				'type'	=> 'Assertion',
				'id'	=> $badgeos_assertion_url,
				'recipient'	=> array(
					'type'	=> 'email',
					'hashed'	=> false,
					'salt'	=> 'BadgeOSOBI',
					'identity'	=> $identity_id
				),
				'badge'	=> $badgeos_json_url,
				'evidence'	=> $badgeos_evidence_url,
				'issuedOn'	=> $date->format('Y-m-d\TH:i:sP'),
				'image'	=> $thumbnail_url,
				'verification'	=> array(
					'type'	=> 'HostedBadge',
					'verificationProperty'	=> 'id',
				),
			);

			wp_send_json( $result );
		}
	}

	/**
	 * Generates badge json.
	 * 
	 * @param $user_id
	 * @param $entry_id
	 * @param $achievement_id
	 * 
	 * @return none
	 */ 
	public function badgeos_generate_badge( $user_id, $entry_id, $achievement_id ) {
				
		$badge = get_post( $achievement_id );
		if( $badge ) {

			$post_content = $badge->post_content;
			$post_title = $badge->post_title;
			
			$thumbnail_url = get_the_post_thumbnail_url( $achievement_id, 'full' );

			$badgeos_json_url  = get_permalink( $this->badgeos_json_page_id );
			$badgeos_json_url  = add_query_arg( 'bg', $achievement_id, $badgeos_json_url );
			$badgeos_json_url  = add_query_arg( 'eid', $entry_id, $badgeos_json_url );
			$badgeos_json_url  = add_query_arg( 'uid', $user_id, $badgeos_json_url );

			$badgeos_issuer_url = get_permalink( $this->badgeos_issuer_page_id );

			$badge_link = get_permalink( $achievement_id );

			$result =  array(
				'@context'		=> 'https://w3id.org/openbadges/v2',
				'type'			=> 'BadgeClass',
				'id'			=> $badgeos_json_url,
				'name'			=> $post_title,
				'image'			=> $thumbnail_url,
				'description'	=> $post_content,
				'criteria'		=> $badge_link,
				'issuer'		=> $badgeos_issuer_url,
				'tags'			=> array()
			);
			
			wp_send_json( $result );
		}
	}

	/**
	 * Generates issuer json.
	 * 
	 * @param $user_id
	 * @param $entry_id
	 * @param $achievement_id
	 * 
	 * @return none
	 */ 
	public function badgeos_generate_issuer( $user_id, $entry_id, $achievement_id ) {
		
		$blog_title = get_bloginfo( 'name' );
		$admin_email = get_bloginfo( 'admin_email' );
		$blog_url = get_site_url();

		$result =  array(
			'@context'	=> 'https://w3id.org/openbadges/v2',
			'type'	=> 'Issuer',
			'id'	=> get_permalink(),
			'name'	=> $blog_title,
			'url'	=> $blog_url,
			'email'	=> $admin_email
		);
		
		wp_send_json( $result );
	}


	/**
	 * Bake a badge if the Badge Baking is enabled
	 * 
	 * @param $entry_id
	 * @param $user_id
	 * @param $achievement_id
	 * 
	 * @return none
	 */ 
	public function bake_user_badge( $entry_id, $user_id, $achievement_id ) {
		global $wpdb;
		$badge = get_post( $achievement_id );
		if( $badge ) {
			
			$open_badge_enable_baking       	= ( get_post_meta( $achievement_id, '_open_badge_enable_baking', true ) ? get_post_meta( $achievement_id, '_open_badge_enable_baking', true ) : 'false' );
			$open_badge_criteria            	= ( get_post_meta( $achievement_id, '_open_badge_criteria', true ) ? get_post_meta( $achievement_id, '_open_badge_criteria', true ): '' );
			$open_badge_include_evidence    	= ( get_post_meta( $achievement_id, '_open_badge_include_evidence', true ) ? get_post_meta( $achievement_id, '_open_badge_include_evidence', true ) : 'false' );
			$open_badge_expiration  			= ( get_post_meta( $achievement_id, '_open_badge_expiration', true ) ? get_post_meta( $achievement_id, '_open_badge_expiration', true ) : '0' );
			if( $open_badge_enable_baking == 'true' ) {
				
				$date = new DateTime();
				
				$dirs = wp_upload_dir();
				$basedir = trailingslashit( $dirs[ 'basedir' ] );
				$baseurl = trailingslashit( $dirs[ 'baseurl' ] );

				$user_badge_directory = $basedir.'user_badges';
				if ( ! file_exists( $user_badge_directory ) && ! is_dir( $user_badge_directory ) ) {
					mkdir( $user_badge_directory );         
				}
				
				$user_badge_directory = trailingslashit( $user_badge_directory ).$user_id;
				if ( ! file_exists( $user_badge_directory ) && ! is_dir( $user_badge_directory ) ) {
					mkdir( $user_badge_directory );         
				}
				$user_badge_directory = trailingslashit( $user_badge_directory );

				$post_content = $badge->post_content;
				$post_title = $badge->post_title;
				
				$thumbnail_url 	= get_the_post_thumbnail_url( $achievement_id, 'full' );
				$userdirectory 	= '/images/users/';
				$directory 		= badgeos_get_directory_path();
				$directory 		.= $userdirectory;

				$directory_url 	= badgeos_get_directory_url();
				$directory_url .= $userdirectory;
				$badge_link 	= get_permalink( $achievement_id );

				$badgeos_assertion_url 	= get_permalink( $this->badgeos_assertion_page_id );
				$badgeos_assertion_url  = add_query_arg( 'bg', $achievement_id, $badgeos_assertion_url );
				$badgeos_assertion_url  = add_query_arg( 'eid', $entry_id, $badgeos_assertion_url );
				$badgeos_assertion_url  = add_query_arg( 'uid', $user_id, $badgeos_assertion_url );
				
				$badgeos_json_url 		= get_permalink( $this->badgeos_json_page_id );
				$badgeos_json_url 		= add_query_arg( 'bg', $achievement_id, $badgeos_json_url );
				$badgeos_json_url  		= add_query_arg( 'eid', $entry_id, $badgeos_json_url );
				$badgeos_json_url  		= add_query_arg( 'uid', $user_id, $badgeos_json_url );

				$badgeos_issuer_url 	= get_permalink( $this->badgeos_issuer_page_id );
				$badgeos_issuer_url 	= add_query_arg( 'bg', $achievement_id, $badgeos_issuer_url );
				$badgeos_issuer_url  	= add_query_arg( 'eid', $entry_id, $badgeos_issuer_url );
				$badgeos_issuer_url  	= add_query_arg( 'uid', $user_id, $badgeos_issuer_url );

				$badgeos_evidence_url 	= get_permalink( $this->badgeos_evidence_page_id );
				$badgeos_evidence_url 	= add_query_arg( 'bg', $achievement_id, $badgeos_evidence_url );
				$badgeos_evidence_url  	= add_query_arg( 'eid', $entry_id, $badgeos_evidence_url );
				$badgeos_evidence_url  	= add_query_arg( 'uid', $user_id, $badgeos_evidence_url );

				$identity_id = $this->get_identity_id( $user_id, $entity_id, $achievement_id );

				$json = array(
					'@context'	=> 'https://w3id.org/openbadges/v2',
					'type'	=> 'Assertion',
					'id'	=> $badgeos_assertion_url,
					'recipient'	=> array(
						'type'	=> 'email',
						'hashed'	=> false,
						'salt'	=> 'BadgeOSOBI',
						'identity'	=> $identity_id
					),
					'badge'	=> array(
						'@context'	=> 'https://w3id.org/openbadges/v2',
						'type'	=> 'BadgeClass',
						'id'	=> "a-".$achievement_id,
						'name'	=> $post_title,
						'image'	=> $thumbnail_url,
						'description' => $post_content,
						'criteria'	=> $badge_link,
						'issuer'	=> $badgeos_issuer_url,
						'tags'	=> array(),
					),
					'issuedOn'	=> $date->format('Y-m-d\TH:i:sP'),
					'image'	=> $thumbnail_url,
					'verification'	=> array(
						'type'	=> 'HostedBadge',
						'verificationProperty'	=> 'id',
					),
				);
				
				// 2016-11-08T11:20:30+00:00
				if( $open_badge_include_evidence == 'true' ) {
					$json[ 'evidence' ] = $badgeos_evidence_url;
				}
				$result = $this->bake_image( $thumbnail_url, $json );

				$filename = ( 'Badge-'.$entry_id . '-' . $achievement_id ).'.png';
				file_put_contents( $user_badge_directory.$filename ,$result);
				
				badgeos_run_database_script();

				$table_name = $wpdb->prefix . 'badgeos_achievements';
				$data_array = array( 'baked_image' => $filename );
                $where = array( 'entry_id' => absint(  $entry_id ) );
                $wpdb->update( $table_name , $data_array, $where );
			}
		}
	}

	/**
	 * Return the Identity
	 * 
	 * @param $user_id
	 * @param $entry_id
	 * @param $achievement_id
	 * 
	 * @return none
	 */ 
	function get_identity_id( $user_id, $entity_id, $achievement_id ) {
		$user = get_user_by( 'ID', $user_id );
		return $user->user_email;
	}

	/**
	 * Bake a badge if the Badge Baking is enabled
	 *
	 * @param int $image        The ID of the user earning the achievement
	 * @param int $assertion The ID of the achievement being earned
	 */
	function bake_image($image, array $assertion) {
		$png = file_get_contents($image);

		// You may wish to perform additional checks to ensure the file
		// is a png file here.
		$embed = [
			'openbadges',
			'',
			'',
			'',
			'',
			(string) json_encode($assertion),
		];

		// Glue with null-bytes.
		$data = implode("\0", $embed);

		// Make the CRC.
		$crc = pack("N", crc32('iTXt' . $data));

		// Put it all together.
		$final = pack("N", strlen($data)) . 'iTXt' . $data . $crc;

		// What's the length?
		$length = strlen($png);

		// Put this all at the end, before IEND.
		// We _should_ be removing all other iTXt blobs with keyword openbadges
		// before writing this out.
		$png = substr($png, 0, $length - 12) . $final . substr($png, $length - 12,12);

		return $png;
	}
}
?>