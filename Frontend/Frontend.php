<?php

declare(strict_types=1);

namespace DonutAdventure\Frontend;

use DonutAdventure\Includes\Settings;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The frontend functionality of the plugin.
 *
 * @link       https://wowvendor.com
 * @since      1.0.0
 *
 * @package    DonutAdventure
 * @subpackage DonutAdventure/Frontend
 * @author     Kirill S. <kirill@indigit.info>
 */
class Frontend
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     */
    private string $pluginSlug;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     */
    private string $version;

    /**
     * The settings of this plugin.
     *
     * @since    1.0.0
     */
    private Settings $settings;

    /**
     * Initialize the class and set its properties.
     *
     * @since   1.0.0
     * @param string $pluginSlug The name of the plugin.
     * @param string $version The version of this plugin.
     * @param Settings $settings The Settings object.
     */
    public function __construct( string $pluginSlug, string $version, Settings $settings )
    {
        $this->pluginSlug = $pluginSlug;
        $this->version    = $version;
        $this->settings   = $settings;
    }

    /**
     * Register all the hooks of this class.
     *
     * @since    1.0.0
     */
    public function initializeHooks(): void
    {
        add_action( 'wp', [ $this, 'setCookie' ] );
        add_filter( 'the_content', [ $this, 'displayGame' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueueStyles' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueueScripts' ] );
    }

    /**
     * Output game html
     *
     * @param string $content Content of the current post.
     * @return string Content of the current post.
     */
    public function displayGame( string $content ): string
    {
        if ( is_singular( [ 'post', 'page' ] ) && $this->settings->isIDInArray( get_the_ID() ) ) {
            load_template( path_join( DONUT_ADVENTURE_PATH, 'Frontend/templates/donut-adventure.php' ) );
        }

    	return $content;
    }

    /**
     * Set cookie for guest
     *
     * @return void
     */
    public function setCookie(): void
    {
        if ( is_main_query() && ! is_user_logged_in() && is_singular( [ 'post', 'page' ] ) && $this->settings->isIDInArray( get_the_ID() ) ) {

            if ( ! isset( $_COOKIE[$this->pluginSlug] ) ) {

                setcookie( 
                    name: $this->pluginSlug,
                    value: uniqid(),
                    expires_or_options: time() + YEAR_IN_SECONDS,
                    path: '/',
                    secure: true,
                    httponly: true,
                );

            }

        }
    }

    /**
     * Register the stylesheets for the frontend side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueStyles(): void
    {
        if ( is_singular( [ 'post', 'page' ] ) && $this->settings->isIDInArray( get_the_ID() ) ) {
            $style_id       = $this->pluginSlug . '-style';
            $style_filename = 'style.min.css';
            $style_url      = path_join( DONUT_ADVENTURE_URL, 'assets/css/' . $style_filename );
    
            wp_register_style( $style_id, $style_url, [], $this->version, 'all' );
            wp_enqueue_style( $style_id );
        }
    }

    /**
     * Register the JavaScript for the frontend side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueScripts(): void
    {
        if ( is_singular( [ 'post', 'page' ] ) && $this->settings->isIDInArray( get_the_ID() ) ) {
            $app_id          = $this->pluginSlug . '-app';
            $script_id       = $this->pluginSlug . '-script';
            $app_filename    = 'app.js';
            $script_filename = 'script.js';
            $app_url         = path_join( DONUT_ADVENTURE_URL, 'assets/js/' . $app_filename );
            $script_url      = path_join( DONUT_ADVENTURE_URL, 'assets/js/' . $script_filename );
    
            wp_register_script( $app_id, $app_url, [], $this->version, true );
            wp_register_script( $script_id, $script_url, [ $app_id ], $this->version, true );
            wp_enqueue_script( $script_id );
    
            wp_localize_script( $script_id, 'wp_api_data', [
                'donut_rest' => esc_url_raw( rest_url() . $this->pluginSlug . '/v2/highscores/' ),
                'wp_nonce'   => wp_create_nonce( 'wp_rest' ),
            ] );
        }
    }

}
