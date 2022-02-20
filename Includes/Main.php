<?php

declare(strict_types=1);

namespace DonutAdventure\Includes;

use DonutAdventure\Admin\Admin;
use DonutAdventure\Frontend\Frontend;
use DonutAdventure\Includes\Settings;
use DonutAdventure\Includes\Common;
use DonutAdventure\Includes\API;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The core plugin class.
 *
 * @link       https://wowvendor.com
 * @since      1.0.0
 * @package    DonutAdventure
 * @subpackage DonutAdventure/Includes
 * @author     Kirill S. <kirill@indigit.info>
 */
class Main
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
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->version    = DONUT_ADVENTURE_VERSION;
        $this->pluginSlug = DONUT_ADVENTURE_SLUG;
    }

    /**
     * Create the objects and register all the hooks of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function defineHooks(): void
    {

        $settings = new Settings();

        /**
         * Admin objects - Register all of the hooks related to the admin area functionality of the plugin.
         */
        if ( is_admin() )
        {
            $admin = new Admin( $this->pluginSlug, $this->version, $settings );
            $admin->initializeHooks();
        }
        /**
         * Frontend objects - Register all of the hooks related to the public-facing functionality of the plugin.
         */
        else
        {
            $frontend = new Frontend( $this->pluginSlug, $this->version, $settings );
            $frontend->initializeHooks();
        }

        /**
         * Common objects - Register all of the hooks related to the public and addmin areas of the plugin.
         */
        $common = new Common( $this->pluginSlug, $this->version, $settings );
        $common->initializeHooks();
        
        $api = new API( $this->pluginSlug, $this->version, $settings );
        $api->initializeHooks();
    }

    /**
     * Run the plugin.
     *
     * @since    1.0.0
     */
    public function run(): void
    {
        $this->defineHooks();
    }
}
