<?php
if (!defined('ABSPATH')) {
    exit;
}




if (!class_exists('Msb_livraison_shipping_calculator')) {

    class Msb_livraison_shipping_calculator
    {

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
        public static $calculator_metakey = "__calculator_hide";

        public function __construct()
        {
            global $msb_livraison_plugin_dir, $msb_livraison_plugin_url;

            /* plugin url and directory variable */
            self::$plugin_dir = $msb_livraison_plugin_dir;
            self::$plugin_url = $msb_livraison_plugin_url;

            /* load shipping calculator setting */
            $this->msb_settings = get_option(self::$msb_option_key);

            // echo '<pre>';
            // var_export($this->msb_settings);
            // die;

            self::$APIurl = $this->get_setting('APIurl');
            self::$license = $this->get_setting('license');
            self::$login = $this->get_setting('login');
            self::$password = $this->get_setting('password');
            self::$client = $this->get_setting('client');

            /* localization */
            add_action('init', array($this, 'load_plugin_textdomain'));

            /* hook for calculate shipping with ajax */
            add_action('wp_ajax_nopriv_ajax_calc_shipping', array($this, 'ajax_calc_shipping'));
            add_action('wp_ajax_ajax_calc_shipping', array($this, 'ajax_calc_shipping'));
            /* hook update shipping method */
            add_action('wp_ajax_nopriv_update_shipping_method', array($this, 'update_shipping_method'));
            add_action('wp_ajax_update_shipping_method', array($this, 'update_shipping_method'));


            add_action('wp_ajax_nopriv_check_zip_code_availability', array($this, 'check_zip_code_availability'));
            add_action('wp_ajax_check_zip_code_availability', array($this, 'check_zip_code_availability'));

            /* wp_footer hook */
            add_action("wp_footer", array($this, "wp_footer"));

            /* wp_header hook used for include css */
            add_action("wp_head", array($this, "wp_head"));

            /* register admin css and js for shipping calculator */
            add_action('admin_enqueue_scripts', array($this, 'admin_script'));

            /* shipping calculato shortcode */
            add_shortcode("shipping-calculator", array($this, "msb_shipping_calculator"));
            add_shortcode("msb-shipping-info", array($this, "msb_shipping_info"));
            add_shortcode("msb-shipping", array($this, "msb_shipping"));
            add_shortcode("msb-create-mission", array($this, "msb_create_mission"));

            add_action('woocommerce_product_options_general_product_data', array($this, 'add_custom_price_box'));
            add_action('woocommerce_process_product_meta', array($this, 'custom_woocommerce_process_product_meta'), 2);
            /* check shipping button display on product page */
            if ($this->get_setting("enable_on_productpage") == 1) {
                /* hook for display shipping button on product page */

                if ($this->get_setting("button_pos") == 1)
                    add_action('woocommerce_single_product_summary', array(&$this, 'display_shipping_calculator'), 100);
                elseif ($this->get_setting("button_pos") == 2)
                    add_action('woocommerce_single_product_summary', array(&$this, 'display_shipping_calculator'), 20);
                else
                    add_action('woocommerce_single_product_summary', array(&$this, 'display_shipping_calculator'), 8);
            }

            add_action('woocommerce_product_bulk_edit_end', array($this, 'output_bulk_shipping_fields'));
            add_action('woocommerce_product_bulk_edit_save', array($this, 'save_bulk_shipping_fields'));

            add_action('manage_product_posts_custom_column', array($this, 'output_quick_shipping_values'));
            add_action('woocommerce_product_quick_edit_end', array($this, 'output_quick_shipping_fields'));
            add_action('woocommerce_product_quick_edit_save', array($this, 'save_quick_shipping_fields'));


            add_action('woocommerce_before_add_to_cart_button', array($this, 'msb_woocommerce_before_add_to_cart_button'));
            add_action('woocommerce_after_add_to_cart_button', array($this, 'msb_woocommerce_after_add_to_cart_button'));


            add_filter('woocommerce_add_to_cart_validation',           array($this, 'msb_add_to_cart_validation'), 10, 4);
            add_filter('woocommerce_add_cart_item_data',               array($this, 'msb_add_cart_item_data'), 10, 3);
            add_filter('woocommerce_get_item_data',                    array($this, 'msb_get_item_data'), 10, 2);
            add_action('woocommerce_checkout_create_order_line_item',  array($this, 'msb_checkout_create_order_line_item'), 10, 4);
            add_filter('woocommerce_order_item_name',                  array($this, 'msb_order_item_name'), 10, 2);

            // add_filter( 'woocommerce_package_rates',  array($this, 'msb_shipping_when_free_is_available'), 100 );
            add_filter('woocommerce_cart_ready_to_calc_shipping', array($this, 'msb_disable_shipping_calc_on_cart'), 99);

            // add_filter( 'woocommerce_shipping_rate_cost',               array($this, 'msb_wc_shipping_cost_tiers', 10, 2 ) );

            add_action('woocommerce_order_status_changed',   array($this, "msb_order_status_changed"), 10, 3);

            // add_action( 'woocommerce_order_status_completed',   array($this, 'msb_order_status_changed') );
            // add_action( 'woocommerce_checkout_order_processed ',   array($this, 'msb_order_status_changed') );

            add_action('woocommerce_order_details_after_order_table',   array($this, 'msb_order_details_after_order_table'));

            // add_action( 'woocommerce_order_details_after_order_table_items',   array($this, 'msb_order_details_after_order_table') );
        }

        /**
         * Add a custom text input field to the product page
         */
        public function msb_woocommerce_before_add_to_cart_button()
        {
            echo do_shortcode('[msb-shipping-info]');
        }

        public function msb_woocommerce_after_add_to_cart_button()
        {
            echo do_shortcode('[msb-shipping]');
            // echo do_shortcode('[msb-create-mission]'); 
        }

        public function msb_add_to_cart_validation($passed, $product_id, $quantity, $variation_id = null)
        {

            // echo '<pre>';
            // var_dump($_POST);
            // echo '</pre>';
            // die;

            // if( empty( $_POST['delivery_slot'] ) ) {
            //     $passed = false;
            //     wc_add_notice( __( 'delivery_slot is a required field.', 'msb_livraison' ), 'error' );
            // }

            // if( empty( $_POST['shipping_dates'] ) ) {
            //     $passed = false;
            //     wc_add_notice( __( 'shipping_dates is a required field.', 'msb_livraison' ), 'error' );
            // }

            if (empty($_POST['postal_code'])) {
                $passed = false;
                wc_add_notice(__('Code postal is a required field.', 'msb_livraison'), 'error');
            }

            if (empty($_POST['city_name'])) {
                $passed = false;
                wc_add_notice(__('Ville is a required field.', 'msb_livraison'), 'error');
            }

            // if( empty( $_POST['shipping_avalablity_message'] ) ) {
            //     $passed = false;
            //     wc_add_notice( __( 'shipping_avalablity_message is a required field.', 'msb_livraison' ), 'error' );
            // }

            return $passed;
        }

        /**
         * Add custom cart item data
         */
        public function msb_add_cart_item_data($cart_item_data, $product_id, $variation_id)
        {

            if (isset($_POST['delivery_slot'])) {
                $cart_item_data['msb_delivery_slot'] = sanitize_text_field($_POST['delivery_slot']);
            }

            if (isset($_POST['shipping_dates'])) {
                $cart_item_data['msb_shipping_dates'] = sanitize_text_field($_POST['shipping_dates']);
            }

            if (isset($_POST['postal_code'])) {
                $cart_item_data['msb_postal_code'] = sanitize_text_field($_POST['postal_code']);
            }

            if (isset($_POST['city_name'])) {
                $cart_item_data['msb_city_name'] = sanitize_text_field($_POST['city_name']);
            }

            if (isset($_POST['shipping_avalablity_message'])) {
                $cart_item_data['msb_shipping_avalablity_message'] = sanitize_text_field($_POST['shipping_avalablity_message']);
            }

            if (isset($_POST['product_note'])) {
                $cart_item_data['msb_product_note'] = sanitize_text_field($_POST['product_note']);
            }

            return $cart_item_data;
        }

        /**
         * Display custom item data in the cart
         */
        public function msb_get_item_data($item_data, $cart_item_data)
        {

            if (isset($cart_item_data['msb_shipping_dates']) && !empty($cart_item_data['msb_shipping_dates'])) {
                $item_data[] = array(
                    'key'     => 'hidden',
                    'value'   =>  __('À livrer le ', 'msb_livraison') . wc_clean($cart_item_data['msb_shipping_dates'])
                );
            }

            if (isset($cart_item_data['msb_delivery_slot']) && !empty($cart_item_data['msb_delivery_slot'])) {
                $item_data[] = array(
                    'key'     => 'hidden',
                    'value'   => __('Entre ', 'msb_livraison') . wc_clean($cart_item_data['msb_delivery_slot'])
                );
            }


            // if( isset( $cart_item_data['msb_postal_code'] ) ) { 
            //     $item_data[] = array(
            //        'key'     => 'hidden',
            //         'value'   => wc_clean( $cart_item_data['msb_postal_code'] )
            //     );
            // }

            // if( isset( $cart_item_data['msb_city_name'] ) ) { 
            //        'key'     => 'hidden',
            //         'key'     => __( 'city name', 'msb_livraison' ),
            //         'value'   => wc_clean( $cart_item_data['msb_city_name'] )
            //     );
            // }

            if (isset($cart_item_data['msb_shipping_avalablity_message'])  && !empty($cart_item_data['msb_shipping_avalablity_message'])) {
                $item_data[] = array(
                    'key'     => 'hidden',
                    'value'   => wc_clean($cart_item_data['msb_shipping_avalablity_message'])
                );
            }

            if (isset($cart_item_data['msb_product_note'])  && !empty($cart_item_data['msb_product_note'])) {
                $item_data[] = array(
                    'key'     => 'hidden',
                    'value'   => wc_clean($cart_item_data['msb_product_note'])
                );
            }


            return $item_data;
        }

        /**
         * Add custom meta to order
         */
        public function msb_checkout_create_order_line_item($item, $cart_item_key, $values, $order)
        {

            if (isset($values['msb_shipping_dates'])) {
                $item->add_meta_data(__('À livrer le', 'msb_livraison'), $values['msb_shipping_dates'], true);
            }
            if (isset($values['msb_delivery_slot'])) {
                $item->add_meta_data(__('Entre', 'msb_livraison'), $values['msb_delivery_slot'], true);
            }
            if (isset($values['msb_postal_code'])) {
                $item->add_meta_data(__('Code postal', 'msb_livraison'), $values['msb_postal_code'], true);
            }
            if (isset($values['msb_city_name'])) {
                $item->add_meta_data(__('Ville', 'msb_livraison'), $values['msb_city_name'], true);
            }
            if (isset($values['msb_shipping_avalablity_message'])) {
                $item->add_meta_data(__('Frais de port', 'msb_livraison'), $values['msb_shipping_avalablity_message'], true);
            }
            if (isset($values['msb_product_note'])) {
                $item->add_meta_data(__('Note', 'msb_livraison'), $values['msb_product_note'], true);
            }
        }

        /**
         * Add custom cart item data to emails
         */
        public function msb_order_item_name($product_name, $item)
        {

            if (isset($item['msb_shipping_dates'])) {
                $product_name .= sprintf('<ul><li>%s : %s</li></ul>', __('À livrer le', 'msb_livraison'), esc_html($item['msb_shipping_dates']));
            }
            if (isset($item['msb_delivery_slot'])) {
                $product_name .= sprintf('<ul><li>%s : %s</li></ul>', __('Entre', 'msb_livraison'), esc_html($item['msb_delivery_slot']));
            }
            if (isset($item['msb_postal_code'])) {
                $product_name .= sprintf('<ul><li>%s : %s</li></ul>', __('Code postal', 'msb_livraison'), esc_html($item['msb_postal_code']));
            }
            if (isset($item['msb_city_name'])) {
                $product_name .= sprintf('<ul><li>%s : %s</li></ul>', __('Ville', 'msb_livraison'), esc_html($item['msb_city_name']));
            }
            if (isset($item['msb_shipping_avalablity_message'])) {
                $product_name .= sprintf('<ul><li>%s : %s</li></ul>', __('Frais de port', 'msb_livraison'), esc_html($item['msb_shipping_avalablity_message']));
            }
            if (isset($item['msb_product_note'])) {
                $product_name .= sprintf('<ul><li>%s : %s</li></ul>', __('Note', 'msb_livraison'), esc_html($item['msb_product_note']));
            }
            return $product_name;
        }

        /**
         * Filters the shipping method cost to account for tiered quantity pricing.
         * 
         * @param float $cost the shipping method cost
         * @param \WC_Shipping_Rate $method the shipping method
         * @return float cost
         */
        public function msb_wc_shipping_cost_tiers($cost, $method)
        {

            // TODO: change the numbers in this array with your desired instance IDs
            // see if this shipping instance is one we want to modify cost for
            if (in_array($method->get_instance_id(), array(22, 23)) && WC()->cart) {

                $cart_item_count = WC()->cart->get_cart_contents_count();

                // if we have items that need shipping, round the quantity / 2 to the nearest whole number
                // this produces tiered cost increases for every 2 items
                if ($cart_item_count > 1) {
                    $cost = round($cart_item_count / 2) * $cost;
                }
            }

            return 100;
        }

        /*
            * Created on Fri Mar 12 2021 12:42:27 PM
            * Remove Shipping from Woocommerce cart
            *
            * Copyright (c) 2021 MSB
            */
        public function msb_disable_shipping_calc_on_cart($show_shipping)
        {
            if (is_cart() || is_checkout()) {
                return false;
            }
            return $show_shipping;
        }

        /*
            * Created on Fri Mar 12 2021 12:42:27 PM
            * Hide shipping rates when free shipping is available.
            *
            * Copyright (c) 2021 MSB
            */
        public function msb_shipping_when_free_is_available($rates)
        {
            $free = array();
            foreach ($rates as $rate_id => $rate) {
                if ('free_shipping' === $rate->method_id) {
                    $free[$rate_id] = $rate;
                    break;
                }
            }
            return !empty($free) ? $free : $rates;
        }

        /*
            * Created on Fri Mar 12 2021 12:42:27 PM
            * Whene the order status are changed
            *
            * Copyright (c) 2021 MSB
            */
        public function msb_order_status_changed($order_id, $old_status, $new_status)
        {

            if ($new_status == "completed") { 

                // API Callout to URL
                $url_auth = 'https://espace-clients.topchrono.fr/WebManager/WCFDispatchAPI.svc/REST_HTTPS/Json/Authentify';
                $credential = array(
                    'Credential' => array(
                        'License' => self::$license,
                        'Login' => self::$login,
                        'Password' => self::$password,
                        'Language' => 'fr-FR',
                    )
                );

                $authentificationRequest = json_encode($credential);

                $response_auth = wp_remote_post(
                    $url_auth,
                    array(
                        'headers'    => array('Content-Type' => 'application/json; charset=utf-8'),
                        'method'     => 'POST',
                        'timeout'    => 75,
                        'body'       => $authentificationRequest,
                    )
                );

                $vars_auth = json_decode($response_auth['body'], true);
                $ConnectionToken = $vars_auth['ConnectionToken'];

                if ($ConnectionToken != "") {
                    // Order Setup Via WooCommerce
                    $order = new WC_Order($order_id);

                    // Iterate Through Items
                    $items = $order->get_items();

                    $billing_first_name     = $order->billing_first_name;
                    $billing_last_name      = $order->billing_last_name;
                    $billing_email          = $order->billing_email;
                    $billing_postcode       = $order->billing_postcode;
                    $billing_city           = $order->billing_city;

                    $shipping_first_name    = $order->shipping_first_name;
                    $shipping_last_name     = $order->shipping_last_name;
                    $shipping_address       = $order->shipping_address_1;
                    $shipping_address_2     = $order->shipping_address_2;
                    $shipping_email         = $order->shipping_email;
                    $shipping_postcode      = $order->shipping_postcode;
                    $shipping_city          = $order->shipping_city;

                    foreach ($items as $key => $item) {

                        // Store Product ID
                        $product_id = $item['product_id'];
                        $product = new WC_Product($item['product_id']);
                        $projectsku = $product->get_sku();

                        $to_be_delivered_on = $item->get_meta('À livrer le');
                        $between = $item->get_meta('Entre');

                        $date = ($to_be_delivered_on) ? $to_be_delivered_on : '';
                        $begin_hour = ($between) ? explode('-', $between) : array('10:00');

                        $string_date = $date;
                        $string_hour = $begin_hour[0] . ":00";

                        $createShipmentRequest = "";

                        //Identification 
                        $createShipmentRequest = array(
                            'Credential' => array(
                                'ConnectionToken' => $ConnectionToken,
                                'License' => self::$license,
                                'Language' => 'fr-FR',
                            ),
                            //Indique que l'on va saisir une mission et non un devis
                            'Quote' => false,
                            //Indique la mission va être enregistré dans Dispatch
                            'Save' => true,
                        );

                        $shipment = array(
                        /*
                        * à partir de la version 2.4.4 de dispatch et de la version 46 de l'API
                        * Ce mode permet d'utiliser la class ShipmentSchedule de l'objet shipment,
                        * Cette classe permet de manipuler plus facilement les dates de la mission et de définir des créneaux d'enlèvement livraison
                        * Ce mode doit être utilisé pour tout nouveau développement 
                        */
                            'AdvancedDateMode' => true,
                            //Code Client Dispatch

                            'ClientCode' => self::$client,

                        );


                        // if (preg_match('/^(.*?)((?:unit )?(?:[0-9]+\s?-\s?[0-9]+|[0-9]+))(.*)$/', get_option('woocommerce_store_address'), $result)) {
                        //     $streetNumber = $result[2];
                        //     if (trim($result[3]) == '') {
                        //         $streetName = $result[1];
                        //     } else {
                        //         $streetName = $result[3];
                        //     }
                        // }

                        //Adresse d'enlèvement
                        $pickupAddress = array(

                            'AddressLine1' => get_option('woocommerce_store_address'),
                            'AddressLine2' => get_option('woocommerce_store_address_2'),
                            'Sector' => '',

                            "No" => get_option('woocommerce_store_address'),
                            "Street" => get_option('woocommerce_store_address_2'),
                            "PostalCode" => get_option('woocommerce_store_postcode'),
                            "City" => get_option('woocommerce_store_city'),
                            "Country" => "FR"

                        );

                        $shipment['FromAddress'] = $pickupAddress;

                        $contractual_start_date = array();

                        // if ($string_date != '') {
                        //     $contractual_start_date['StringDate'] = $string_date;
                        // }

                        // if ($string_hour != '') {
                        //     $contractual_start_date['StringHour'] = $string_hour;
                        // }

                        if ($string_date != '' && $string_hour != '') {
                            $pickup_contractual_start_date['StringFullDate'] = $string_date  . ' ' . $string_hour;
                            $delivery_contractual_start_date['StringFullDate'] = $string_date  . ' ' . $string_hour;
                        }

                        //Date d'enlèvement
                        $shipment['PickupSchedules']['ContractualStartDate'] = $pickup_contractual_start_date;

                        //Date livraison
                        $shipment['DeliverySchedules']['ContractualStartDate'] = $delivery_contractual_start_date;

                        //Adresse de livraison
                        $deliveryAddress = array(
                            'Name' => $shipping_first_name . ' ' . $shipping_last_name,
                            'EMail' => $shipping_email,
                            'Sector' => '',

                            'No' => $shipping_address,
                            'Street' =>  $shipping_address_2,
                            'PostalCode' => $shipping_postcode,
                            'City' => $shipping_city,
                            'Country' => 'FR',
                        );

                        $shipment['ToAddress'] = $deliveryAddress;
                        //Code Prestation Dispatch
                        $shipment['ServiceCode'] = 'ZV';

                        $createShipmentRequest['Shipment'] = $shipment;

                        //La sauvegarde de mission renverra le prix ttc dans l'objet shipment
                        $createShipmentRequest['ComputePriceWithTaxes'] = true;
                        $createShipmentRequest = json_encode($createShipmentRequest);

                        // API Callout to URL
                        $url = 'https://espace-clients.topchrono.fr/WebManager/WCFDispatchAPI.svc/REST_HTTPS/json/CreateShipment';

                        $response = wp_remote_post(
                            $url,
                            array(
                                'headers'    => array('Content-Type' => 'application/json; charset=utf-8'),
                                'method'     => 'POST',
                                'timeout'    => 75,
                                'body'        => $createShipmentRequest,
                            )
                        );

                        $vars = json_decode($response['body'], true);

                        // API Response Stored as Post Meta
                        update_post_meta($order_id, 'TrackId' . '_' . $key, $vars['Shipment']['TrackId']);
                        update_post_meta($order_id, 'TotalAmountWithTaxes' . '_' . $key, $vars['TotalAmountWithTaxes']);

                    }
                }
            }
        }

        /*
        * Created on Fri Mar 12 2021 12:42:27 PM
        * Affichage du suivi de livraison dans la commande
        *
        * Copyright (c) 2021 MSB
        */
        public function msb_order_details_after_order_table($order)
        {

            // API Callout to URL
            $url_auth = 'https://espace-clients.topchrono.fr/WebManager/WCFDispatchAPI.svc/REST_HTTPS/Json/Authentify';
            $credential = array(
                'Credential' => array(
                    'License' => self::$license,
                    'Login' => self::$login,
                    'Password' => self::$password,
                )
            );

            $authentificationRequest = json_encode($credential);

            $response_auth = wp_remote_post(
                $url_auth,
                array(
                    'headers'    => array('Content-Type' => 'application/json; charset=utf-8'),
                    'method'     => 'POST',
                    'timeout'    => 75,
                    'body'        => $authentificationRequest,
                )
            );

            $vars_auth = json_decode($response_auth['body'], true);
            $ConnectionToken = $vars_auth['ConnectionToken'];

            if ($ConnectionToken != "") {
                // $searchClient = array( 
                //     'Credential' => array(
                //         'ConnectionToken' => $ConnectionToken,
                //         'License' => self::$license,
                //         'Language' => 'fr-FR',
                //     ),
                // ); 

                // $searchClient = json_encode($searchClient);

                // // API Callout to URL
                // $urlClient = 'https://espace-clients.topchrono.fr/WebManager/WCFDispatchAPI.svc/REST_HTTPS/Json/GetClientService';

                // $responseClient = wp_remote_post( $urlClient, 
                //     array(
                //         'headers'    => array('Content-Type' => 'application/json; charset=utf-8'),
                //         'method'     => 'POST',
                //         'timeout'    => 75,				    
                //         'body'		=> $searchClient,
                //     )
                // );

                // $varsClient = json_decode($responseClient['body'],true); 

                // echo '<pre>'  ;
                // echo  var_dump($varsClient)  ;
                // echo  '</pre>';

                $items = $order->get_items();
                $inc = 0;

                foreach ($items as $key => $item) {
                    $inc++;

                    $product = $item->get_product();

                    $TrackId[0] = $order->get_meta('TrackId' . '_' . $key);

                    if (!empty($TrackId[0])) {

                        $searchRequest = array(
                            'Credential' => array(
                                'ConnectionToken' => $ConnectionToken,
                                'License' => self::$license,
                                'Language' => 'fr-FR',
                            ),
                            'LoadShipmentHistory' => true,
                            //Critères de recherche
                            'SearchParams' => array(
                                'ClientList' => array(self::$client),
                                'TrackIdList' => $TrackId,
                            ),
                        );

                        $searchRequest = json_encode($searchRequest);

                        // API Callout to URL
                        $url = 'https://espace-clients.topchrono.fr/WebManager/WCFDispatchAPI.svc/REST_HTTPS/Json/Shipments';

                        $response = wp_remote_post(
                            $url,
                            array(
                                'headers'    => array('Content-Type' => 'application/json; charset=utf-8'),
                                'method'     => 'POST',
                                'timeout'    => 75,
                                'body'        => $searchRequest,
                            )
                        );

                        $vars = json_decode($response['body'], true);

                        if ($vars['ShipmentList']) {
                            $shipmentList = $vars['ShipmentList'][0];

                            $trackingUrl = $shipmentList['TrackingUrl'];
                            $shipmentEventList = $shipmentList['ShipmentEventList'];

                            if ($inc == 1) {  ?>
                                <h2 class="woocommerce-order-details__title"><?php esc_html_e('Suivi de commande', 'woocommerce'); ?></h2>
                            <?php } ?>

                            <?php
                            $is_visible = true;
                            $product_permalink = apply_filters('woocommerce_order_item_permalink', $is_visible ? $product->get_permalink($item) : '', $item, $order);
                            echo apply_filters('woocommerce_order_item_name', $product_permalink ? sprintf('<a href="%s" target="_blank"><h3 class="">%s</h3></a>', $product_permalink, $item->get_name()) : $item->get_name(), $item, $is_visible); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            // wc_display_item_meta( $item ); 
                            ?>


                            <?php if ($shipmentEventList) { ?>

                                <table class="shipment_event_list">
                                    <tr>
                                        <th>StringFullDate</th>
                                        <th>EventName</th>
                                        <th>Comment</th>
                                    </tr>

                                    <?php foreach ($shipmentEventList as $key => $shipmentEvent) { ?>
                                        <tr>
                                            <td><?= $shipmentEvent['LocalDate']['StringFullDate'] ?></td>
                                            <td><?= $shipmentEvent['EventName'] ?></td>
                                            <td><?= $shipmentEvent['Comment'] ?></td>
                                        </tr>
                                    <?php } ?>
                                </table>

                            <?php }  ?>

                            <a href="<?= $trackingUrl ?>" target="_blank">URL de suivi</a>
                            <p>
                                    </p>

                <?php }
                    }
                }
            }
        }

        /**
         * Handle localisation
         */
        public function load_plugin_textdomain()
        {
            load_plugin_textdomain('msb_livraison', false, dirname(plugin_basename(__FILE__)) . '/i18n/');
        }

        public function save_quick_shipping_fields($product)
        {
            $product_id = $product->id;

            if ($product_id > 0) {
                $metavalue = isset($_REQUEST[self::$calculator_metakey]) ? "yes" : "no";
                update_post_meta($product_id, self::$calculator_metakey, $metavalue);
            }
        }

        public function output_quick_shipping_fields()
        {
            include self::$plugin_dir . "views/quick-settings.php";
        }

        public function output_quick_shipping_values($column)
        {
            global $post;

            $product_id = $post->ID;
            if ($column == 'name') {
                $estMeta = get_post_meta($product_id, self::$calculator_metakey, true);
                ?>
                <div class="hidden" id="ewcwoo_shipping_inline_<?php echo $product_id; ?>">
                    <div class="_shipping_enable"><?php echo $estMeta; ?></div>
                </div>
            <?php
            }
        }

        public function output_bulk_shipping_fields()
        {
            include self::$plugin_dir . "views/bulk-settings.php";
        }

        public function save_bulk_shipping_fields($product)
        {
            $product_id = $product->id;
            if ($product_id > 0) {
                $metavalue = isset($_REQUEST[self::$calculator_metakey]) ? "yes" : "no";
                update_post_meta($product_id, self::$calculator_metakey, $metavalue);
            }
        }

        public function custom_woocommerce_process_product_meta($post_id)
        {
            $metavalue = isset($_POST[self::$calculator_metakey]) ? "yes" : "no";
            update_post_meta($post_id, self::$calculator_metakey, $metavalue);
        }

        public function add_custom_price_box()
        {
            $hide_calculator = "yes";
            if (isset($_GET["post"]))
                $hide_calculator = get_post_meta($_GET["post"], self::$calculator_metakey, true);
            woocommerce_wp_checkbox(array('id' => self::$calculator_metakey, 'value' => $hide_calculator, 'label' => __('Hide Shipping Calculator', 'ewchpa_hide_calculator')));
        }

        public function update_shipping_method()
        {
            WC_Shortcode_Cart::calculate_shipping();

            if (isset($_POST["product_id"]) && $this->check_product_incart($_POST["product_id"]) === false) {
                $qty = (isset($_POST['current_qty']) && $_POST['current_qty'] > 0) ? $_POST['current_qty'] : 1;
                if (isset($_POST['variation_id']) && $_POST['variation_id'] != "" && $_POST['variation_id'] > 0) {
                    $cart_item_key = WC()->cart->add_to_cart(sanitize_text_field($_POST["product_id"]), $qty, sanitize_text_field($_POST['variation_id']));
                } else {
                    $cart_item_key = WC()->cart->add_to_cart(sanitize_text_field($_POST["product_id"]), $qty);
                }


                $packages = WC()->cart->get_shipping_packages();
                $packages = WC()->shipping->calculate_shipping($packages);
                $available_methods = WC()->shipping->get_packages();
                WC()->cart->remove_cart_item($cart_item_key);
            } else {
                $packages = WC()->cart->get_shipping_packages();
                $packages = WC()->shipping->calculate_shipping($packages);
                $available_methods = WC()->shipping->get_packages();
            }

            wc_clear_notices();
            if ($this->get_setting('shipping_type') == 2) {
                if (isset($available_methods[0]["rates"]) && count($available_methods[0]["rates"]) > 0) {
                    $count = 0;
                    echo '<ul class="shipping_with_price">';
                    foreach ($available_methods[0]["rates"] as $key => $method) {
                        echo "<li>";
                        echo wp_kses_post($method->label) . "&nbsp;<strong>(" . $method->cost . ")</strong>";
                        echo "</li>";
                    }
                    echo '</ul>';
                }
            } else if ($this->get_setting('shipping_type') == 1) {
                if (isset($available_methods[0]["rates"]) && count($available_methods[0]["rates"]) > 0) {
                    $count = 0;
                    foreach ($available_methods[0]["rates"] as $key => $method) {
                        $checked = ($count == 0) ? "checked=checked" : "";
                        echo '<input name="calc_shipping_method" type="radio" ' . $checked . ' ' . checked($key, WC()->session->chosen_shipping_method, false) . ' value="' . esc_attr($key) . '">&nbsp;' . wp_kses_post($method->label) . "<br>";
                        $count++;
                    }
                }
            } else {
            ?>
                <select name="calc_shipping_method" id="calc_shipping_method" class="shipping_method">
                    <option value=""><?php _e('Select a Shipping Method&hellip;', 'woocommerce'); ?></option>
                    <?php
                    if (isset($available_methods[0]["rates"]) && count($available_methods[0]["rates"]) > 0) {

                        foreach ($available_methods[0]["rates"] as $key => $method) {
                            echo '<option value="' . esc_attr($key) . '" ' . selected($key, WC()->session->chosen_shipping_method, false) . '>' . wp_kses_post($method->label) . '</option>';
                        }
                    }
                    ?>
                </select>
            <?php
            }
            die();
        }

        public function check_zip_code_availability()
        {
            WC_Shortcode_Cart::calculate_shipping();
            if (isset($_POST["product_id"]) && $this->check_product_incart($_POST["product_id"]) === false) {
                $qty = (isset($_POST['current_qty']) && $_POST['current_qty'] > 0) ? $_POST['current_qty'] : 1;
                if (isset($_POST['variation_id']) && $_POST['variation_id'] != "" && $_POST['variation_id'] > 0) {
                    $cart_item_key = WC()->cart->add_to_cart(sanitize_text_field($_POST["product_id"]), $qty, sanitize_text_field($_POST['variation_id']));
                } else {
                    $cart_item_key = WC()->cart->add_to_cart(sanitize_text_field($_POST["product_id"]), $qty);
                }

                $packages = WC()->cart->get_shipping_packages();
                $packages = WC()->shipping->calculate_shipping($packages);
                $available_methods = WC()->shipping->get_packages();
                WC()->cart->remove_cart_item($cart_item_key);
            } else {
                $packages = WC()->cart->get_shipping_packages();
                $packages = WC()->shipping->calculate_shipping($packages);
                $available_methods = WC()->shipping->get_packages();
            }

            wc_clear_notices();


            $returnResponse = array();
            // echo '<pre>';
            // var_dump($available_methods[0]["rates"]);
            // echo '</pre>'; 

            if (!$available_methods[0]["rates"]) {
                /* check shipping method and country selected */
                $returnResponse = array("code" => "error", "message" => __("Nous ne livrons pas encore chez vous", "msb_livraison"));
            } else {

                foreach ($available_methods[0]["rates"] as $key => $value) {

                    if (!$available_methods[0]["rates"][$key]) {

                        /* check shipping method and country selected */
                        $returnResponse = array("code" => "error", "message" => __("Nous ne livrons pas encore chez vous", "msb_livraison"));
                    } elseif ($_POST["calc_shipping_postcode"] == "") {

                        $returnResponse = array("code" => "error", "message" => __("Please select shipping country", "msb_livraison"));
                    } else {

                        $method_id   = $value->method_id;

                        if ($method_id == 'flat_rate') {
                            $country = sanitize_text_field($_POST["calc_shipping_country"]);

                            /* calculate shipping */
                            $shippingCharge = $this->get_shipping_text(sanitize_text_field($key), $country);
                            /* get country full name */
                            $country = WC()->countries->countries[$country];

                            if (isset($shippingCharge['label'])) {
                                if (trim($this->get_setting("shipping_message")) != "") {
                                    $message = str_replace(array("[shipping-method]", "[shipping-cost]", "[shipping-country]"), array($shippingCharge["label"], $shippingCharge["cost"], $country), $this->get_setting("shipping_message"));
                                } else {
                                    $message = $shippingCharge["label"] . " : " . $shippingCharge["cost"];
                                    $cost = $shippingCharge["cost"];
                                    $id = $shippingCharge["id"];
                                }

                                $returnResponse = array("code" => "success", "message" => __($message, "msb_livraison"), "cost" =>  $cost, "id" =>  $id);
                            } else if (isset($shippingCharge['code'])) {
                                $returnResponse = array("code" => "error", "message" => __($shippingCharge['message'], "msb_livraison"));
                            } else {
                                $returnResponse = array("code" => "error", "message" => __("Selected Shipping method not available.", "msb_livraison"));
                            }
                            break;
                        }
                    }
                }
            }

            echo json_encode($returnResponse);


            die();
        }

        /* function for display shipping calculator on product page */
        public function display_shipping_calculator()
        {
            global $product;

            $id = (WC()->version < '2.7.0') ? $product->id : $product->get_id();
            if (get_post_meta($id, self::$calculator_metakey, true) != "yes")
                include_once self::$plugin_dir . 'public/views/shipping-calculator.php';
        }

        public function msb_shipping_calculator()
        {
            ob_start();
            include_once self::$plugin_dir . 'public/views/shipping-calculator.php';
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        //Return string for shortcode
        public function msb_shipping()
        {

            wp_enqueue_script('msb_livraison_moment');
            wp_enqueue_script('msb_livraison_vuejs');
            wp_enqueue_script('msb_livraison_vmoment');
            wp_enqueue_script('msb_livraison_jquery-ui');
            wp_enqueue_script('msb_livraison_autocomplete');
            wp_enqueue_script('msb_livraison_vcalendar');
            wp_enqueue_script('msb_livraison_lodash');
            wp_enqueue_script('msb_livraison_axios');
            wp_enqueue_script('msb_livraison_form');

            ob_start();
            include_once self::$plugin_dir . 'public/views/shipping.php';
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        public function msb_shipping_info()
        {
            ob_start();
            include_once self::$plugin_dir . 'public/views/shipping-info.php';
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        public function msb_create_mission()
        {

            wp_enqueue_script('msb_livraison_moment');
            wp_enqueue_script('msb_livraison_vuejs');
            wp_enqueue_script('msb_livraison_vmoment');
            wp_enqueue_script('msb_livraison_jquery-ui');
            wp_enqueue_script('msb_livraison_autocomplete');
            wp_enqueue_script('msb_livraison_vcalendar');
            wp_enqueue_script('msb_livraison_lodash');
            wp_enqueue_script('msb_livraison_axios');
            wp_enqueue_script('msb_livraison_create_shipment');


            ob_start();
            include_once self::$plugin_dir . 'public/views/create-mission.php';
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        /* calculate shipping */
        public function ajax_calc_shipping()
        {
            $returnResponse = array();
            if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "ajax_calc_shipping") :


                /* check shipping method and country selected */
                if ($_POST["calc_shipping_method"] == "") {
                    $returnResponse = array("code" => "error", "message" => __("Please select shipping method", "msb_livraison"));
                } elseif ($_POST["calc_shipping_country"] == "") {
                    $returnResponse = array("code" => "error", "message" => __("Please select shipping country", "msb_livraison"));
                } else {
                    $country = sanitize_text_field($_POST["calc_shipping_country"]);

                    /* calculate shipping */
                    $shippingCharge = $this->get_shipping_text(sanitize_text_field($_POST["calc_shipping_method"]), $country);

                    /* get country full name */
                    $country = WC()->countries->countries[$country];

                    if (isset($shippingCharge['label'])) {
                        if (trim($this->get_setting("shipping_message")) != "") {
                            $message = str_replace(array("[shipping-method]", "[shipping-cost]", "[shipping-country]"), array($shippingCharge["label"], $shippingCharge["cost"], $country), $this->get_setting("shipping_message"));
                        } else {
                            $message = $shippingCharge["label"] . " : " . $shippingCharge["cost"];
                        }

                        $returnResponse = array("code" => "success", "message" => __($message, "msb_livraison"));
                    } else if (isset($shippingCharge['code'])) {
                        $returnResponse = array("code" => "error", "message" => __($shippingCharge['message'], "msb_livraison"));
                    } else {
                        $returnResponse = array("code" => "error", "message" => __("Selected Shipping method not available.", "msb_livraison"));
                    }
                }
            endif;
            echo json_encode($returnResponse);
            die();
        }

        public function check_product_incart($product_id)
        {
            foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
                $_product = $values['data'];

                if ($product_id == $_product->get_id()) {
                    return true;
                }
            }
            return false;
        }

        /* function for calculate shiiping */
        public function get_shipping_text($shipping_method, $country)
        {
            global $woocommerce, $post;
            $returnResponse = array();
            WC_Shortcode_Cart::calculate_shipping();


            if (isset($_POST["product_id"]) && $this->check_product_incart($_POST["product_id"]) === false) {
                $qty = (isset($_POST['current_qty']) && $_POST['current_qty'] > 0) ? $_POST['current_qty'] : 1;
                if (isset($_POST['variation_id']) && $_POST['variation_id'] != "" && $_POST['variation_id'] > 0) {
                    $cart_item_key = WC()->cart->add_to_cart(sanitize_text_field($_POST["product_id"]), $qty, sanitize_text_field($_POST['variation_id']));
                } else {
                    $cart_item_key = WC()->cart->add_to_cart(sanitize_text_field($_POST["product_id"]), $qty);
                }
                $packages = WC()->cart->get_shipping_packages();
                $packages = WC()->shipping->calculate_shipping($packages);
                $packages = WC()->shipping->get_packages();
                WC()->cart->remove_cart_item($cart_item_key);
            } else {
                $packages = WC()->cart->get_shipping_packages();
                $packages = WC()->shipping->calculate_shipping($packages);
                $packages = WC()->shipping->get_packages();
            }

            wc_clear_notices();

            if (isset($packages[0]["rates"][$shipping_method])) {

                $selectedShiiping = $packages[0]["rates"][$shipping_method];
                $returnResponse = array("id" => $selectedShiiping->instance_id, "label" => $selectedShiiping->label, "cost" => wc_price($selectedShiiping->cost));
            } else {
                $AllMethod = WC()->shipping->load_shipping_methods();
                $selectedMethod = $AllMethod[$shipping_method];
                $flag = 0;
                if ($selectedMethod->availability == "including") :
                    foreach ($selectedMethod->countries as $methodcountry) {
                        if ($country == $methodcountry) {
                            $flag = 1;
                        }
                    }
                    if ($flag == 0) :
                        $message = $selectedMethod->method_title . " is not available in selected country.";
                        $returnResponse = array("code" => "error", "message" => $message);
                    endif;
                endif;
            }
            return $returnResponse;
        }

        public function admin_script()
        {
            if (is_admin()) {

                // Add the color picker css file       
                wp_enqueue_style('wp-color-picker');

                wp_enqueue_script('msb_livraison-admin', self::$plugin_url . "assets/js/admin.js", array('wp-color-picker'), false, true);
                wp_enqueue_style('msb_livraison-admin', self::$plugin_url . "assets/css/admin.css");
            }
        }

        public function wp_head()
        {
            /* register jquery */
            wp_enqueue_script('jquery');

            $buttonAlign = "left";
            if ($this->get_setting('button_align') == 0)
                $buttonAlign = "left";
            else if ($this->get_setting('button_align') == 1)
                $buttonAlign = "right";
            else if ($this->get_setting('button_align') == 2)
                $buttonAlign = "center";

            $buttonBorder = $this->get_setting('button_border_size');
            $buttonSize = ($buttonBorder != "") ? $buttonBorder . "px" : "0px";
            $buttonColor = $this->get_setting('button_border_color');
            $defaultOpen = ($this->get_setting('default_open') == 1) ? "block" : "none";
            ?>
            <script type="text/javascript">
                var ewc_ajax_url = "<?php echo admin_url("admin-ajax.php") ?>";
            </script>
            <style type="text/css">
                #Msb_livraison_shipping_calculator {
                    margin-top: 10px;
                    max-width: <?php echo $this->get_setting('max_width') ? $this->get_setting('max_width') : 400 ?>px;
                }

                .ewc_shipping_button {
                    margin-bottom: 10px;
                    text-align: <?php echo $buttonAlign; ?>
                }

                .ewc_shiiping_form {
                    display: <?php echo $defaultOpen; ?>;
                }

                .loaderimage {
                    display: none;
                    margin-left: 5px;
                }

                .ewc_message {
                    margin-bottom: 10px;
                }

                .ewc_error {
                    color: red;
                }

                .ewc_success {
                    color: green;
                }

                .ewc_shipping_button .btn_shipping {
                    padding: 8px 10px;
                    text-align: center;
                    display: inline-block;
                    border: <?php echo $buttonSize ?> <?php echo $buttonColor ?> solid;
                    border-radius: <?php echo $this->get_setting('button_border_radius'); ?>px;
                    color: <?php echo $this->get_setting('button_text_color'); ?>;
                    background-color: <?php echo $this->get_setting('button_bg_color'); ?>;
                    cursor: pointer;
                }

                <?php
                if ($this->get_setting('custom_css') != "") :
                    echo $this->get_setting('custom_css');
                endif;
                ?>
            </style>
<?php
        }

        public function wp_footer()
        {
            // wp_enqueue_script('wc-country-select');
            // wp_enqueue_script(self::$plugin_slug, self::$plugin_url . "assets/js/shipping-calculator.js");
        }

        /* function for get setting */
        public function get_setting($key)
        {

            if (!$key || $key == "")
                return;

            if (!isset($this->msb_settings[$key]))
                return;

            return $this->msb_settings[$key];
        }
    }
}
new Msb_livraison_shipping_calculator();
