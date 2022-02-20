<?php

declare(strict_types=1);

namespace DonutAdventure\Includes;

use DonutAdventure\Includes\Settings;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Fired during plugin activation.
 *
 * @link       https://wowvendor.com
 * @since      1.0.0
 * @package    DonutAdventure
 * @subpackage DonutAdventure/Includes
 * @author     Kirill S. <kirill@indigit.info>
 */
class Activator
{
    /**
     * Define the plugins that our plugin requires to function.
     * The key is the plugin name, the value is the plugin file path.
     *
     * @since 1.0.0
     * @var string[]
     */
    private const REQUIRED_PLUGINS = array(
        //'Hello Dolly' => 'hello-dolly/hello.php',
        //'WooCommerce' => 'woocommerce/woocommerce.php'
    );

    /**
     * Activation
     *
     * @since    1.0.0
     */
    public static function activate(): void
    {
        // Permission check
        if ( ! current_user_can( 'activate_plugins' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            // Localization class hasn't been loaded yet.
            wp_die( 'You don\'t have proper authorization to activate a plugin!' );
        }

        self::checkDependencies();
        self::onActivation();
    }

    /**
     * Check whether the required plugins are active.
     * 
     * @since      1.0.0
     */
    private static function checkDependencies(): void
    {
        foreach ( self::REQUIRED_PLUGINS as $pluginName => $pluginFilePath )
        {
            if ( ! is_plugin_active( $pluginFilePath ) )
            {
                // Deactivate the plugin.
                deactivate_plugins( plugin_basename( __FILE__ ) );
                wp_die( "This plugin requires {$pluginName} plugin to be active!" );
            }
        }
    }
    
    /**
	 * The actual tasks performed during activation of the plugin.
	 */
	public static function onActivation()
	{
		Settings::addOptions();
        self::createTable();
    }

    /**
     * Create a table
     *
     * @return void
     */
    private static function createTable(): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table_name = $wpdb->prefix . get_option( Settings::$opt_name )['table_name'];
        $charset_collate = $wpdb->get_charset_collate();

        $query = <<<SQL
            CREATE TABLE {$table_name} (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
                `guest_id` VARCHAR(13) NOT NULL DEFAULT '',
                `game_date` DATE NOT NULL,
                `rock_position` SMALLINT(2) UNSIGNED NOT NULL,
                `rock_size` CHAR(8) NOT NULL,
                `jump_position` SMALLINT(2) UNSIGNED NOT NULL,
                `game_time` INT(4) UNSIGNED NOT NULL,
                `win` TINYINT(1) NOT NULL,
                PRIMARY KEY (`id`),
                INDEX `user` (`user_id` ASC, `id` DESC) VISIBLE,
                INDEX `guest` (`guest_id` ASC, `id` DESC) VISIBLE)
            {$charset_collate}
        SQL;

        dbDelta( $query );
    }
}
