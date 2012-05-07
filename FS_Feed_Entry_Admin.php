<?php

/**
 * Encapsulates any FS_Feed_Entry functionality specific to wordpress administration
 * @package FS
 */

class FS_Feed_Entry_Admin
{
    /**
     * Echo's the content for custom columns added to WP's feed entry management table
     * @param string $columnName
     * @param int $ID The ID of the post being rendered into the table
     */
    public static function addColumnContent( $columnName, $ID )
    {
        $post = get_post( $ID );
        $post = get_post( $post->post_parent );

        if( $columnName === 'scraped_from' )
        {
            echo '<a href="'. get_edit_post_link( $post->ID, true ) .'">'. $post->post_title .'</a>';
        }
    }

    /**
     * Adds columns to WP admin's feed entry viewing table
     * @param array &$columns WP's array of table columns
     */
    public static function addColumns( &$columns )
    {
        $new = array_slice( $columns, 0, 1, true );
        $new += array( 'scraped_from' => 'Feed' );
        $new += array_slice( $columns, 1, null, true );
        $columns = $new;
    }

    /**
     * Specifies the messages used when performing various post type actions
     * @param array &$messages A reference to WP's message array
     */
    public function filterPostUpdatedMessages( &$messages )
    {
        $messages['fs_feed_entry'] = array
        (
            1 => 'Feed Entry updated',
            4 => 'Feed Entry updated',
            6 => 'Feed Entry saved',
            7 => 'Feed Entry saved'
        );

        // Use generic post messages for less common/unused messages
        $messages['fs_feed_entry'] += $messages['post'];
    }
}