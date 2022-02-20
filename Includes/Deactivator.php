<?php

declare(strict_types=1);

namespace DonutAdventure\Includes;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Fired during plugin deactivation.
 *
 * @link       https://wowvendor.com
 * @since      1.0.0
 * @package    DonutAdventure
 * @subpackage DonutAdventure/Includes
 * @author     Kirill S. <kirill@indigit.info>
 */
class Deactivator
{
    /**
     * Deactivation
     *
     * @since    1.0.0
     */
    public static function deactivate(): void
    {
        // Permission check
        if ( ! current_user_can( 'activate_plugins' ) )
        {
            // Localization class hasn't been loaded yet.
            wp_die( 'You don\'t have proper authorization to deactivate a plugin!' );
        }

        self::onDeactivation();
    }

    /**
	 * The actual tasks performed during deactivation of the plugin.
	 */
	public static function onDeactivation()
	{

	}
}
