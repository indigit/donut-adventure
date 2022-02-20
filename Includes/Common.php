<?php

declare(strict_types=1);

namespace DonutAdventure\Includes;

use DonutAdventure\Includes\Settings;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The common-specific functionality of the plugin.
 *
 * @link       https://wowvendor.com
 * @since      1.0.0
 *
 * @package    DonutAdventure
 * @subpackage DonutAdventure/Includes
 * @author     Kirill S. <kirill@indigit.info>
 */
class Common
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

    }
}
