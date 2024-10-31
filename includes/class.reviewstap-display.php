<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function do_reviewstap_shortcode( $atts ){
	return reviewstap_widget_output();
}
add_shortcode( 'reviewstap_widget', 'do_reviewstap_shortcode' );

// Register the widget
function do_reviewstap_widget() {
	register_widget( 'reviewstap_widget');
}
add_action( 'widgets_init', 'do_reviewstap_widget' );


function reviewstap_widget_output(){
    $bus_id = get_option('reviewstap_bus_id');
    if(!$bus_id){
        return 'Check and save ReviewsTap settings before using widget.';
    }
	return "<script type='text/javascript'>(function (r) {r['_reviewsTapUrl'] = 'https://app.reviewstap.com/'; r['_RTbusinessId'] = ".$bus_id."; if(typeof(reviewsTapWidgetJs) == 'undefined') { reviewsTapWidgetJs = 'loaded'; var scr = document.createElement('script'); scr.src = 'https://app.reviewstap.com/widgets/reviews.js'; var head = document.getElementsByTagName('head')[0]; head.appendChild(scr); } })(window);</script><div id='reviewstap-widget' data-schemaType='Organization'></div>";
}






class reviewstap_widget extends WP_Widget {
 
    function __construct() {
        parent::__construct(
         
        // Base ID of your widget
        'reviewstap_widget', 
         
        // Widget name will appear in UI
        __('ReviewsTap Widget', 'reviewstap'), 
         
        // Widget description
        array( 'description' => __( 'Easily display reviews on your website', 'reviewstap' ), ) 
        );
    }
 
    // Creating widget front-end
 
    public function widget( $args, $instance ) {
    $title = apply_filters( 'widget_title', $instance['title'] );
     
    // before and after widget arguments are defined by themes
    echo $args['before_widget'];
    if ( ! empty( $title ) )
    echo $args['before_title'] . $title . $args['after_title'];
     
    // This is where you run the code and display the output
    echo reviewstap_widget_output();
    echo $args['after_widget'];
    }
         
    // Widget Backend 
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
        $title = $instance[ 'title' ];
        }
        else {
        $title = __( 'Reviews', 'reviewstap' );
        }
        // Widget admin form
        ?>
        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <?php 
    }
     
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }
} // Class reviewstap_widget ends here
?>
