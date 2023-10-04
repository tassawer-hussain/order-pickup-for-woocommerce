<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://tassawer.com/
 * @since      1.0.0
 *
 * @package    Cleaning_Delivery_Order_Pickup
 * @subpackage Cleaning_Delivery_Order_Pickup/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Cleaning_Delivery_Order_Pickup
 * @subpackage Cleaning_Delivery_Order_Pickup/public
 * @author     Tassawer <hello@tassawer.com>
 */
class Cleaning_Delivery_Order_Pickup_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->options = get_option( 'cdop_settings' );

		//add_filter( 'woocommerce_billing_fields' , array( $this, 'cdop_checkout_fields' ), 20, 1 );
		add_action( 'woocommerce_after_checkout_billing_form' , array( $this, 'cdop_checkout_fields_new' ), 20, 1 );
		add_action('woocommerce_checkout_process', array( $this, 'cdop_checkout_field_process' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'cdop_checkout_field_update_order_meta' ) );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'cdop_checkout_field_display_admin_order_meta' ), 10, 1 );
		add_action('woocommerce_email_after_order_table', array( $this, 'cdop_output_order_time_order_email'), 20, 1);
		add_action('woocommerce_order_details_after_order_table_items', array( $this, 'cdop_output_order_time_on_myaccount' ), 20, 1);

		// Update delivery date
		add_action( 'wp_ajax_nopriv_cdop_updated_delivery_date_options', array( $this, 'cdop_updated_delivery_date_options' ) );
		add_action( 'wp_ajax_cdop_updated_delivery_date_options', array( $this, 'cdop_updated_delivery_date_options' ) );

		// Update delivery time slots
		add_action( 'wp_ajax_nopriv_cdop_updated_delivery_timeslots_options', array( $this, 'cdop_updated_delivery_timeslots_options' ) );
		add_action( 'wp_ajax_cdop_updated_delivery_timeslots_options', array( $this, 'cdop_updated_delivery_timeslots_options' ) );
		
		// add radio button after order note
		add_action( 'woocommerce_after_order_notes', array( $this, 'cdop_add_radio_button_after_order_note' ) );
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'cdop_checkout_radio_choice_set_session' ) );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cleaning-delivery-order-pickup-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cleaning-delivery-order-pickup-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'frontend_ajax_object',
			array(
				'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
			)
		);

	}

	/**
	 * Add Pickup/Delivery Time/Date Fields
	 */
	public function cdop_checkout_fields( $billing_fields ) {
		
		$cdop_fields = array(
			'pickup_date' => array(
				'type'        => 'select',
				'label'       => __('Pickup date'),
				'class'       => array('cdop-row form-row-first '),
				'required'    => true,
				'clear'       => true,
				'options' 	  => $this->cdop_pickup_date_options(),
			),
			'pickup_time' => array(
				'type'        => 'select',
				'label'       => __('Pickup time'),
				'class'       => array('cdop-row form-row-last '),
				'required'    => true,
				'clear'       => true,
				'options' 	  => $this->cdop_time_slots('pickup'),
			),
			'delivery_date' => array(
				'type'        => 'select',
				'label'       => __('Delivery date'),
				'class'       => array('cdop-row form-row-first '),
				'required'    => true,
				'clear'       => true,
				'options' 	  => array(
                	'' => '',
                ),
			),
			'delivery_time' => array(
				'type'        => 'select',
				'label'       => __('Delivery time'),
				'class'       => array('cdop-row form-row-last '),
				'required'    => true,
				'clear'       => true,
				'options' 	  => $this->cdop_time_slots('delivery'),
			),
		);


		$billing_fields = array_merge($billing_fields, $cdop_fields);
		
		return $billing_fields;
	}

	public function cdop_checkout_fields_new( $checkout ) {
		
		echo '<div id="my_custom_checkout_field"><h3>' . __('Schedule Pickup & Delivery') . '</h3>';

		woocommerce_form_field( 'pickup_date', array(
			'type'        => 'select',
			'label'       => __('Pickup date'),
			'class'       => array('cdop-row form-row-first '),
			'required'    => true,
			'clear'       => true,
			'options' 	  => $this->cdop_pickup_date_options(),
		), $checkout->get_value( 'pickup_date' ));

		woocommerce_form_field( 'pickup_time', array(
			'type'        => 'select',
			'label'       => __('Pickup time'),
			'class'       => array('cdop-row form-row-last '),
			'required'    => true,
			'clear'       => true,
			'options' 	  => $this->cdop_time_slots_placeholder(),
		), $checkout->get_value( 'pickup_time' ));

		woocommerce_form_field( 'delivery_date', array(
			'type'        => 'select',
			'label'       => __('Delivery date'),
			'class'       => array('cdop-row form-row-first '),
			'required'    => true,
			'clear'       => true,
			'options' 	  => array(
				'' => 'Select Delivery Date',
			),
		), $checkout->get_value( 'delivery_date' ));

		woocommerce_form_field( 'delivery_time', array(
			'type'        => 'select',
			'label'       => __('Delivery time'),
			'class'       => array('cdop-row form-row-last '),
			'required'    => true,
			'clear'       => true,
			'options' 	  => $this->cdop_time_slots_placeholder(),
		), $checkout->get_value( 'delivery_time' ));

		echo '</div>';
	}

	/**
	 * Output the time slots placeholder for pickup/delivery
	 */
	public function cdop_time_slots_placeholder() {
		$slots = array();
		$slots[''] = 'Select Time Slot';
		
		return $slots;
	}

	/**
	 * Output the time slots for pickup/delivery
	 */
	public function cdop_time_slots($mode, $date) {
		global $wpdb;
		$prefix = $wpdb->get_blog_prefix();
		$table = $prefix.'zipcode_order_pickup';
		
		$slots = '';
		$slots .= '<option value="">Select Time Slot</option>';
		
		if( $mode == 'pickup' ) {
			$pickup_record = $wpdb->get_row("SELECT * FROM $table WHERE order_date = '$date' AND order_mode = 'pickup'", ARRAY_A);
			
			if( null !== $pickup_record) {
				$pickup_records = unserialize ($pickup_record['time_slot_record']);
				foreach($pickup_records as $k => $v) {
					if($v < $this->options['cdop_no_of_orders']) {
						$slots .= '<option value="'. $k .'">'. $k .'</option>';
					}
				}
			} else {
				$cdop_time_slots = $this->options['cdop_pickup_time_slots'];
				$cdop_time_slots = explode("\n", $cdop_time_slots);
				foreach($cdop_time_slots as $k) {
					if( strlen($k) > 1) {
						$slots .= '<option value="'. $k .'">'. $k .'</option>';
					}
				}
			}

		} else if( $mode == 'delivery' ) {
			$delivery_record = $wpdb->get_row("SELECT * FROM $table WHERE order_date = '$date' AND order_mode = 'delivery'", ARRAY_A);

			if( null !== $delivery_record) {
				$delivery_records = unserialize ($delivery_record['time_slot_record']);
				foreach($delivery_records as $k => $v) {
					if($v < $this->options['cdop_no_of_orders']) {
						$slots .= '<option value="'. $k .'">'. $k .'</option>';
					}
				}
			} else {
				$cdop_time_slots = $this->options['cdop_delivery_time_slots'];
				$cdop_time_slots = explode("\n", $cdop_time_slots);
				foreach($cdop_time_slots as $k) {
					if( strlen($k) > 1) {
						$slots .= '<option value="'. $k .'">'. $k .'</option>';
					}
				}
			}
		
		}
		
		return $slots;
	}

	/**
	 * Return array of working days
	 */
	public function cdop_working_days() {
		$working_days = $this->options['cdop_working_days'];
		$working_days = explode("\n", $working_days);

		$days = array();
		foreach($working_days as $day) {
			if(strlen($day) > 1) {
				$days[] = rtrim(ltrim($day));
			}
		}

		return $days;
	}

	/**
	 * Output the date options for pickup
	 */
	public function cdop_pickup_date_options() {
		$date_format = 'D, M d';
		$date = date($date_format);
		
		$pickup_dates = array(
        	'' => 'Select Pickup Date',
        );
		$working_days = $this->cdop_working_days();

		$k = 1;
		for( $i=1 ; $i<=$this->options['cdop_no_of_days'] ; ) {
			$next_day = date('l', strtotime($date .' +'.$k.' day'));
			
			if(in_array($next_day, $working_days)) {
				$next_date = date($date_format, strtotime($date .' +'.$k.' day'));
				$pickup_dates[$next_date] = $next_date;
				$i++;
			}
			
			$k++;
		}

		return $pickup_dates;
	}

	/**
	 * Output the date options for delivery
	 */
	public function cdop_delivery_date_options() {
		$date_format = 'D, M d';
		$date = date($date_format);
		
		$delivery_dates = array(
			'' => 'Select Delivery Date'
		);
		$working_days = $this->cdop_working_days();
		
		$k = 2;
		for( $i=1 ; $i<=$this->options['cdop_no_of_days'] ; ) {
			$next_day = date('l', strtotime($date .' +'.$k.' day'));
			
			if(in_array($next_day, $working_days)) {
				$next_date = date($date_format, strtotime($date .' +'.$k.' day'));
				$delivery_dates[$next_date] = $next_date;
				$i++;
			}
			
			$k++;
		}
		return $delivery_dates;
	}

	/**
	 * Output the updated date options for delivery
	 */
	public function cdop_updated_delivery_date_options() {
		if (!empty($_REQUEST['date_selected'])) {
			$k = $_REQUEST['date_selected'];
			$turnaround = $_REQUEST['turnaround'];
			
			/** Prepare time slot for selected pickup date */
			$pickup_time_slots = $this->cdop_time_slots('pickup', $k);

			$date_format = 'D, M d';
			$selected_date = strtotime($k);
			$date = date($date_format, $selected_date);
		
			$delivery_dates = '<option value="">Select Delivery Date</option>';
			$working_days = $this->cdop_working_days();
			
			$k = $turnaround;
			for( $i=1 ; $i<=$this->options['cdop_no_of_days'] ; ) {
				$next_day = date('l', strtotime($date .' +'.$k.' day'));
				
				if(in_array($next_day, $working_days)) {
					$next_date = date($date_format, strtotime($date .' +'.$k.' day'));
					$delivery_dates .= '<option value="'. $next_date .'">'. $next_date .'</option>';
					$i++;
				}
				
				$k++;
			}
			$data = array();
			$data['time_slots'] = $pickup_time_slots;
			$data['delivery_time_slots'] = '<option value="">Select Time Slot</option>';
			// $data['time_slots'] = '';
			$data['delivery_dates'] = $delivery_dates;
			echo json_encode($data);
			wp_die();
		}
	}

	/**
	 * Output the updated date options for delivery
	 */
	public function cdop_updated_delivery_timeslots_options() {
		if (!empty($_REQUEST['date_selected'])) {
			$k = $_REQUEST['date_selected'];
			
			/** Prepare time slot for selected delivery date */
			echo $this->cdop_time_slots('delivery', $k);
			wp_die();
		}
	}


	/**
	 * Process the checkout
	 */
	function cdop_checkout_field_process() {
		// Check if set, if its not set add an error.
		if ( ! $_POST['pickup_date'] )
			wc_add_notice( __( '<strong>Pickup Date</strong> is a required field.' ), 'error' );

		if ( ! $_POST['pickup_time'] )
			wc_add_notice( __( '<strong>Pickup Time</strong> is a required field.' ), 'error' );

		if ( ! $_POST['delivery_date'] )
			wc_add_notice( __( '<strong>Delivery Date</strong> is a required field.' ), 'error' );

		if ( ! $_POST['delivery_time'] )
			wc_add_notice( __( '<strong>Delivery Time</strong> is a required field.' ), 'error' );

		if ( isset($_POST['cd_radio_choice']) && $_POST['cd_radio_choice'] == "Other" ) {
			if ( isset($_POST['cd_radio_choice_other']) && empty($_POST['cd_radio_choice_other']) )
			wc_add_notice( __( 'Please specify <strong>PickUp/Delivery Location</strong>.' ), 'error' );
		}

		if( isset( $_POST['ship_to_different_address'])) {
			// shipping address
			$shipping_zipcode = $_POST['shipping_postcode'];
			$shipping_address = $_POST['shipping_address_1'] . " " . $_POST['shipping_city'] . ", " . $_POST['shipping_state'];
			$shipping_address1 = str_replace(' ', '+', $shipping_address);

			// billing address
			$billing_zipcode = $_POST['billing_postcode'];
			$billing_address = $_POST['billing_address_1'] . " " . $_POST['billing_city'] . ", " . $_POST['billing_state'];
			$billing_address1 = str_replace(' ', '+', $billing_address);

			// Are we serving in this zip code
			$this->cdop_serving_this_zipcode($shipping_zipcode);

			// validate shipping address
			$this->validate_address_using_google_map($shipping_address, $shipping_address1, $shipping_zipcode, 'shipping');
			
			// validate billing address
			$this->validate_address_using_google_map($billing_address, $billing_address1, $billing_zipcode, 'billing');

		} else {
			// billing address
			$billing_zipcode = $_POST['billing_postcode'];
			$billing_address = $_POST['billing_address_1'] . " " . $_POST['billing_city'] . ", " . $_POST['billing_state'];
			$billing_address1 = str_replace(' ', '+', $billing_address);

			// Are we serving in this zip code
			$this->cdop_serving_this_zipcode($billing_zipcode);

			// validate billing address
			$this->validate_address_using_google_map($billing_address, $billing_address1, $billing_zipcode, 'billing');
		}
		
	}

	public function cdop_serving_this_zipcode($zipcode) {
		global $wpdb;
		$prefix = $wpdb->get_blog_prefix();
		$table = $prefix.'zipcode_serving';
		$count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE zipcode = '$zipcode'");
		if(!$count) {
			wc_add_notice( __( "We are not serving in <strong>$zipcode</strong> zip code. Please change zip code." ), 'error' );
		}
	}

	public function validate_address_using_google_map($human_address, $machine_address, $zipcode, $address_is) {
		/**
		 * Validate address withusing google API
		 * Verify address with Zipcode
		 */
		$url = "https://maps.googleapis.com/maps/api/geocode/json?address=$machine_address&sensor=true_or_false&key=AIzaSyCxa7CWP7E1dMr6zTG-753L-IHqH_b7_Ik";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$array = json_decode($response, true);

		if($array['results'][0]['address_components'][6]['long_name'] == $zipcode
			|| $array['results'][0]['address_components'][6]['short_name'] == $zipcode) {
		} else {
			wc_add_notice( __( "Your $address_is address <strong>$human_address</strong> does not belong to <strong>$zipcode</strong> zip code. Please double check your address. Please add any apartment number, unit, building number, or any other extra address in the next line."), 'error' );
		}
	}

	/**
	 * Update the order meta with field value - USEFULL FOR SAVING MATRICES
	 */
	function cdop_checkout_field_update_order_meta( $order_id ) {
		$order_pickup_delivery = array();

		if ( $_POST['pickup_date'] )
			$order_pickup_delivery['pickup_date'] = $_POST['pickup_date'];

		if ( $_POST['pickup_time'] )
			$order_pickup_delivery['pickup_time'] = $_POST['pickup_time'];

		if ( $_POST['delivery_date'] )
			$order_pickup_delivery['delivery_date'] = $_POST['delivery_date'];

		if ( $_POST['delivery_time'] )
			$order_pickup_delivery['delivery_time'] = $_POST['delivery_time'];
		
		if ( $_POST['cd_radio_choice'] ) {
			$order_pickup_delivery['cd_radio_choice'] = $_POST['cd_radio_choice'];
			if($_POST['cd_radio_choice'] == "Other") {
				$order_pickup_delivery['cd_radio_choice_other'] = $_POST['cd_radio_choice_other'];
			}
		}
		
		update_post_meta( $order_id, 'order_pickup_delivery', $order_pickup_delivery );

		$this->cdop_update_time_slots($order_pickup_delivery);
	}

	/**
	 * Updat time slot record in database
	 */
	public function cdop_update_time_slots($date_time) {
		$pickupdate = trim($date_time['pickup_date']);
		$pickupTime = trim($date_time['pickup_time']);
		$deliveryDate = trim($date_time['delivery_date']);
		$deliveryTime = trim($date_time['delivery_time']);

		global $wpdb;
		$prefix = $wpdb->get_blog_prefix();
		$table = $prefix.'zipcode_order_pickup';

		/** Update pickup reord */
		$pickup_record = $wpdb->get_row("SELECT * FROM $table WHERE order_date = '$pickupdate' AND order_mode = 'pickup'", ARRAY_A);
		if( null !== $pickup_record) {

			$pickup_record['time_slot_record'] = unserialize ($pickup_record['time_slot_record']);
			$pickup_record['time_slot_record'][$pickupTime] = intval($pickup_record['time_slot_record'][$pickupTime]) + 1; 
			$wpdb->update( $table, 
				array('time_slot_record' => serialize($pickup_record['time_slot_record']) ),
				array( 'order_date' => $pickupdate, 'order_mode' => 'pickup') );
		} else {
			$slots = array();
			$cdop_time_slots = explode("\n", $this->options['cdop_pickup_time_slots']);

			foreach($cdop_time_slots as $value) {
				if( strlen($value) > 1) {
					$value = trim(str_replace("\n", "", $value));
					
					if($value == $pickupTime ) {
						$slots[$value] = 1;
					} else {
						$slots[$value] = 0;
					} 
				}
			}
			$wpdb->insert( $table, 
				array('order_date' => $pickupdate,
					'order_mode' => 'pickup',
					'time_slot_record' => serialize($slots) ),
				);
		}

		/** Update delivery reord */
		$delivery_record = $wpdb->get_row("SELECT * FROM $table WHERE order_date = '$deliveryDate' AND order_mode = 'delivery'", ARRAY_A);
		if( null !== $delivery_record) {
			$delivery_record['time_slot_record'] = unserialize ($delivery_record['time_slot_record']);
			$delivery_record['time_slot_record'][$deliveryTime] = intval($delivery_record['time_slot_record'][$deliveryTime]) + 1;
			$wpdb->update( $table, 
				array('time_slot_record' => serialize($delivery_record['time_slot_record']) ),
				array( 'order_date' => $deliveryDate, 'order_mode' => 'delivery') ); 
		} else {
			$slots = array();
			$cdop_time_slots = explode("\n", $this->options['cdop_delivery_time_slots']);

			foreach($cdop_time_slots as $value) {
				if( strlen($value) > 1) {
					$value = trim(str_replace("\n", "", $value));
					
					if($value == $deliveryTime ) {
						$slots[$value] = 1;
					} else {
						$slots[$value] = 0;
					} 
				}
			}

			$wpdb->insert( $table, 
				array('order_date' => $deliveryDate,
					'order_mode' => 'delivery',
					'time_slot_record' => serialize($slots) ),
				);
		}

	} 

	/**
	 * Display field value on the order edit page
	 */
	function cdop_checkout_field_display_admin_order_meta($order) {
		$this->cdop_display_order_pickup_delivery_time($order);
	}

	/**
	 * Display field value on the order email
	 */
	function cdop_output_order_time_order_email($order) {
		$this->cdop_display_order_pickup_delivery_time($order);
	}

	/**
	 * Output the total order saving on my-account order details
	 */
	public function cdop_output_order_time_on_myaccount($order) {
		$this->cdop_display_order_pickup_delivery_time($order);
	}

	/**
	 * Output the order pickup and delivery time and date
	 */
	public function cdop_display_order_pickup_delivery_time($order) {
		$order_pickup_delivery = get_post_meta( $order->id, 'order_pickup_delivery', true );

		echo '<p><strong>'.__('Pickup Date').':</strong> ' . $order_pickup_delivery['pickup_date'] . '</p>';
		echo '<p><strong>'.__('Pickup Time').':</strong> ' . $order_pickup_delivery['pickup_time'] . '</p>';
		echo '<p><strong>'.__('Delivery Date').':</strong> ' . $order_pickup_delivery['delivery_date'] . '</p>';
		echo '<p><strong>'.__('Delivery Time').':</strong> ' . $order_pickup_delivery['delivery_time'] . '</p>';
		if( $order_pickup_delivery['cd_radio_choice'] == "Other") {
			echo '<p><strong>'.__('PickUp/Delivery Location').':</strong> ' . $order_pickup_delivery['cd_radio_choice'] . ' - '. $order_pickup_delivery['cd_radio_choice_other'] .'</p>';
		} else {
			echo '<p><strong>'.__('PickUp/Delivery Location').':</strong> ' . $order_pickup_delivery['cd_radio_choice'] . '</p>';
		}
	}
	
	/**
	 * Output radio button after the order note field
	 */
	public function cdop_add_radio_button_after_order_note() {
		$chosen = WC()->session->get( 'radio_chosen' );
		$chosen = empty( $chosen ) ? WC()->checkout->get_value( 'cd_radio_choice' ) : $chosen;
		$chosen = empty( $chosen ) ? 'No Preference' : $chosen;

		$args = array(
			'type' => 'radio',
			'class' => array( 'form-row-wide', 'update_totals_on_change' ),
			'options' => array(
				'No Preference' => 'No Preference',
			   	'Front Door' => 'Front Door',
			   	'Back Door' => 'Back Door',
			   	'Side Porch' => 'Side Porch',
			   	'Garage' => 'Garage',
			   	'Building Reception' => 'Building Reception',
			   	'Mailroom' => 'Mailroom',
				'Other' => 'Other',
			),
			'default' => $chosen
		);
			  
		echo '<div id="checkout-radio">';
		echo '<h3>PickUp/Delivery Location</h3>';
		woocommerce_form_field( 'cd_radio_choice', $args, $chosen );

		woocommerce_form_field( 'cd_radio_choice_other', array(
			'type' => 'text',
			'placeholder' => 'Please specify',
			'type' => 'text',
			'type' => 'text',
		), WC()->checkout->get_value( 'cd_radio_choice' ));

		echo '</div>';
	}
  
	/**
	 * Add Radio Choice to Session
	 */
	public function cdop_checkout_radio_choice_set_session( $posted_data ) {
		parse_str( $posted_data, $output );
		if ( isset( $output['cd_radio_choice'] ) ){
			WC()->session->set( 'cd_radio_choice', $output['cd_radio_choice'] );
		}
	}
}
