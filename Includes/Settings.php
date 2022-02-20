<?php

declare(strict_types=1);

namespace DonutAdventure\Includes;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Plugin settings.
 *
 * @link       https://wowvendor.com
 * @since      1.0.0
 * @package    DonutAdventure
 * @subpackage DonutAdventure/Includes
 * @author     Kirill S. <kirill@indigit.info>
 */
class Settings
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
     * Debug state
     *
     * @var boolean
     */
    private static bool $debug = false;

    /**
     * Options name
     *
     * @var string
     */
    public static string $opt_name = 'donut_options';

    /**
     * Plugin default options
     *
     * @var array
     */
    public static array $default_options = [
        'table_name' => 'da_highscores',
        'posts' => [],
    ];

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->version    = DONUT_ADVENTURE_VERSION;
        $this->pluginSlug = DONUT_ADVENTURE_SLUG;
        $this->initHooks();
    }

    /**
     * Create the objects and register all the hooks of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function initHooks(): void
    {
        // add_action( 'init', [ $this, 'addOptions' ] );
    }

    /**
     * Get debug state
     *
     * @return boolean
     */
    public function getDebug(): bool
    {
        return self::$debug;
    }

    /**
     * Add options
     *
     * @return void
     */
    public static function addOptions(): void
    {
        $options = get_option( self::$opt_name );

        if ( $options === false ) {
            add_option( self::$opt_name, self::$default_options );
        } else {
            $options = wp_parse_args( $options, self::$default_options );
            update_option( self::$opt_name, $options, true );
        }
    }

    /**
     * Check is post_id in array
     *
     * @param integer $post_id
     * @return boolean
     */
    public function isIDInArray( int $post_id ): bool
    {
        $options = get_option( Settings::$opt_name );
        return in_array( $post_id, $options['posts'], true );
    }

    /**
     * Add post ID to array
     *
     * @param integer $post_id
     * @return void
     */
    public function addID( int $post_id ): void
    {
        $options = get_option( Settings::$opt_name );
        array_push( $options['posts'], $post_id );
        update_option( Settings::$opt_name, $options, true );
    }

    /**
     * Remove post ID from array
     *
     * @param integer $post_id
     * @return void
     */
    public function removeID( int $post_id ): void
    {
        $options = get_option( Settings::$opt_name );
        $options['posts'] = array_diff( $options['posts'], [ $post_id ] );
        update_option( Settings::$opt_name, $options, true );
    }

    /**
     * Get Donut table name
     *
     * @return string
     */
    public function getTableName(): string
    {
        return get_option( Settings::$opt_name )['table_name'];
    }

}
