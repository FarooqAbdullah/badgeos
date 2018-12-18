<?php
/**
 * Opne Badge Class
 *
 * @package BadgeOS
 * @subpackage Classes
 * @author Wooninjas
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
	
	 
	function badgeos_template_pages( $page_template ) {
		
		global $post;
		$bg = isset( $_REQUEST[ 'bg' ] ) ? $_REQUEST[ 'bg' ]:0;

		if( $post->ID == $this->badgeos_assertion_page_id ){
			$this->badgeos_generate_assertion( $bg );
			exit;
		} else if( $post->ID == $this->badgeos_json_page_id ) {
			$this->badgeos_generate_badge( $bg );
			exit;
		} else if( $post->ID == $this->badgeos_issuer_page_id ) {
			$this->badgeos_generate_issuer( $bg );
			exit;
		}
		
		return $page_template;
	}

	public function badgeos_generate_assertion( $badge_id ) {
		
		$badge = get_post( $badge_id );
		if( $badge ) {

			$post_content = $badge->post_content;
			$post_title = $badge->post_title;
			
			$date = new DateTime();

			$thumbnail_url = get_the_post_thumbnail_url( $badge_id, 'full' );

			$badgeos_assertion_url 	= get_permalink( $this->badgeos_assertion_page_id );
			$badgeos_assertion_url  = add_query_arg( 'bg', $badge_id, $badgeos_assertion_url );
			
			$badgeos_json_url 		= get_permalink( $this->badgeos_json_page_id );
			$badgeos_json_url 		= add_query_arg( 'bg', $badge_id, $badgeos_json_url );
			
			$badgeos_evidence_url 	= get_permalink( $this->badgeos_evidence_page_id );
			$badgeos_evidence_url 	= add_query_arg( 'bg', $badge_id, $badgeos_evidence_url );

			$result = array(
				'@context'	=> 'https://w3id.org/openbadges/v2',
				'type'	=> 'Assertion',
				'id'	=> $badgeos_assertion_url,
				'recipient'	=> array(
					'type'	=> 'email',
					'hashed'	=> true,
					'salt'	=> 'BadgeOSOBI',
					'identity'	=> 'sha256$876f53c4bd1278924e56a2d5f2e972280ee555a5b8dfb7528512d181c40df3a4'
				),
				'badge'	=> $badgeos_json_url,
				'evidence'	=> $badgeos_evidence_url,
				'issuedOn'	=> $date->format('Y-m-d\TH:i:s'),
				'image'	=> $thumbnail_url,
				'verification'	=> array(
					'type'	=> 'HostedBadge',
					'verificationProperty'	=> 'id',
				),
			);

			wp_send_json( $result );
		}
	}

	public function badgeos_generate_badge( $badge_id ) {
				
		$badge = get_post( $badge_id );
		if( $badge ) {

			$post_content = $badge->post_content;
			$post_title = $badge->post_title;
			
			$thumbnail_url = get_the_post_thumbnail_url( $badge_id, 'full' );

			$badgeos_json_url = get_permalink( $this->badgeos_json_page_id );
			$badgeos_json_url = add_query_arg( 'bg', $badge_id, $badgeos_json_url );

			$badgeos_issuer_url = get_permalink( $this->badgeos_issuer_page_id );

			$badge_link = get_permalink( $badge_id );

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
	 * generates the issue json
	 *
	 * @param int $badge_id
	 */
	public function badgeos_generate_issuer( $badge_id=0 ) {
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
	 * @param int $user_id        The ID of the user earning the achievement
	 * @param int $achievement_id The ID of the achievement being earned
	 */
	public function bake_user_badge( $user_id, $achievement_id ) {
		
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

				$badgeos_json_url 		= get_permalink( $this->badgeos_json_page_id );
				$badgeos_json_url 		= add_query_arg( 'bg', $achievement_id, $badgeos_json_url );

				$badgeos_issuer_url 	= get_permalink( $this->badgeos_issuer_page_id );
				$badgeos_issuer_url 	= add_query_arg( 'bg', $achievement_id, $badgeos_issuer_url );

				$badgeos_evidence_url 	= get_permalink( $this->badgeos_evidence_page_id );
				$badgeos_evidence_url 	= add_query_arg( 'bg', $achievement_id, $badgeos_evidence_url );

				$json = array(
					'@context'	=> 'https://w3id.org/openbadges/v2',
					'type'	=> 'Assertion',
					'id'	=> $badgeos_assertion_url,
					'recipient'	=> array(
						'type'	=> 'email',
						'hashed'	=> true,
						'salt'	=> 'BadgeOSOBI',
						'identity'	=> 'sha256$876f53c4bd1278924e56a2d5f2e972280ee555a5b8dfb7528512d181c40df3a4'
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
					'issuedOn'	=> $date->format('Y-m-d\TH:i:s'),
					'image'	=> $thumbnail_url,
					'verification'	=> array(
						'type'	=> 'HostedBadge',
						'verificationProperty'	=> 'id',
					),
				);
				// 2018-12-17T18:43:42.000000
				
				// 2016-11-08T11:20:30+00:00
				if( $open_badge_include_evidence == 'true' ) {
					$json[ 'evidence' ] = $badgeos_evidence_url;
				}
				$result = $this->bake_image( $thumbnail_url, $json );
				$filename = ( $achievement_id . "-" . time() ).'.png';
				file_put_contents( $user_badge_directory.$filename ,$result);
				exit;
			}
		}
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