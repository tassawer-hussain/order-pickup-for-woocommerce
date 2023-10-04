<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://tassawer.com/
 * @since      1.0.0
 *
 * @package    Cleaning_Delivery_Order_Pickup
 * @subpackage Cleaning_Delivery_Order_Pickup/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cleaning_Delivery_Order_Pickup
 * @subpackage Cleaning_Delivery_Order_Pickup/admin
 * @author     Tassawer <hello@tassawer.com>
 */
class Cleaning_Delivery_Order_Pickup_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $options;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->options = get_option( 'cdop_settings' );

		add_action( 'admin_menu', array($this, 'cdop_settings_menu') );
		add_action( 'admin_init', array($this, 'cdop_settings') );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cleaning_Delivery_Order_Pickup_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cleaning_Delivery_Order_Pickup_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cleaning-delivery-order-pickup-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cleaning_Delivery_Order_Pickup_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cleaning_Delivery_Order_Pickup_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cleaning-delivery-order-pickup-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function cdop_settings_menu() {
		
		// Create top-level menu item
		add_menu_page( 
            'Cleaning Delivery Order Pickup', //$page_title
            'CD Order Pickup',//$menu_title
            'manage_options', // $capability
            'cd-order-pickup', // $menu_slug
            array($this, 'cd_order_pickup_callback' ), // $function
			'dashicons-calendar-alt',
			33
		); // $icon_url
	}

	public function cd_order_pickup_callback() { ?>
		<div id="cd-order-pickup" class="wrap">
			<h2>Cleaning Delivery Order PickUp – Settings</h2>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php //settings_fields($option_group); ?>
				<?php // $option_group of register setting func
				settings_fields( 'cdop_settings' ); ?>
				<?php //do_settings_sections($page); ?>
				<?php // $page of add_settings_section func
				do_settings_sections( 'cd-order-pickup' ); ?> 
				<input type="submit" value="Submit" class="button-primary" />
			</form>
		</div>
	<?php }

	public function cdop_settings() {
		// Register a setting group with a validation function
		// so that post data handling is done automatically for us
		register_setting( 
			'cdop_settings', //$option_group, UNIQUE NAME
			'cdop_settings', //$option_name, SAME AS IN DATABASE
			array( $this, 'cdop_validate_options' )); //$args, CALL BACK VALIDATING FUNC 

			
		// Add a new settings section within the group
		add_settings_section( 
			'cdop_order_pickup_section', //$id, unique name
			'Saving Matrics - Units', //$title
			array( $this, 'cdop_order_pickup_section_callback' ),//$callback
			'cd-order-pickup' );//$page

		// Add each field with its name and function to use for
		// our new settings, put them in our new section
		add_settings_field( 
			'cdop_no_of_days', //$id
			'# of Days to show', //$title, a label that will be display next to the field
			array( $this, 'cdop_display_text_field' ), //$callback
			'cd-order-pickup', //$page
			'cdop_order_pickup_section', //$section
			array( 'name' => 'cdop_no_of_days' ) ); //$args

		add_settings_field( 
			'cdop_no_of_orders', //$id
			'# of Orders against time slot', //$title, a label that will be display next to the field
			array( $this, 'cdop_display_text_field' ), //$callback
			'cd-order-pickup', //$page
			'cdop_order_pickup_section', //$section
			array( 'name' => 'cdop_no_of_orders' ) ); //$args

		add_settings_field( 
			'cdop_working_days', //$id
			'Working Days', //$title, a label that will be display next to the field
			array( $this, 'cdop_display_text_area' ), //$callback
			'cd-order-pickup', //$page
			'cdop_order_pickup_section', //$section
			array( 'name' => 'cdop_working_days' ) ); //$args

		add_settings_field( 
			'cdop_pickup_time_slots', //$id
			'Order PickUp Time Slots', //$title, a label that will be display next to the field
			array( $this, 'cdop_display_text_area' ), //$callback
			'cd-order-pickup', //$page
			'cdop_order_pickup_section', //$section
			array( 'name' => 'cdop_pickup_time_slots' ) ); //$args

		add_settings_field( 
			'cdop_delivery_time_slots', //$id
			'Order Delivery Time Slots', //$title, a label that will be display next to the field
			array( $this, 'cdop_display_text_area' ), //$callback
			'cd-order-pickup', //$page
			'cdop_order_pickup_section', //$section
			array( 'name' => 'cdop_delivery_time_slots' ) ); //$args
		
	}

	public function cdop_validate_options( $input ) {
		$input['version'] = $this->version;
		return $input;
	}

	// Declare a body for the cdop_order_pickup_section_callback function
	public function cdop_order_pickup_section_callback() { ?>
		<p>Configure working day, pickup time slots and delivery time slots.</p>
	<?php }

	// Provide an implementation for the ch3sapi_display_text_field function
	function cdop_display_text_field( $data = array() ) {
		extract( $data ); ?>
		<input type="number" min="1" max="30" name="cdop_settings[<?php echo $name; ?>]" value="<?php echo isset($this->options[$name]) ? $this->options[$name] : ''; ?>"/><br />
	<?php }

	public function cdop_display_text_area( $data = array() ) {
		extract ( $data ); ?>
		<textarea type="text" name="cdop_settings[<?php echo $name; ?>]" rows="8" cols="100"><?php if(isset($this->options[$name])) { echo esc_html($this->options[$name]); } ?></textarea>
	<?php }

}
