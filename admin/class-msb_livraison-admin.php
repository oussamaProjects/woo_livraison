<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       slashnpro@gmail.com
 * @since      1.0.0
 *
 * @package    Msb_livraison
 * @subpackage Msb_livraison/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Msb_livraison
 * @subpackage Msb_livraison/admin
 * @author     Oussama Elmaaroufy <slashnpro@gmail.com>
 */
class Msb_livraison_Admin {

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

	
	private static $plugin_url;
	private static $plugin_dir;
	private static $plugin_title = "Shipping Calculator";
	private static $plugin_slug = "msb-calculator-setting";
	private static $msb_option_key = "msb-calculator-setting";
	private $msb_settings;
	public static $APIurl = "";
	public static $license = "";
	public static $login = "";
	public static $password = "";
	public static $client = "";

	
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		/* create admin menu for shipping calculator setting */
		add_action("admin_menu", array($this, "admin_menu"));

		global $msb_livraison_plugin_dir, $msb_livraison_plugin_url;

		/* plugin url and directory variable */
		self::$plugin_dir = $msb_livraison_plugin_dir;
		self::$plugin_url = $msb_livraison_plugin_url;

		/* load shipping calculator setting */
		$this->msb_settings = get_option(self::$msb_option_key);

		self::$APIurl = $this->get_setting('APIurl');
		self::$license = $this->get_setting('license');
		self::$login = $this->get_setting('login');
		self::$password = $this->get_setting('password');
		self::$client = $this->get_setting('client');

	}

	public function admin_menu()
	{
		$wc_page = 'woocommerce';
		add_submenu_page($wc_page, self::$plugin_title, self::$plugin_title, "install_plugins", self::$plugin_slug, array($this, "calculator_setting_page"));
	}


    public function calculator_setting_page()
	{
		/* save shipping calculator setting */
		if (isset($_POST[self::$plugin_slug])) {
			$this->saveSetting();
			wp_redirect($_SERVER['HTTP_REFERER']);
		}
		 
		/* include admin  shipping calculator setting file */
		include_once self::$plugin_dir . "admin/views/shipping-setting.php";
	}

	/* function for save setting */

	public function saveSetting()
	{
		$arrayRemove = array(self::$plugin_slug, "btn-msb_livraison-submit");
		$saveData = array();
		foreach ($_POST as $key => $value):
			if (in_array($key, $arrayRemove))
				continue;
			$saveData[$key] = $value;
		endforeach;
		$this->msb_settings = $saveData;
		update_option(self::$msb_option_key, $saveData);
	}

	public function get_setting($key)
	{

		if (!$key || $key == "")
			return;

		if (!isset($this->msb_settings[$key]))
			return;

		return $this->msb_settings[$key];
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
		 * defined in Msb_livraison_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Msb_livraison_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/msb_livraison-admin.css', array(), $this->version, 'all' );

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
		 * defined in Msb_livraison_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Msb_livraison_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/msb_livraison-admin.js', array( 'jquery' ), $this->version, false );

	}

}
