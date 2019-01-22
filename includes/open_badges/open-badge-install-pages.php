<?php

/**
 *  Shows a notice to install/create the required open badge pages
 */
function badgeos_install_pages_if_not_installed(){

    $assertion_page = get_permalink( get_option( 'badgeos_assertion_url' ) );
    $json_page      = get_permalink( get_option( 'badgeos_json_url' ) );
    $issuer_page    = get_permalink( get_option( 'badgeos_issuer_url' ) );
    $evidence_page  = get_permalink( get_option( 'badgeos_evidence_url' ) );
    $embed_page     = get_permalink( get_option( 'badgeos_embed_url' ) );

    if( empty( $assertion_page ) || empty( $json_page ) || empty( $issuer_page ) || empty( $evidence_page ) || empty( $embed_page ) ) {
        $class = 'notice is-dismissible error';
        $config_link = 'admin-post.php?action=badgeos_config_pages';
        $message = __( 'Please, click <a href="'.$config_link.'">here</a> to configure/create new open badge pages.', 'ldbu_addon' );
        printf( '<div id="message" class="%s"> <p>%s</p></div>', $class, $message );
    }
    
}
add_action( 'admin_notices', 'badgeos_install_pages_if_not_installed' );

/**
 *  Shows a notice to install/create the required open badge pages
 */
function badgeos_config_pages_func(){
    
    $assertion_page = get_option( 'badgeos_assertion_url' );
    if( !isset( $assertion_page ) || intval( $assertion_page ) < 1 || empty( get_permalink( $assertion_page ) ) ) {
        $assertion_page_id = wp_insert_post ([
            'post_type' =>'page',        
            'post_name' => 'Assertion Page',
            'post_title' => 'Assertion Page',
            'post_content' => 'This page will display assertion json only.',
            'post_status' => 'publish',
        ]);

        update_option( 'badgeos_assertion_url', sanitize_text_field( $assertion_page_id ) );
    }

    $json_page = get_option( 'badgeos_json_url' );
    if( !isset( $json_page ) || intval( $json_page ) < 1 || empty( get_permalink( $json_page ) ) ) {
        
        $json_page_id = wp_insert_post ([
            'post_type' =>'page',        
            'post_name' => 'Badge Json',
            'post_title' => 'Badge Json',
            'post_content' => 'This page will display badge json only.',
            'post_status' => 'publish',
        ]);

        update_option( 'badgeos_json_url', sanitize_text_field( $json_page_id ) );
    }

    $issuer_page = get_option( 'badgeos_issuer_url' );
    if( !isset( $issuer_page ) || intval( $issuer_page ) < 1 || empty( get_permalink( $issuer_page ) ) ) {
        $issuer_page_id = wp_insert_post ([
            'post_type' =>'page',        
            'post_name' => 'Issuer Json',
            'post_title' => 'Issuer Json',
            'post_content' => 'This page will display issuer json only.',
            'post_status' => 'publish',
        ]);

        update_option( 'badgeos_issuer_url', sanitize_text_field( $issuer_page_id ) );
        
    }

    $evidence_page = get_option( 'badgeos_evidence_url' );
    if( !isset( $evidence_page ) || intval( $evidence_page ) < 1  || empty( get_permalink( $evidence_page ) ) ) {
        $evidence_page_id = wp_insert_post ([
            'post_type' =>'page',        
            'post_name' => 'Evidence Page',
            'post_title' => 'Evidence Page',
            'post_content' => '[badgeos_evidence]',
            'post_status' => 'publish',
        ]);

        update_option( 'badgeos_evidence_url', sanitize_text_field( $evidence_page_id ) );

    }

    $embed_page = get_option( 'badgeos_embed_url' );
    if( !isset( $embed_page ) || intval( $embed_page ) < 1 || empty( get_permalink( $embed_page ) ) ) {
        $embed_page_id = wp_insert_post ([
            'post_type' =>'page',        
            'post_name' => 'Embed Page',
            'post_title' => 'Embed Page',
            'post_content' => 'This page will display embed page only.',
            'post_status' => 'publish',
        ]);

        update_option( 'badgeos_embed_url', sanitize_text_field( $embed_page_id ) );
    }
    wp_redirect( 'admin.php?page=badgeos-ob' );
}
add_action( 'admin_post_badgeos_config_pages', 'badgeos_config_pages_func' );