<?php

/**
 * Base Feed Scraper class
 * Mainly used for initialising hooks/filters
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
        add_action( 'save_post', array( 'FS', 'onWPSavePost' ) );
    }

    /**
     * Registers filters
     */
    private static function _registerFilters()
    {
        add_filter( 'post_updated_messages', array( 'FS_Feed_Admin', 'filterPostUpdatedMessages' ) );
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