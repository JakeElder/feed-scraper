<?php

/**
 * Encapsulates any FS_Feed_Entry functionality specific to wordpress administration
 * @package FS
 */

class FS_Feed_Entry_Admin
{
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