<?php

/**
 * Encapsulates any FS_Feed functionality specific to wordpress administration
 * @package FS
 */

class FS_Feed_Admin
{
    /**
     * Registers a custom meta box write panel for fs_feed administration page
     */
    public static function addMetaBoxes()
    {
        global $post_ID;

        add_meta_box
        (
            // ID
            'fs_feed_url',
            // Title
            'Feed Url',
            // HTML Generation callback
            array( 'FS_Feed_Admin', 'urlMetaBoxHTML' ),
            // Post type
            'fs_feed',
            // Context, where the box is shown
            'normal',
            // Priority
            'high'
        );

        add_meta_box
        (
            // ID
            'fs_feed_info',
            // Title
            'Feed Info',
            // HTML Generation callback
            array( 'FS_Feed_Admin', 'infoMetaBoxHTML' ),
            // Post type
            'fs_feed',
            // Context, where the box is shown
            'normal',
            // Priority
            'high'
        );
    }

    /**
     * Outputs HTML for the url meta box
     */
    public static function urlMetaBoxHTML()
    {
        global $post_ID;
        $feed = new FS_Feed( $post_ID );

        ?>
            <div class="fs_meta">

                <label for="fs_feed_url">Feed url:</label>
                <input type="text" id="fs_feed_url" name="fs_feed_url" value="<?php echo get_post_meta( $post_ID, 'fs_feed_url', true ); ?>" />

            </div>

        <?php
    }

    /**
     * Outputs HTML for the info meta box
     */
    public static function infoMetaBoxHTML()
    {
        global $post_ID;
        $feed = new FS_Feed( $post_ID );

        $lastScrape = $feed->getLastScrape();

        if( is_null( $lastScrape ) )
        {
            $lastScrape = 'N/A';
            $nextScrape = 'N/A';
        }
        else
        {
            $lastScrape = $lastScrape->format( 'd/m/Y H:i:s' );
            $nextScrape = FS::nextScheduledScrape();
        }

        ?>
            <div class="fs_meta">

                <dl>
                    <div class="row">
                        <dt>Valid feed:</dt>
                        <dd><?php echo $feed->isUrlValid() ? '<span class="yes">Yes</span>' : '<span class="no">No</span>'; ?></dd>
                    </div>
                    <div class="row">
                        <dt>Entries scraped:</dt>
                        <dd><?php echo count( $feed->getEntries() ); ?></dd>
                    </div>
                    <div class="row">
                        <dt>Last scrape:</dt>
                        <dd><?php echo $lastScrape; ?></dd>
                    </div>
                    <div class="row">
                        <dt>Next scrape:</dt>
                        <dd><?php echo $nextScrape ?></dd>
                    </div>
                </dl>

            </div>

        <?php
    }

    /**
     * Performs any functions necessary on feed deletion
     */
    public static function handleDelete( $postID )
    {
        $post = get_post( $postID );
        if( $post->post_type === 'fs_feed' )
        {
            FS_Feed_Entries::delete( $postID );
        }
    }


    /**
     * Performs any functions necessary after a feed has been saved
     */
    public static function handleSave( $postID )
    {
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE === true )
        {
            return;
        }

        if( wp_is_post_revision( $postID ) )
        {
            return;
        }

        if( intval( $postID ) === 0 )
        {
            return;
        }

        if( ! array_key_exists( 'fs_feed_url', $_POST ) )
        {
            return;
        }

        $feed = new FS_Feed( $postID );
        $feed->setUrl( $_POST['fs_feed_url'] );
    }

    /**
     * Specifies the messages used when performing various post type actions
     * @param array $messages A reference to WP's message array
     */
    public function filterPostUpdatedMessages( &$messages )
    {
        $messages['fs_feed'] = array
        (
            1 => 'Feed updated',
            4 => 'Feed updated',
            6 => 'Feed saved',
            7 => 'Feed saved'
        );

        // Use generic post messages for less common/unused messages
        $messages['fs_feed'] += $messages['post'];
    }
}