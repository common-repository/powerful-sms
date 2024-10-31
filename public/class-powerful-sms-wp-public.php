<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://felipepeixoto.tecnologia.ws/
 * @since      1.0.0
 *
 * @package    Powerful_Sms_Wp
 * @subpackage Powerful_Sms_Wp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Powerful_Sms_Wp
 * @subpackage Powerful_Sms_Wp/public
 * @author     Felipe Peixoto <peixoto152@gmail.com>
 */
class Powerful_Sms_Wp_Public {

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

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/powerful-sms-wp-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/powerful-sms-wp-public.js', array( 'jquery' ), $this->version, false );

	}

	public function woocommerce_load_action(){

		//verifica se é para enviar somente quando whatsapp falhar
		if (get_option('psms_pac_fail', 0) == 1) {
			add_action('pac_send_fail', function($phone_no, $message){
				$res = Powerful_Sms_Wp_Disparopro::sendSMS($phone_no,$message);
			});
			return false;
		}
		$woo = new Powerful_Sms_Wp_Woocommerce();
		$settings = $woo->triggers;
		if (!empty($settings)) {

			foreach ($settings as $key => $sValue) {	
				$sValue = (object) $sValue;
				if ($sValue->ativo == '1' and $sValue->action != 'reminder') {					
					if ( strpos($key, 'custom_status_') === false) {
						add_action( $sValue->action, function ($orderId) use ($sValue) {
							$woo = new Powerful_Sms_Wp_Woocommerce();
							$woo->do_action($orderId,$sValue);
						}, PHP_INT_MAX ,1 );
					} else{
						add_action( 'woocommerce_order_status_changed', function ($orderId, $status_from,  $status_to) use ($sValue) {				
							if (str_replace('wc-', '', $sValue->action) == $status_to ) {
								$woo = new Powerful_Sms_Wp_Woocommerce();
								$woo->do_action($orderId,$sValue);
							}
						}, PHP_INT_MAX ,3 );
					}
				}
			}			
		}
		add_action( 'psms_reminder_action', array( new Powerful_Sms_Wp_Woocommerce, 'do_reminder' ) );

		//Adiciona permissão de envio
		if (psms_is_plugin_active('woofunnels-aero-checkout/woofunnels-aero-checkout.php')) {
			add_action( 'woocommerce_review_order_before_payment', array(  new Powerful_Sms_Wp_Woocommerce, 'option_field' ) );	
		} else{
			add_action( 'woocommerce_after_order_notes', array(  new Powerful_Sms_Wp_Woocommerce, 'option_field' ) );
		}
		add_action( 'woocommerce_checkout_update_order_meta', array(  new Powerful_Sms_Wp_Woocommerce, 'save_option_field' ) );
	}

}
