<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       slashnpro@gmail.com
 * @since      1.0.0
 *
 * @package    Msb_livraison
 * @subpackage Msb_livraison/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Msb_livraison
 * @subpackage Msb_livraison/includes
 * @author     Oussama Elmaaroufy <slashnpro@gmail.com>
 */
class Msb_livraison_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'msb_livraison',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
