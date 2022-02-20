<?php

declare(strict_types=1);

namespace DonutAdventure\Includes;

use DonutAdventure\Includes\Settings;
use WP_REST_Request;
use WP_REST_Response;
use Requests_Cookie;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Donut Adventure REST API
 *
 * @link       https://wowvendor.com
 * @since      1.0.0
 * @package    DonutAdventure
 * @subpackage DonutAdventure/Includes
 * @author     Kirill S. <kirill@indigit.info>
 */
class API
{
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     */
    protected string $pluginSlug;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     */
    protected string $version;

    /**
     * The settings of this plugin.
     *
     * @since    1.0.0
     */
    private Settings $settings;

    /**
     * User ID
     *
     * @var integer
     */
    private int $user_id;

    /**
     * Guest ID
     *
     * @var string
     */
    private string $guest_id;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct( string $pluginSlug, string $version, Settings $settings )
    {
        $this->version    = $version;
        $this->pluginSlug = $pluginSlug;
        $this->settings   = $settings;
        $this->user_id    = 0;
        $this->guest_id   = '';
    }

    /**
     * Register all the hooks of this class.
     *
     * @since    1.0.0
     * @param   $isAdmin    Whether the current request is for an administrative interface page.
     */
    public function initializeHooks(): void
    {
        add_action( 'rest_api_init', [ $this, 'apiInit' ] );
    }

    /**
     * Init REST API
     *
     * @return void
     */
    public function apiInit(): void
    {
        $namespace = $this->pluginSlug . '/v2';

        register_rest_route( $namespace, '/highscores/', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'getResult' ],
                'permission_callback' => [ $this, 'validateRequest' ],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'addResult' ],
                'permission_callback' => [ $this, 'validateRequest' ],
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [ $this, 'deleteResult' ],
                'permission_callback' => [ $this, 'validateRequest' ],
            ],
        ] );
    }

    /**
     * Validate request
     *
     * @param WP_REST_Request $request
     * @return boolean
     */
    public function validateRequest( WP_REST_Request $request ): bool
    {
        if ( is_user_logged_in() ) {
            $this->user_id = get_current_user_id();
            return true;
        }
        elseif ( isset( $_COOKIE[$this->pluginSlug] ) && preg_match( '~^[0-9a-f]{13}$~', (string) $_COOKIE[$this->pluginSlug], $guid ) ) {
            $this->guest_id = $guid[0];
            return true;
        }
        
        return false;
    }

    /**
     * Get high scores
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function getResult( WP_REST_Request $request ): WP_REST_Response
    {
        $score = new Score( $this->settings, $this->user_id, $this->guest_id );

        return new WP_REST_Response( [ 'success' => true, 'data' => $score->get() ], 200 );
    }

    /**
     * Add score result
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function addResult( WP_REST_Request $request ): WP_REST_Response
    {
        if ( ! $request->is_json_content_type() ) {
            return new WP_REST_Response( array( 'message' => 'Wrong MIME' ), 400 );
        }

        $data  = $request->get_json_params();
        $score = new Score( $this->settings, $this->user_id, $this->guest_id, $data );
        
        if ( $score->save() ) {
            return new WP_REST_Response( [ 'success' => true, 'data' => $score->get() ], 200 );
        }

        return new WP_REST_Response( [ 'success' => false ], 200 );
    }

    /**
     * Remove high scores
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function deleteResult( WP_REST_Request $request ): WP_REST_Response
    {
        $score = new Score( $this->settings, $this->user_id, $this->guest_id );

        return new WP_REST_Response( [ 'success' => $score->delete() ], 200 );
    }
}
