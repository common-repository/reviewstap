<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class ReviewsTap {

	public function __construct() {
	}

	public function initialize() {
	    //setup hooks
	    add_action( 'user_register', array( $this,'reviewstap_new_user'), 10, 1 );
	    add_action('woocommerce_created_customer', array( $this,'reviewstap_wc_add_new_customer'), 10 , 3);
	    add_action('woocommerce_order_status_completed', array( $this,'reviewstap_wc_add_new_order'), 10 , 3);
	}
    
    public function reviewstap_save_options(){
        update_option('reviewstap_api_key', sanitize_text_field($_POST['reviewstap_api_key']) );
        update_option('reviewstap_secret_key', sanitize_text_field($_POST['reviewstap_secret_key']) );
	    update_option('reviewstap_auto_add_users', (isset($_POST['reviewstap_auto_add_users'])&&$_POST['reviewstap_auto_add_users'])?1:0 );
	    update_option('reviewstap_auto_add_wc_customers', (isset($_POST['reviewstap_auto_add_wc_customers'])&&$_POST['reviewstap_auto_add_wc_customers'])?1:0 );
	    update_option('reviewstap_auto_add_wc_orders', (isset($_POST['reviewstap_auto_add_wc_orders'])&&$_POST['reviewstap_auto_add_wc_orders'])?1:0 );
	    
	    $bus_id = $this->reviewstap_api_get_bus_id();
	    if($bus_id){
	        update_option('reviewstap_bus_id', sanitize_text_field($bus_id));
	    }
    }
    
    public function reviewstap_new_user($user_id=0, $auto=true){
        if(!$user_id){return;}
        if(!get_option('reviewstap_auto_add_users') && $auto){return;}
    
        $user = get_userdata($user_id);
        if(!$user){return;}
        
        $email = $user->user_email;
        $name = ($user->first_name||$user->last_name) ? ($user->first_name||$user->last_name) :  $user->user_nicename ;
        
        $this->reviewstap_api_add_user($name, $email);
    }
    
    public function bulk_add_all_users(){
        $users = get_users( array( 'fields' => array( 'ID' ) ) );
        foreach($users as $user){
            $this->reviewstap_new_user($user->ID, false);
        }
    }
	
	/* Woocommerce */
    function reviewstap_wc_add_new_customer( $customer_id, $new_customer_data, $password_generated) {
        if(!get_option('reviewstap_auto_add_wc_customers') && $auto){return;}
       
        $this->reviewstap_api_add_user($new_customer_data['user_login'], $new_customer_data['user_email'], '');
        //$new_customer_data['user_login' => $username, 'user_pass'  => $password, 'user_email' => $email,    'role'       => 'customer']
    }
    
    function reviewstap_wc_add_new_order( $order_id ) {
        if(!get_option('reviewstap_auto_add_wc_orders') && $auto){return;}
        
        $result = $wpdb->get_row("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_billing_email' and post_id ='".$order_id."'");
        if ($result) {
            $email = $result->meta_value;
            $name_row = $wpdb->get_row("SELECT DISTINCT meta_value  FROM $wpdb->postmeta WHERE post_id='".$order_id."' and meta_key='_billing_first_name'");
            $name_row2 = $wpdb->get_row("SELECT DISTINCT meta_value  FROM $wpdb->postmeta WHERE post_id='".$order_id."' and meta_key='_billing_last_name'");
            $name = $name_row ? $name_row->meta_value.' '.$name_row2->meta_value : '';
  
            $this->reviewstap_api_add_user($name, $email, $order_id);
        }
    }



    public function bulk_add_all_wc_customers(){
        global $wpdb;
        $cnt = 0;
        $customer_ids = $wpdb->get_col("SELECT DISTINCT meta_value  FROM $wpdb->postmeta
            WHERE meta_key = '_customer_user' AND meta_value > 0");
        
        if (sizeof($customer_ids) > 0) {
            foreach ($customer_ids as $customer_id){
                $customer = new WP_User($customer_id);
                $name = $customer->display_name;
                $email = $customer->user_email;
                $order_number = '';
                $this->reviewstap_api_add_user($name, $email, $order_number);
                $cnt++;
            }
        }
        return $cnt;
    }
    public function bulk_add_all_wc_orders(){
        global $wpdb;
        $cnt = 0;
        
        $results = $wpdb->get_results("SELECT meta_value, post_id FROM $wpdb->postmeta WHERE meta_key='_billing_email' and post_id in (SELECT ID FROM $wpdb->posts WHERE post_type = 'shop_order') group by meta_value");
        if (sizeof($results) > 0) {
            foreach ($results as $result){
                $email = $result->meta_value;
                $order_number = $result->post_id;

                $name_row = $wpdb->get_row("SELECT DISTINCT meta_value  FROM $wpdb->postmeta WHERE post_id='".$result->post_id."' and meta_key='_billing_first_name'");
                $name_row2 = $wpdb->get_row("SELECT DISTINCT meta_value  FROM $wpdb->postmeta WHERE post_id='".$result->post_id."' and meta_key='_billing_last_name'");
                $name = $name_row ? $name_row->meta_value.' '.$name_row2->meta_value : '';
    
                $this->reviewstap_api_add_user($name, $email, $order_number);
                $cnt++;
            }
        }
        return $cnt;
    }
    
    //Remote functions
    protected function reviewstap_api_add_user($name='', $email='', $order_number=''){
        if(!($name && $email)){return;}
        
        $api_key = sanitize_text_field(get_option('reviewstap_api_key'));
        $secret_key = sanitize_text_field(get_option('reviewstap_secret_key'));
        if(!$api_key || !$secret_key){return;}
        
        //Send to reviewstap server
        try{
            $data = [
                'site_key' => $api_key,
                'email'=> $email,
                'name' => $name,
                'order_id' => $order_number,
                'hash' => md5($email.$api_key.$secret_key)
            ];
            $args = [
                'body' => json_encode($data),
                'headers' => "Content-Type: application/json\r\n",
                'method'      => 'POST'
            ];

            $data = wp_remote_post( 'https://app.reviewstap.com/api/v1/adduser', $args );
            
        }catch(\Exception $ex){ 
            //fail silently to avoid any site issues
        }
    }
    
    //Remote functions
    protected function reviewstap_api_get_bus_id(){       
        $api_key = sanitize_text_field(get_option('reviewstap_api_key'));
        $secret_key = sanitize_text_field(get_option('reviewstap_secret_key'));
        if(!$api_key || !$secret_key){return;}
        
        //Send to reviewstap server
        try{
            $data = [
                'site_key' => $api_key,
                'hash' => md5('getid'.$api_key.$secret_key)
            ];
            $args = [
                'body' => json_encode($data),
                'headers' => "Content-Type: application/json\r\n",
                'method'      => 'POST'
            ];

            $data = wp_remote_post( 'https://app.reviewstap.com/api/v1/getbusid', $args );
            return @$data['body'];
        }catch(\Exception $ex){ 
            //fail silently to avoid any site issues
        }
        return 0;
    }
}

