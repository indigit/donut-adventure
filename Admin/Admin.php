<?php

declare(strict_types=1);

namespace DonutAdventure\Admin;

use DonutAdventure\Includes\Settings;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wowvendor.com
 * @since      1.0.0
 *
 * @package    DonutAdventure
 * @subpackage DonutAdventure/Admin
 * @author     Kirill S. <kirill@indigit.info>
 */
class Admin
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
     * @param string $pluginSlug The name of this plugin.
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
        add_action( 'add_meta_boxes', [ $this, 'addDonutMetabox' ], 10, 2 );
        add_action( 'save_post', [ $this, 'savePost' ], 10, 3 );
    }

    /**
     * Add Donut metabox
     *
     * @param string  $post_type Post type.
     * @param WP_Post $post      Post object.
     */
    public function addDonutMetabox( string $post_type, WP_Post $post ): void
    {
        $nonce_action = "{$this->pluginSlug}_{$post->ID}";
        wp_create_nonce( $nonce_action );
        add_meta_box(
            id: $this->pluginSlug . '-metabox',
            title: 'Donut Adventure',
            callback: [ $this, 'displayDonutMetabox' ],
            screen: [ 'post', 'page' ],
            priority: 'high',
            context: 'side',
            callback_args: [ 'nonce_action' => $nonce_action ],
        );
    }

    /**
     * Display Donut metabox
     *
     * @param WP_Post $post
     * @param array $args
     * @return void
     */
    public function displayDonutMetabox( WP_Post $post, array $args ): void
    {
        $show    = $this->settings->isIDInArray( $post->ID );
        $nonce   = wp_nonce_field( $args['args']['nonce_action'], "_{$this->pluginSlug}nonce", echo: false );
        $checked = $show ? 'checked' : '';
        $html    = <<<HTML
            <label>
                <input name="{$this->pluginSlug}_show" type="checkbox" {$checked} />
                <span>Show the game on this page</span>
            </label>
            {$nonce}
        HTML;

        echo $html;
    }

    /**
     * Fires once a post has been saved.
     *
     * @param int      $post_ID Post ID.
     * @param WP_Post  $post    Post object.
     * @param bool     $update  Whether this is an existing post being updated.
     */
    public function savePost( int $post_ID, WP_Post $post, bool $update ): void
    {
        if ( in_array( $post->post_type, [ 'post', 'page' ], true ) ) {

            if ( defined( 'DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
                return;
            }
    
            if ( ! isset( $_POST["_{$this->pluginSlug}nonce"] ) || ! wp_verify_nonce( $_POST["_{$this->pluginSlug}nonce"], "{$this->pluginSlug}_{$post->ID}" ) ) {
                return;
            }

            $post_type = get_post_type_object( $post->post_type );
            if ( ! current_user_can( $post_type->cap->edit_post, $post_ID ) ) {
                return;
            }

            $in = $this->settings->isIDInArray( $post_ID );
            if ( isset( $_POST["{$this->pluginSlug}_show"] ) && ! $in ) {
                $this->settings->addID( $post_ID );
            }
            elseif ( ! isset( $_POST["{$this->pluginSlug}_show"] ) && $in ) {
                $this->settings->removeID( $post_ID );
            }

        }
    }

}
