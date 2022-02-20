<?php

declare(strict_types=1);

namespace DonutAdventure;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The PSR-4 autoloader project-specific implementation.
 *
 *
 *
 * @since             1.0.0
 * @package           DonutAdventure
 *
 * @param   $className The fully-qualified class name.
 * @return void
 * @link https://www.php-fig.org/psr/psr-4/examples/
 */
spl_autoload_register( function ( string $classname ): void
{
    // Plugin-specific namespace prefix
    $prefix = 'DonutAdventure\\';

    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/';

    // Does the class use the namespace prefix?
    $prefix_length = strlen( $prefix );
    if ( strncmp( $prefix, $classname, $prefix_length ) !== 0)
    {
        return;
    }

    // Get the relative class name
    $relative_classname = substr( $classname, $prefix_length );

    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file_path = $base_dir . str_replace( '\\', '/', $relative_classname ) . '.php';

    // If the file exists, require it
    if ( file_exists( $file_path ) )
    {
        require_once $file_path;
    }
    else
    {
        exit( esc_html( "The file $classname.php could not be found!" ) );
    }
});
