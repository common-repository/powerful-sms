<?php

/**
 * Fired during plugin activation
 *
 * @link       http://felipepeixoto.tecnologia.ws/
 * @since      1.0.0
 *
 * @package    Powerful_Sms_Wp
 * @subpackage Powerful_Sms_Wp/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Powerful_Sms_Wp
 * @subpackage Powerful_Sms_Wp/includes
 * @author     Felipe Peixoto <peixoto152@gmail.com>
 */
class Powerful_Sms_Wp_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$args = array( true );
        if (! wp_next_scheduled ( 'psms_reminder_action', $args )) {
            wp_schedule_event( time(), 'hourly', 'psms_reminder_action', $args );
        }
	}

}
