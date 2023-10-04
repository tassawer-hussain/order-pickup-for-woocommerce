<?php

/**
 * Fired during plugin activation
 *
 * @link       https://tassawer.com/
 * @since      1.0.0
 *
 * @package    Cleaning_Delivery_Order_Pickup
 * @subpackage Cleaning_Delivery_Order_Pickup/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cleaning_Delivery_Order_Pickup
 * @subpackage Cleaning_Delivery_Order_Pickup/includes
 * @author     Tassawer <hello@tassawer.com>
 */
class Cleaning_Delivery_Order_Pickup_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;
        $prefix = $wpdb->get_blog_prefix();
        
        $creation_query =
        'CREATE TABLE IF NOT EXISTS ' . $prefix . 'zipcode_order_pickup (
                `id` int(20) NOT NULL AUTO_INCREMENT,
                `order_date` text NOT NULL,
                `order_mode` text NOT NULL,
                `time_slot_record` longtext,
                PRIMARY KEY (`id`)
                );'; 
        $tble_creation = $wpdb->query( $creation_query );        
	}

}
