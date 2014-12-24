<?php
/*
Plugin Name: WP Support Ticket
Plugin URI: http://aviplugins.com/
Description: Wordpress user support plugin. Registered users will be able to create new support tickets and reply to already created support tickets. 
Version: 2.2.0
Author: avimegladon
Author URI: http://avifoujdar.wordpress.com/
*/

/**
	  |||||   
	<(`0_0`)> 	
	()(afo)()
	  ()-()
**/

$ticket_status_array = array( 1 => 'Open', 2 => 'Closed', 3 => 'Resolved');

$supported_files_array = array( 
'image/jpeg', 
'image/jpeg', 
'image/png', 
'image/gif', 
'application/msword', 
'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
'application/pdf', 
'application/zip'
);


include_once dirname( __FILE__ ) . '/settings.php';
include_once dirname( __FILE__ ) . '/ticket_class.php';
include_once dirname( __FILE__ ) . '/message_class.php';
include_once dirname( __FILE__ ) . '/data_class.php';
include_once dirname( __FILE__ ) . '/reply_class.php';
include_once dirname( __FILE__ ) . '/mod.php';
include_once dirname( __FILE__ ) . '/support_shortcode.php';

class WPsupport {

     static function wps_install() {
         global $wpdb;
		 $create_table = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."support_reply` (
		  `reply_id` int(11) NOT NULL AUTO_INCREMENT,
		  `ticket_id` int(11) NOT NULL,
		  `user_id` int(11) NOT NULL,
		  `reply_from` ENUM( 'user', 'admin' ) NOT NULL,
		  `reply_msg` text NOT NULL,
		  `reply_added` datetime NOT NULL,
		  PRIMARY KEY (`reply_id`)
		)";
		$wpdb->query($create_table);
		
		$create_table1 = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."support_attachment` (
		  `att_id` int(11) NOT NULL AUTO_INCREMENT,
		  `reply_id` int(11) NOT NULL,
		  `att_file` varchar(255) NOT NULL,
		  PRIMARY KEY (`att_id`)
		)";
		$wpdb->query($create_table1);
		
     }
	  static function wps_uninstall() { }
}
register_activation_hook( __FILE__, array( 'WPsupport', 'wps_install' ) );
register_deactivation_hook( __FILE__, array( 'WPsupport', 'wps_uninstall' ) );