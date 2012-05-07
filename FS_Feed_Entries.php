<?php

/**
 * Manages FS feeds
 * @package FS
 */

class FS_Feed_Entries
{
    /**
     * The default number of entries to output when listing feed entries
     * @var int
     */
    private static $_defaultLatestAmount = 5;

    /**
     * Returns a formatted <ul> containing a list of the feed entries passed in $entries
     * @param array $entries An array of feed entries, supplied as wordpress post objects
     * @return string
     */
    public static function getFormattedList( $entries )
    {
        if( count( $entries ) === 0 )
        {
            return '';
        }

        ob_start();

        echo '<ul class="fs-list">';

        foreach( $entries as $entry )
        {
            $date = new DateTime( $entry->post_date );
            $url = get_post_meta( $entry->ID, 'fs_feed_entry_url', true );
            echo new FS_Feed_Entry( $entry->post_content, $entry->post_parent, $date->format( DATE_RSS ), $entry->post_title, $url );
        }

        echo '</ul>';

        return ob_get_clean();
    }

    /**
     * Returns a formatted list of the latest entries
     * @param int $amount The amount of entries to show
     * @return string
     */
    public static function getLatest( $amount )
    {
        $entries = self::_getLatestWPPosts( $amount );
        return self::getFormattedList( $entries );
    }

    /**
     * Callback for [latest_feed_entries] shortcode. 
     * @return string
     */
    public static function handleShortcode( $params )
    {
        $params = shortcode_atts
        (
            array( 'amount' => self::$_defaultLatestAmount ),
            $params
        );

        return self::getLatest( $params[ 'amount' ] );
    }

    /**
     * Removes feed entries from the database
     * @param int $feedID The of the feed which entries should be deleted., or false for all
     */
    public static function delete( $feedID = false )
    {
        $args = array
        (
            'post_type' => 'fs_feed_entry',
            'posts_per_page' => -1
        );

        if( $feedID !== false )
        {
            $args['post_parent'] = $feedID;
        }

        $entries = get_posts( $args );

        foreach( $entries as $entry )
        {
            wp_delete_post( $entry->ID, true );
        }
    }

    /**
     * Returns an array of WP posts, containing $amount number of the most recent entries
     * @param int $amount The amount of entries to return
     * @return array
     */
    private static function _getLatestWPPosts( $amount )
    {
        return get_posts
        (
            array
            (
                'post_type' => 'fs_feed_entry',
                'posts_per_page' => $amount,
            )
        );
    }
}