<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       slashnpro@gamil.com
 * @since      1.0.0
 *
 * @package    Msb_livraison
 * @subpackage Msb_livraison/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Msb_livraison
 * @subpackage Msb_livraison/public
 * @author     Oussama Elmaaroufy <slashnpro@gamil.com>
 */
class Msb_livraison_Public {

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/msb_livraison-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '.fonts.gstatic', 'https://fonts.gstatic.com' );
		wp_enqueue_style( $this->plugin_name . '.fonts','https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Hind:wght@300;400;500;600;700&display=swap' );

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
		 * defined in Msb_livraison_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Msb_livraison_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		//   credential: {
		//     License: 'TOPCHRONO',
		//     Login: 'BAPAPI',
		//     Password: 'Pit@2020',
		//   },
			
		 $msb_livraison_object = array(
			'ajax_url' 		=> admin_url('admin-ajax.php'), 
			'base_api_url' 	=> Msb_livraison_Admin::$APIurl,
			'credential' 		=> array(
				'license' 		=> Msb_livraison_Admin::$license,
				'login' 		=> Msb_livraison_Admin::$login,
				'password' 		=> Msb_livraison_Admin::$password, 
			),
			'clientCode' 		=> Msb_livraison_Admin::$client . 'ere',
		);

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/msb_livraison-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'msb_livraison_object', $msb_livraison_object); 

		wp_register_script( 'msb_livraison_jquery-ui'		, 'https://code.jquery.com/ui/1.12.1/jquery-ui.js');
		wp_register_script( 'msb_livraison_vuejs'			, 'https://cdn.jsdelivr.net/npm/vue@2.6.12/dist/vue.js"');
		wp_register_script( 'msb_livraison_moment'			, 'https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js');
		wp_register_script( 'msb_livraison_fr_moment'			, 'https://cdn.jsdelivr.net/npm/moment-locale-fr@1.0.0/fr.min.js');
		wp_register_script( 'msb_livraison_vmoment'			, 'https://cdn.jsdelivr.net/npm/vue-moment@4.1.0/dist/vue-moment.min.js');
		wp_register_script( 'msb_livraison_vcalendar'		, 'https://cdn.jsdelivr.net/npm/v-calendar@2.1.1/lib/v-calendar.umd.min.js');
		wp_register_script( 'msb_livraison_axios'			, 'https://cdn.jsdelivr.net/npm/axios@0.21.0/dist/axios.min.js' );
		wp_register_script( 'msb_livraison_lodash'			, 'https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js' );
		// wp_register_script( 'axios'							, 'https://unpkg.com/axios/dist/axios.min.js' );
		
		wp_register_script( 'msb_livraison_form'			, plugin_dir_url( __FILE__ ) . 'js/msb_livraison-form.js', array( 'jquery' ), $this->version, false );
		wp_register_script( 'msb_livraison_create_shipment'	, plugin_dir_url( __FILE__ ) . 'js/msb_livraison-create-shipment.js', array( 'jquery' ), $this->version, false );
	}

}
