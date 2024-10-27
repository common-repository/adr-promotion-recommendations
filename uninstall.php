<?php 
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
 
delete_option('adrcdp_publisher_auth');
delete_option('adrcdp_publisher');
