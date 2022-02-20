<?php

declare(strict_types=1);

namespace DonutAdventure\Includes;

use DateTime;
use DateTimeZone;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Score
 *
 * @link       https://wowvendor.com
 * @since      1.0.0
 *
 * @package    DonutAdventure
 * @subpackage DonutAdventure/Includes
 * @author     Kirill S. <kirill@indigit.info>
 */
class Score
{
    /**
     * Keys
     *
     * @var array
     */
    private static array $keys = [
        'rockPos',
        'rockSize',
        'time',
        'jumpPos',
        'won'
    ];

    /**
     * Score array
     *
     * @var array
     */
    private array $score;

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
     * DB Table Name
     *
     * @var string
     */
    private string $table_name;


    /**
     * Initialize the class and set its properties.
     *
     * @since   1.0.0
     * @param array $score Score data from API
     */
    public function __construct( Settings $settings, int $user_id = 0, string $guest_id = '', array $score = [] )
    {
        $this->settings      = $settings;
        $this->user_id       = $user_id;
        $this->guest_id      = $guest_id;
        global $wpdb;
        $this->table_name    = $wpdb->prefix . $settings->getTableName();
        if ( ! empty( $score ) ) {
            $this->addScore( $score );
        }
    }

    /**
     * Add score
     *
     * @param array $score
     * @return void
     */
    public function addScore( array $score ): void
    {
        $this->score = array_filter( $score, function ( $k ) {
            return in_array( $k, self::$keys, true );
        }, ARRAY_FILTER_USE_KEY );
    }

    /**
     * Score validation
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        if ( ! isset( $this->score ) )
            return false;

        $result = match ( true ) {
            count( self::$keys ) !== count( $this->score )                      => false,
            ! is_bool( $this->score['won'] )                                    => false,
            preg_match( '~^\d{1,3}x\d{1,3}$~', $this->score['rockSize'] ) !== 1 => false,
            absint( $this->score['time'] ) !== $this->score['time']             => false,
            $this->score['time'] > DAY_IN_SECONDS * 1000                        => false,
            absint( $this->score['rockPos'] ) !== $this->score['rockPos']       => false,
            $this->score['rockPos'] > 5000                                      => false,
            absint( $this->score['jumpPos'] ) !== $this->score['jumpPos']       => false,
            $this->score['jumpPos'] > 5000                                      => false,
            default                                                             => true,
        };

        return $result;
    }

    /**
     * Set types
     *
     * @param array $result
     * @return array
     */
    private function setTypes( array $result ): array
    {
        foreach ( $result as &$row ) {
            foreach ( $row as $key => &$field ) {
                if ( in_array( $key, [ 'game_time', 'jump_position', 'rock_position' ] ) )
                    $field = (int) $field;
                if ( $key === 'game_date' )
                    $field = ( new DateTime( $field, new DateTimeZone( 'UTC' ) ) )->format( 'm.d.Y' );
                if ( $key === 'win' )
                    $field = (bool) $field;
            }
        }

        return $result;
    }

    /**
     * Put score in DB
     *
     * @return boolean
     */
    public function save(): bool
    {
        if ( ! $this->isValid() )
            return false;

        global $wpdb;

        $query = <<<SQL
            INSERT INTO {$this->table_name}
            ( `user_id`, `guest_id`, `game_date`, `rock_position`, `rock_size`, `jump_position`, `game_time`, `win` )
            VALUES ( %d, %s, UTC_DATE(), %d, %s, %d, %d, %d )
        SQL;

        $args = [
            $this->user_id,
            $this->guest_id,
            $this->score['rockPos'],
            $this->score['rockSize'],
            $this->score['jumpPos'],
            $this->score['time'],
            (int) $this->score['won'],
        ];

        $prepared = $wpdb->prepare( $query, $args );

        return $wpdb->query( $prepared ) !== false;
    }

    /**
     * Get High Scores
     *
     * @return array
     */
    public function get(): array
    {
        global $wpdb;
        $where = $this->user_id
            ? $wpdb->prepare( '`user_id` = %d', [ $this->user_id ] )
            : $wpdb->prepare( '`guest_id` = %s', [ $this->guest_id ] );

        $query = <<<SQL
            SELECT `game_date`, `rock_position`, `rock_size`, `jump_position`, `game_time`, `win`
            FROM {$this->table_name}
            WHERE {$where}
            ORDER BY `id` DESC
            LIMIT 100
        SQL;

        $results = $wpdb->get_results( $query, ARRAY_A );

        return $results ? $this->setTypes( $results ) : [];
    }

    /**
     * Remove High Scores
     *
     * @return boolean
     */
    public function delete(): bool
    {
        global $wpdb;
        $where = $this->user_id
            ? $wpdb->prepare( '`user_id` = %d', [ $this->user_id ] )
            : $wpdb->prepare( '`guest_id` = %s', [ $this->guest_id ] );

        $query = <<<SQL
            DELETE FROM {$this->table_name}
            WHERE {$where}
        SQL;

        $result = $wpdb->query( $query );

        return $result !== false;
    }

}
