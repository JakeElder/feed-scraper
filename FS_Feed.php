<?php

/**
 * Feed specific functionality
 * @package FS
 */

class FS_Feed
{
    /**
     * The ID of the FS_Feed, as stored in the WP database
     * @var int
     */
    private $_ID;

    /**
     * The last date the feed was scraped
     * @var mixed The date of the last successful scrape, stored as a DateTime object, or null if no scrape has been performed
     */
    private $_lastScrape = null;

    /**
     * The url of the feed
     * @var string
     */
    private $_url;

    /**
     * Whether or not the url can be read as xml
     * @var bool
     */
    private $_urlValid = false;

    /**
     * Instantiates an instance of FS_Feed
     * @param int $ID The ID of the feed as stored in the WP DB
     */
    public function __construct( $ID = null )
    {
        if( $ID === null )
        {
            return;
        }

        if( $this->_populateFromWPID( $ID ) )
        {
            $this->_ID = $ID;
        }
    }

    /**
     * Returns an array of entries scraped from this feed
     * @return array
     */
    public function getEntries()
    {
       return get_posts
       (
            array
            (
                'post_parent' => $this->_ID,
                'post_type' => 'fs_feed_entry',
                'posts_per_page' => -1
            )
        );
    }

    /**
     * Returns _lastScrape
     * @return mixed
     */
    public function getLastScrape()
    {
        return $this->_lastScrape;
    }

    /**
     * Returns whether or not the feed url is valid
     * @return bool
     */
    public function isUrlValid()
    {
        return $this->_urlValid;
    }

    /**
     * Hits the feed, saving any new entries since the last scrape
     */
    public function scrape()
    {
        $feedValid = true;

        $contents = @file_get_contents( $this->_url );

        if( $contents === false )
        {
            $feedValid = false;
        }

        if( $feedValid === true )
        {
            try
            {
                $feed = @new SimpleXmlElement( $contents );
            }
            catch( Exception $e )
            {
                $feedValid = false;
            }
        }

        if( $feedValid === false )
        {
            $this->_urlValid = false;
            $this->update();
            return false;
        }

        foreach( $feed->channel->item as $entry )
        {
            $entryDate = dateCreateFromRSSPubDate( ( string ) $entry->pubDate );

            if( $entryDate > $this->_lastScrape )
            {
                $FSEntry = new FS_Feed_Entry
                (
                    strip_tags( ( string ) $entry->description ),
                    $this->_ID,
                    ( string ) $entry->pubDate,
                    ( string ) $entry->title,
                    ( string ) $entry->link
                );

                $FSEntry->insert();
            }
        }

        $this->_urlValid = true;
        $this->_lastScrape = new DateTime;

        $this->update();

        return true;
    }

    /**
     * Sets the url
     * @param string $url 
     */
    public function setUrl( $url )
    {
        if( $url !== $this->_url )
        {
            $this->_url = $url;
            $this->scrape();
        }
    }

    /**
     * Updates any post meta stored in the WP DB
     * @return bool True on success, false on failure
     */
    public function update()
    {
        if( is_null( $this->_lastScrape ) )
        {
            $lastScrape = null;
        }
        else
        {
            $lastScrape = $this->_lastScrape->format( 'Y-m-d H:i:s' );
        }

        update_post_meta( $this->_ID, 'fs_feed_url', $this->_url );
        update_post_meta( $this->_ID, 'fs_feed_url_valid', $this->_urlValid );
        update_post_meta( $this->_ID, 'fs_feed_last_scrape', $lastScrape );
    }

    /**
     * Populates property values based on information retrieved from the WP DB
     * @param int $ID The ID of the post in the WP database
     * @return bool True on success, false on failure ( IE post not found in DB )
     */
    private function _populateFromWPID( $ID )
    {
        $wpPost = get_post( $ID );

        if( is_null( $wpPost ) )
        {
            return false;
        }

        $lastScrape = get_post_meta( $ID, 'fs_feed_last_scrape', true );

        if( $lastScrape === '' )
        {
            $lastScrape = null;
        }
        else
        {
            $lastScrape = new DateTime( $lastScrape );
        }

        $this->_lastScrape = $lastScrape;
        $this->_url = get_post_meta( $ID, 'fs_feed_url', true );
        $this->_urlValid = ( bool ) get_post_meta( $ID, 'fs_feed_url_valid', true );

        return true;
    }

    /**
     * Registers the custom post type entries will be stored as
     */
    public static function registerWPPostType()
    {
        register_post_type
        (
            'fs_feed',
            array
            (
                'label' => 'Feeds',
                'labels' => array
                (
                    'name' => 'Feeds',
                    'singular_name' => 'Feed',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Feed',
                    'edit_item' => 'Edit Feed',
                    'new_item' => 'New Feed',
                    'view_item' => 'View Feed',
                    'search_items' => 'Search Feeds',
                    'not_found' => 'No Feeds Found',
                    'not_found_in_trash' => 'No Feeds Found In Trash',
                    'parent_item_colon' => 'Parent Feeds:',
                    'edit' => 'Edit',
                    'view' => 'View Feed'
                ),
                'public' => false,
                'show_ui' => true,
                'supports' => array( 'title', 'thumbnail' )
            )
        );
    }
}