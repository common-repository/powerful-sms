<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://felipepeixoto.tecnologia.ws/
 * @since      1.0.0
 *
 * @package    Powerful_Sms_Wp
 * @subpackage Powerful_Sms_Wp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Powerful_Sms_Wp
 * @subpackage Powerful_Sms_Wp/admin
 * @author     Felipe Peixoto <peixoto152@gmail.com>
 */
class Powerful_Sms_Wp_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style('jquery-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css');
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/powerful-sms-wp-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'jquery-ui-accordion', array( 'jquery' ) );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/powerful-sms-wp-admin.js', array( 'jquery' ), $this->version, true );

	}
	/* Register the Menu for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu() {
		add_menu_page( 
			'Powerful SMS',
			'Powerful SMS',
			'manage_options',
			'powerfulsms',
			array($this,'view_config'),
			'dashicons-format-status',
			6
		);
		add_submenu_page(
			'powerfulsms',
			'Status de Pedido', 
			'Status de Pedido', 
			'manage_options',
			'powerfulsms-status', 
			array($this,'view_status_order') 
		);
	}

	public function chk_perms(){
		global $post;
		$chk_perms = get_post_meta($post->ID,'psms_notify', true);
		$checked = $chk_perms==false ? '' : 'checked="checked"';
?>
	<div>
		<h3><input type="checkbox" value="1" <?php echo esc_attr($checked); ?> name="psms_notify"> Permissão para enviar notificação por SMS</h3>
		<input type="hidden" value="1" name="psms_notify_update_flag">
	</div>
<?php
	}

	public function chk_perms_save($post_id, $post){
		$post_type = $post->post_type;
	    if($post_id && $post_type=='shop_order' and isset($_POST['psms_notify_update_flag'])) { 
	    	if(isset($_POST['psms_notify'])){
	    		update_post_meta($post_id,'psms_notify',1);
	    	} else {
	    		delete_post_meta($post_id,'psms_notify');
	    	}
	    }
	}

	public function view_config() {

		if(isset($_POST['psms-disparopro-token']) and !empty($_POST['psms-disparopro-token'])){
			$disparopro_token = Powerful_Sms_Wp_Disparopro::set_token($_POST['psms-disparopro-token']);
			if ($disparopro_token !== false) {
				add_action( 'admin_notices', array($this,'admin_notices_success') );
			} else{
				add_action( 'admin_notices', array($this,'admin_notices_error') );
			}
		}
		$disparopro_token = Powerful_Sms_Wp_Disparopro::get_token();
		do_action( 'admin_notices');
		include_once(POWERFUL_SMS_WP_PATH.'admin/partials/powerful-sms-wp-admin-config.php');

	}

	public function view_status_order() {

		
		$woo = new Powerful_Sms_Wp_Woocommerce();
		if (isset($_POST)) {
			$woo->save_triggers($_POST);
			add_action( 'admin_notices', array($this,'admin_notices_success') );
			$woo->load_triggers();
		}
		$triggers = $woo->triggers;
		do_action( 'admin_notices');
		include_once(POWERFUL_SMS_WP_PATH.'admin/partials/powerful-sms-wp-admin-status-order.php');
	}

	public function admin_notices_success() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>Salvo com sucesso!</p>
    </div>
    <?php
	}

	public function admin_notices_error() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p>Algo errado aconteceu e não foi possível salvar!</p>
    </div>
    <?php
	}

}
