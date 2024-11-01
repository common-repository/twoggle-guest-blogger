<?php
/*
 * Uninstall for Twoggle Guest Blogger
 * 
 * Triggers when user deletes plugin. Cleans up any used wp_options
 */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
   exit();
}
	
// Clean up!
delete_option('twgb_author_id');
delete_option('twgb_notify_ids');
delete_option('twgb_post_category');
delete_option('twgb_redirection_url');
delete_option('twgb_show_by_twoggle');

