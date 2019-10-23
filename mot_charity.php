<?php
/**
 * @package MOT_Charity
 * @version 0.0.1
 */
/*
Plugin Name: MOT Charity
Plugin URI: https://mot.astromech.info
Description: Pull in the current YTD charity amount from the R2 Builders MOT site.
Author: Darren Poulson
Version: 0.0.1
Author URI: https://r2djp.co.uk/
*/

// Register and load the widget
function wpb_load_widget() {
    register_widget( 'wpb_widget' );
}
add_action( 'widgets_init', 'wpb_load_widget' );
add_action('admin_menu', 'mot_charity_settings_menu');

function mot_charity_settings_menu() {

	//create new top-level menu
    add_submenu_page(
        'options-general.php', // top level menu page
        'MOT Charity Settings', // title of the settings page
        'MOT Charity', // title of the submenu
        'manage_options', // capability of the user to see this page
        'mot-charity-settings-page', // slug of the settings page
        'mot_charity_settings_page' // callback function when rendering the page
        );

	//call register settings function
	add_action( 'admin_init', 'mot_charity_settings' );
}

function mot_charity_settings() {
	//register our settings
	register_setting( 'mot-charity-settings-group', 'site_url' );
	register_setting( 'mot-charity-settings-group', 'key' );
	register_setting( 'mot-charity-settings-group', 'money_symbol');
}
 
function mot_charity_settings_page() {
?>
<div class="wrap">
<h1>MOT Charity Settings</h1>

<form method="post" action="options.php">
    <?php settings_fields( 'mot-charity-settings-group' ); ?>
    <?php do_settings_sections( 'mot-charity-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Site URL</th>
        <td><input type="text" name="site_url" value="<?php echo esc_attr( get_option('site_url') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">API Key</th>
        <td><input type="text" name="key" value="<?php echo esc_attr( get_option('key') ); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Currency Symbol</th>
        <td><input type="text" name="money_symbol" value="<?php echo esc_attr( get_option('money_symbol') ); ?>" /></td>
        </tr>        
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php 
}

// Creating the widget 
class wpb_widget extends WP_Widget {
 
	function __construct() {
		parent::__construct(
 	
		// Base ID of your widget
		'wpb_widget', 
 
		// Widget name will appear in UI
		__('R2 Builders Charity', 'wpb_widget_domain'), 
 
		// Widget description
		array( 'description' => __( 'A widget to display the YTD charity contributions helped to raise by the R2 Builders', 'wpb_widget_domain' ), ) 
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
		$url = esc_attr(get_option('site_url', ''));
		$key = esc_attr(get_option('key', ''));
		$currency = esc_attr(get_option('money_symbol', ''));
		$amount = file_get_contents($url.'?api='.$key.'&request=ytd_charity');
		echo __( $currency.$amount, 'wpb_widget_domain' );
		echo $args['after_widget'];
	}
         
	// Widget Backend 
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'wpb_widget_domain' );
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
} // Class wpb_widget ends here


?>
