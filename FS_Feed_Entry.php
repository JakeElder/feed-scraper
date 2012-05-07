<?php

/**
 * FS_Feed_Entry functionality
 * @package FS
 */

class FS_Feed_Entry
{
    /**
     * The excerpt of feed the entry
     * @param string
     */
    private $_excerpt;

    /**
     * The date the entry was published
     * @param DateTime
     */
    private $_pubDate;

    /**
     * The title of the feed entry
     * @param string
     */
    private $_title;

    /**
     * The ( full, external ) url to the full post
     * @param string
     */
    private $_url;

    /**
     * Instantiates an instance of FS_Feed_Entry
     * @param string $excerpt The excerpt taken from the feed entry
     * @param string $feedID The ID of the feed this entry came from
     * @param string $pubDate The published date of the entry. Formatted as DATE_RSS
     * @param string $title The title of the entry
     * @param string $url The external url to the full post
     */
    public function __construct( $excerpt, $feedID, $pubDate, $title, $url )
    {
        $this->_excerpt = $excerpt;
        $this->_feedID = $feedID;
        $this->_pubDate = dateCreateFromRSSPubDate( $pubDate );
        $this->_title = $title;
        $this->_url = $url;
    }

    /**
     * Returns a formatted list item element with the entries data
     * @return string
     */
    public function __toString()
    {
        ob_start();

        $link = '<a href="'. $this->_url .'" rel="nofollow">%s</a>';

        ?>

        <li>
            <header>
                <h3><?php printf( $link, $this->_title ); ?></h3>
                <div class="meta"><strong><?php _e( 'Date posted', 'feed-scraper' ); ?></strong>: <time><?php echo $this->_pubDate->format( 'd/m/Y' ); ?></time></div>
            </header>
            <p><?php echo $this->_excerpt; ?></p>
            <footer>
                <?php printf( $link, __( 'Read more', 'feed-scraper' ) .' &raquo;' ); ?>
            </footer>
        </li>

        <?php

        return ob_get_clean();
    }

    /**
     * Saves the feed entry as a wordpress post
     * @return bool Whether the entry was successfully saved or not
     */
    public function insert()
    {
        $postDateGMT = $this->_pubDate;
        $postDateGMT->setTimezone( new Datetimezone( 'GMT' ) );

        $post = array
        (
            'post_content' => $this->_excerpt,
            'post_date' => $this->_pubDate->format( 'Y-m-d H:i:s' ),
            'post_date_gmt' => $postDateGMT->format( 'Y-m-d H:i:s' ),
            'post_parent' => $this->_feedID,
            'post_status' => 'publish',
            'post_title' => $this->_title,
            'post_type' => 'fs_feed_entry'
        );

        remove_action( 'save_post', array( 'FS', 'onWPSavePost' ) );
        $postID = wp_insert_post( $post );
        add_action( 'save_post', array( 'FS', 'onWPSavePost' ) );

        if( $postID === 0 )
        {
            return false;
        }

        add_post_meta( $postID, 'fs_feed_entry_url', $this->_url, true );

        return true;
    }

    /**
     * Registers the custom post type entries will be stored as
     */
    public static function registerWPPostType()
    {
        register_post_type
        (
            'fs_feed_entry',
            array
            (
                'label' => 'Feed Entries',
                'labels' => array
                (
                    'name' => 'Feed Entries',
                    'singular_name' => 'Feed Entry',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Feed Entry',
                    'edit_item' => 'Edit Feed Entry',
                    'new_item' => 'New Feed Entry',
                    'view_item' => 'View Feed Entry',
                    'search_items' => 'Search Feed Entries',
                    'not_found' => 'No Feed Entries Found',
                    'not_found_in_trash' => 'No Feed Entries Found In Trash',
                    'parent_item_colon' => 'Parent Feed Entries:',
                    'edit' => 'Edit',
                    'view' => 'View Feed Entry'
                ),
                'public' => false,
                'show_ui' => true
            )
        );
    }

}