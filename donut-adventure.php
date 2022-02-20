<?php

/**
 *
 * @link              https://wowvendor.com
 * @since             1.0.0
 * @package           DonutAdventure
 *
 * @wordpress-plugin
 * Plugin Name:       Donut Adventure
 * Plugin URI:        https://wowvendor.com/contacts/
 * Description:       A simple web browser game
 * Version:           1.0.0
 * Requires PHP:      8.0
 * Author:            Kirill S.
 * Author URI:        https://indigit.info/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       donut-adventure
 * Domain Path:       /Languages
 */

declare(strict_types=1);

namespace DonutAdventure;

use DonutAdventure\Includes\Activator;
use DonutAdventure\Includes\Deactivator;
use DonutAdventure\Includes\Main;

if ( ! defined( 'ABSPATH' ) ) exit;

// Autoloader
require_once plugin_dir_path( __FILE__ ) . 'Autoloader.php';

/**
 * Current plugin version.
 * @link https://semver.org
 */
define( 'DONUT_ADVENTURE_VERSION', '1.0.0' );

define( 'DONUT_ADVENTURE_SLUG', 'donut-adventure' );

/**
 * Define plugin dir path
 */
define( 'DONUT_ADVENTURE_PATH', __DIR__ );

/**
 * Define plugin dir URL
 */
define( 'DONUT_ADVENTURE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin activation.
 */
register_activation_hook( __FILE__, function () {
    Activator::activate();
});

/**
 * Plugin deactivation.
 */
register_deactivation_hook( __FILE__, function () {
    Deactivator::deactivate();
});

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function runPlugin(): void
{
    $plugin = new Main();
    $plugin->run();
}
runPlugin();
