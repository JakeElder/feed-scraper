<?php

/**
 * Manages FS feeds
 * @package FS
 */

class FS_Feeds
{
    /**
     * Returns a list of all feeds
     * @return array
     */
    public static function get()
    {
        return get_posts( array( 'post_type' => 'fs_feed' ) );
    }

    /**
     * Downloads any new entries if necessary
     */
    public static function scrape()
    {
        foreach( self::get() as $post )
        {
            $feed = new FS_Feed( $post->ID );
            $feed->scrape();
        }
    }
}