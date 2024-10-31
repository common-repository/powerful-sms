<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://felipepeixoto.tecnologia.ws/
 * @since      1.0.0
 *
 * @package    Powerful_Sms_Wp
 * @subpackage Powerful_Sms_Wp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Powerful_Sms_Wp
 * @subpackage Powerful_Sms_Wp/includes
 * @author     Felipe Peixoto <peixoto152@gmail.com>
 */
class Powerful_Sms_Wp {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Powerful_Sms_Wp_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'POWERFUL_SMS_WP_VERSION' ) ) {
			$this->version = POWERFUL_SMS_WP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'powerful-sms-wp';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Powerful_Sms_Wp_Loader. Orchestrates the hooks of the plugin.
	 * - Powerful_Sms_Wp_i18n. Defines internationalization functionality.
	 * - Powerful_Sms_Wp_Admin. Defines all hooks for the admin area.
	 * - Powerful_Sms_Wp_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-powerful-sms-wp-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-powerful-sms-wp-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-powerful-sms-wp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-powerful-sms-wp-public.php';

		/**
		 * Classe responsavel por gerenciar a integração com DisparoPro
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/services/class-powerful-sms-wp-disparopro.php';

		/**
		 * Classe responsavel por gerenciar add-on de Woocommerce
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/add-ons/class-powerful-sms-wp-woocommerce.php';

		$this->loader = new Powerful_Sms_Wp_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Powerful_Sms_Wp_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Powerful_Sms_Wp_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Powerful_Sms_Wp_Admin( $this->get_plugin_name(), $this->get_version()  );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		//Menu
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );

		if (in_array( 'woocommerce/woocommerce.php', (array) get_option( 'active_plugins', array() ), true )){
			$this->loader->add_action( 'woocommerce_admin_order_data_after_shipping_address', $plugin_admin, 'chk_perms', 1000, 3 );
			$this->loader->add_action( 'save_post',   $plugin_admin, 'chk_perms_save',1,2 );
		}

	}


	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Powerful_Sms_Wp_Public( $this->get_plugin_name(), $this->get_version() );
		if (in_array( 'woocommerce/woocommerce.php', (array) get_option( 'active_plugins', array() ), true )){
			$this->loader->add_action( 'init', $plugin_public, 'woocommerce_load_action' );
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Powerful_Sms_Wp_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
