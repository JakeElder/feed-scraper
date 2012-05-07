<?php
/*
Plugin Name: Feed Scraper
Description: Scrapes external RSS feeds, saving new entries
Version: 1.0
Author: Jake Elder
*/

function dateCreateFromRSSPubDate( $date )
{
    if( function_exists( 'date_create_from_format' ) )
    {
        return date_create_from_format( DATE_RSS, $date );
    }

    $date = strptime( $date, '%a, %d %b %Y %H:%M:%S %z' );
    $date['tm_year'] += 1900;
    $date['tm_mon']++;
    $timestamp = mktime( $date['tm_hour'], $date['tm_min'], $date['tm_sec'], $date['tm_mon'], $date['tm_mday'], $date['tm_year'] );
    return new DateTime( '@'. $timestamp );
}

define( 'FS_OUTPUT_TIMEZONE', 'CET' );

require_once( plugin_dir_path( __FILE__ ) .'FS.php' );
require_once( plugin_dir_path( __FILE__ ) .'FS_Feeds.php' );
require_once( plugin_dir_path( __FILE__ ) .'FS_Feed.php' );
require_once( plugin_dir_path( __FILE__ ) .'FS_Feed_Admin.php' );
require_once( plugin_dir_path( __FILE__ ) .'FS_Feed_Entries.php' );
require_once( plugin_dir_path( __FILE__ ) .'FS_Feed_Entry.php' );
require_once( plugin_dir_path( __FILE__ ) .'FS_Feed_Entry_Admin.php' );

register_activation_hook( __FILE__ , array( 'FS', 'onWPPluginActivation' ) );
register_deactivation_hook( __FILE__ , array( 'FS', 'onWPPluginDeactivation' ) );

FS::init(); 