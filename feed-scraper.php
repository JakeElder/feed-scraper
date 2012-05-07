<?php
/*
Plugin Name: Feed Scraper
Description: Scrapes external RSS feeds, saving new entries
Version: 1.0
Author: Jake Elder
*/

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