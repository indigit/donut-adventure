<?php

/**
 * Fired when the plugin is uninstalled.
 * 
 * @link       https://wowvendor.com
 * @since      1.0.0
 *
 * @package    Donut_Adventure
 */

declare(strict_types=1);

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Permission check
if ( ! current_user_can( 'activate_plugins' ) )
{
    wp_die( 'You don\'t have proper authorization to delete a plugin!' );
}

deleteOptions();

/**
 * Delete the plugin's options.
 *
 * @since    1.0.0
 */
function deleteOptions(): void
{
    delete_option( 'donut-adventure-options' );
}
