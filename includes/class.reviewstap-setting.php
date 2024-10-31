<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function reviewstap_control_menu() {
    add_submenu_page( 'options-general.php', 'Reviews Tap Setup', 'Reviews Tap', 'manage_options', 'reviewstap', 'reviewstap_dashboard');
}
add_action( 'admin_menu', 'reviewstap_control_menu' );

function reviewstap_dashboard(){
	if(isset($_POST['reviewstap_update_config']) && $_POST['reviewstap_update_config']){
	    $reviewstap = new ReviewsTap();
	    
	    $reviewstap->reviewstap_save_options();
	    
	    if(isset($_POST['bulk_add_existing']) && $_POST['bulk_add_existing']){
	        $reviewstap->bulk_add_all_users();
	    }
	    if(isset($_POST['bulk_add_existing_wc']) && $_POST['bulk_add_existing_wc']){
	        echo $reviewstap->bulk_add_all_wc_customers() .' Sent';
	    }
	    if(isset($_POST['bulk_add_existing_wc_orders']) && $_POST['bulk_add_existing_wc_orders']){
	        echo $reviewstap->bulk_add_all_wc_orders() .' Sent';
	    }
	    
	}
	echo '<div class="wrap">';
	
	echo '<div style="background:#FFF; border-radius:10px; padding:5px 10px; width:50%; float:left;">';
	echo '<form action="#" method="post"><input type="hidden" name="reviewstap_update_config" value="1">';
	    echo('<img src="https://www.reviewstap.com/wp-content/uploads/2020/01/cropped-LOGO-1.png" alt="Reviews Tap"><br>');
	    
	    echo('<p>In order to use this plugin you must have an account with ReviewsTap, <a href="https://www.reviewstap.com" target="_blank">click here to signup</a>.</p>');
	    echo('<p>Once you have registered, head to the <a href="https://app.reviewstap.com/settings/integrations" target="_blank">Integrations Page</a> to obtain your API key.</p>');
	    echo('<span style="min-width:80px; display:inline-block;">API KEY:</span> <input type="text" value="'.sanitize_text_field(get_option('reviewstap_api_key')).'" name="reviewstap_api_key" placeholder="Enter your API KEY Here" size="40"><br>');
	    echo('<span style="min-width:80px; display:inline-block;">SECRET KEY:</span> <input type="text" value="'.sanitize_text_field(get_option('reviewstap_secret_key')).'" name="reviewstap_secret_key" placeholder="Enter your SECRET KEY Here" size="40"><br>');
	    
	    echo('<br><br><input type="checkbox" value="1" name="reviewstap_auto_add_users" id="reviewstap_auto_add_users" '.(get_option('reviewstap_auto_add_users')? "checked='checked'" : "").'> <label for="reviewstap_auto_add_users">Add all new users to ReviewsTap</label><br>');
	    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	        echo('<br><input type="checkbox" value="1" name="reviewstap_auto_add_wc_customers" id="reviewstap_auto_add_wc_customers" '.(get_option('reviewstap_auto_add_wc_customers')? "checked='checked'" : "").'> <label for="reviewstap_auto_add_wc_customers">Add all new WooCommerce Customers to ReviewsTap</label><br>');
	        echo('<br><input type="checkbox" value="1" name="reviewstap_auto_add_wc_orders" id="reviewstap_auto_add_wc_orders" '.(get_option('reviewstap_auto_add_wc_orders')? "checked='checked'" : "").'> <label for="reviewstap_auto_add_wc_orders">Add all completed WooCommerce Orders to ReviewsTap</label><br>');
	    }
	    echo('<br><input type="checkbox" value="1" name="bulk_add_existing" id="bulk_add_existing"> <label for="bulk_add_existing">Bulk add all existing users to ReviewsTap (duplicates will be skipped)</label><br>');
	    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	        echo('<br><input type="checkbox" value="1" name="bulk_add_existing_wc" id="bulk_add_existing_wc"> <label for="bulk_add_existing_wc">Bulk add all existing woocommerce customers to ReviewsTap (duplicates will be skipped)</label><br>');
	        echo('<br><input type="checkbox" value="1" name="bulk_add_existing_wc_orders" id="bulk_add_existing_wc_orders"> <label for="bulk_add_existing_wc_orders">Bulk add all completed woocommerce orders to ReviewsTap (duplicates will be skipped)</label><br>');
	    }
	echo '<br><input type="submit" value="Save Changes" class="button button-primary">';
	echo '</form>';
	echo '</div>';

	echo '<div style="background:#FFF; border-radius:10px; padding:5px 10px; width:40%; margin-left:5%; float:left;">';
	echo('<h3>Reviews Display Widget</h3>');
	echo('<p>To Display a Widget containing your reviews on your site simply insert the following shortcode where you would like the widget to appear.<br>
	[reviewstap_widget]</p>');
	echo('<h3>Help & Support</h3>');
	echo('<p>For help, support or feature requests visit <a href="https://helpdesk.reviewstap.com" target="_blank">https://helpdesk.reviewstap.com</a>.</p>');
	echo '</div>';
		
	echo '</div>';
}

?>
