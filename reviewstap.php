<?php
/*
 * Plugin Name: ReviewsTap
 * Version: 1.1.1
 * Description: ReviewsTap helps small businesses collect, monitor and manage reviews across a range of online platforms.
 * Author: ReviewsTap
 * Author URI: https://www.reviewstap.com/
 * Requires at least: 5.0.0
 * Tested up to: 6.2
 *
 */
 
 // If this file is called directly, abort.

if ( ! defined( 'WPINC' ) ) {
	die;
}

function activate_reviewstap() {
	//activation code if needed
}

function deactivate_reviewstap() {
	//deactivation code if needed
}
register_activation_hook( __FILE__, 'activate_reviewstap' );
register_deactivation_hook( __FILE__, 'deactivate_reviewstap' );


require_once plugin_dir_path( __FILE__ ) . 'includes/class.reviewstap-general.php';
$reviewstap = new ReviewsTap();
$reviewstap->initialize();


require_once plugin_dir_path( __FILE__ ) . 'includes/class.reviewstap-setting.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class.reviewstap-display.php';

?>
