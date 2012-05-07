<?php

/**
 * Base Feed Scraper class
 * @package FS
 */

class FS
{
    /**
     * Returns the time the next scrape is scheduled for
     * @param string $format The format the date should be returned in
     * @return string
     */
    public static function nextScheduledScrape( $format = 'd/m/Y H:i:s' )
    {
        $nextScheduledTimestamp = wp_next_scheduled( 'fs_scrape_interval' );
        $nextScheduledDate = DateTime::createFromFormat( 'U', $nextScheduledTimestamp );
        return $nextScheduledDate->format( $format );
    }

    /**
     * Initialises the plugin
     */
    public static function init()
    {
        self::_registerActions();
        self::_registerFilters();
    }

    /**
     * Callback for fs_scrape_interval action
     */
    public static function onFSScrapeInterval()
    {
        FS_Feeds::scrape();
    }

    /**
     * Callback for wordpress admin_enqueue_scripts action
     */
    public static function onWPAdminEnqueueScripts()
    {
        self::_addStyleSheetLink();
    }

    /**
     * Callback for wordpress admin_menu action
     */
    public static function onWPAdminMenu()
    {
        FS_Feed_Admin::addMetaBoxes();
    }

    /**
     * Callback fro wordpress before_delete_post action
     */
    public static function onWPBeforeDeletePost( $postID )
    {
        FS_Feed_Admin::handleDelete( $postID );
    }

    /**
     * Callback for get_sample_permalink_html filter
     * @param string $return The existing HTML for the permalink area
     * @return string
     */
    public static function onWPGetSamplePermalinkHTML( $return )
    {
        global $post;

        // No need to display the permalink and related buttons if its a feed/entry
        if( $post->post_type === 'fs_feed' 
        || $post->post_Type === 'fs_feed_entry' ) 
        {
            return '';
        }
        
        return $return;
    }

    /**
     * Callback for wordpress init action
     */
    public static function onWPInit()
    {
        self::_loadTextDomain();
        self::_addLatestEntriesShortcode();

        FS_Feed::registerWPPostType();
        FS_Feed_Entry::registerWPPostType();
    }

    /**
     * Callback for wordpress manage_posts_custom_column action
     * @param string $columnName
     * @param int $ID The Id of the post the column is being generated for
     */
    public static function onWPManageFSFeedPostsCustomColumn( $columnName, $ID )
    {
        FS_Feed_Admin::addColumnContent( $columnName, $ID );
    }

    /**
     * Callback for WP's manage_fs_feed_posts_column action
     * @param array $columns WP's array of default columns
     * @return array
     */
    public static function onWPManageFSFeedPostsColumns( $columns )
    {
        FS_Feed_Admin::addColumns( &$columns );
        return $columns;
    }

    /**
     * Callback to be executed when the plugin is activated
     */
    public static function onWPPluginActivation()
    {
        self::_registerScrapeCron();
    }

    /**
     * Callback to be executed when the plugin is disabled
     */
    public static function onWPPluginDeactivation()
    {
        self::_unregisterScrapeCron();
    }

    public static function onWPPostUpdatedMessages( $messages )
    {
        FS_Feed_Admin::filterPostUpdatedMessages( &$messages );
        FS_Feed_Entry_Admin::filterPostUpdatedMessages( &$messages );

        return $messages;
    }

    /**
     * Callback for wordpress save_post action
     * @return type
     */
    public static function onWPSavePost( $postID )
    {
        FS_Feed_Admin::handleSave( $postID );
    }

    /**
     * Removes any data the plugin has saved, reinitialises from new
     */
    public static function reset()
    {
        FS_Feed_Entries::remove();
        self::_unregisterScrapeCron();
        self::onWPPluginActivation();
    }

    /**
     * Registers [latest_feed_entries] wordpress shortcode
     */
    private static function _addLatestEntriesShortcode()
    {
        add_shortcode( 'latest_feed_entries', array( 'FS_Feed_Entries', 'handleShortcode' ) );
    }

    /**
     * Registers style sheet for addition 
     */
    private static function _addStyleSheetLink()
    {
        $styleUrl = plugins_url( 'assets/default.css', __FILE__ );
        $styleFile = WP_PLUGIN_DIR . '/feed-scraper/assets/default.css';

        if( file_exists( $styleFile ) )
        {
            wp_register_style( 'feed-scraper', $styleUrl );
            wp_enqueue_style( 'feed-scraper' );
        }
    }

    /**
     * Loads text domain for wp gettext translations
     */
    private static function _loadTextDomain()
    {
        $plugin_dir = basename( dirname( __FILE__ ) ) . '/languages/';
        load_plugin_textdomain( 'feed-scraper', false, $plugin_dir );
    }

    /**
     * Registers needed actions to appropriate wordpress hooks
     */
    private static function _registerActions()
    {
        add_action( 'admin_enqueue_scripts', array( 'FS', 'onWPAdminEnqueueScripts' ) );
        add_action( 'admin_menu', array( 'FS', 'onWPAdminMenu' ) );
        add_action( 'before_delete_post', array( 'FS', 'onWPBeforeDeletePost' ) );
        add_action( 'fs_scrape_interval', array( 'FS', 'onFSScrapeInterval' ) );
        add_action( 'init', array( 'FS', 'onWPInit' ) );
        add_action( 'manage_fs_feed_posts_custom_column', array( 'FS', 'onWPManageFSFeedPostsCustomColumn' ), 10, 2 );
        add_action( 'save_post', array( 'FS', 'onWPSavePost' ) );
    }

    /**
     * Registers filters
     */
    private static function _registerFilters()
    {
        add_filter( 'get_sample_permalink_html', array( 'FS', 'onWPGetSamplePermalinkHTML' ) );
        add_filter( 'manage_fs_feed_posts_columns', array( 'FS', 'onWPManageFSFeedPostsColumns' ) );
        add_filter( 'post_updated_messages', array( 'FS', 'onWPPostUpdatedMessages' ) );
    }

    /**
     * Registers a WP cron job to scrape for new feed entries
     */
    private static function _registerScrapeCron()
    {
        wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'fs_scrape_interval' );
    }

    /**
     * Unregisters the cron job
     */
    private static function _unregisterScrapeCron()
    {
        wp_clear_scheduled_hook( 'fs_scrape_interval' );
    }
}