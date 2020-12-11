// (function( $ ) {
// 	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

// })( jQuery );

(function ($) {
    $(document).ready(function () {
        $(document).on("change","#calc_shipping_method",function(){
            $('.ewc_calc_shipping').trigger('click');
        });
        $(".btn_shipping").click(function () {
            $(".ewc_shiiping_form").toggle("slow");
        });
        $(".single_variation_wrap").on("show_variation", function (event, variation) {
            $(".loaderimage").show();
            element=$('.country_to_state,.shipping_state select');
            var datastring = element.closest(".woocommerce-shipping-calculator").serialize();
            if($("input.variation_id").length>0){
                datastring=datastring+"&variation_id="+$("input.variation_id").val();
            }
            if($("input[name=quantity]").length>0){
                datastring=datastring+"&current_qty="+$("input[name=quantity]").val();
            }
            $.ajax({
                type: "POST",
                url: ewc_ajax_url+"?action=update_shipping_method",
                data: datastring,
                success: function (data) {
                    $(".loaderimage").hide();
                    element.parent().parent().find('.shippingmethod_container').html(data);
                }
            });
        });

        $('.ewc_calc_shipping').click(function (e) {
            e.preventDefault();
            $(".loaderimage").show();
            var datastring = $(this).closest(".woocommerce-shipping-calculator").serialize();
            console.log(datastring);
            if($("input.variation_id").length>0){
                datastring=datastring+"&variation_id="+$("input.variation_id").val();
            }
            if($("input[name=quantity]").length>0){
                datastring=datastring+"&current_qty="+$("input[name=quantity]").val();
            }
            $.ajax({
                type: "POST",
                url: ewc_ajax_url+"?action=ajax_calc_shipping",
                data: datastring,
                dataType: 'json',
                success: function (data) {
                    $(".loaderimage").hide();
                    $(".ewc_message").removeClass("ewc_error").removeClass("ewc_success");
                    if (data.code == "error") {
                        $(".ewc_message").html(data.message).addClass("ewc_error");
                    } else if (data.code == "success") {
                        $(".ewc_message").html(data.message).addClass("ewc_success");
                    } else {
                        return true;
                    }
                }
            });
            return false;
        });
        
        $('.country_to_state,.shipping_state select').change(function () {
            $(".loaderimage").show();
            element=$(this);
            var datastring = $(this).closest(".woocommerce-shipping-calculator").serialize();
            if($("input.variation_id").length>0){
                datastring=datastring+"&variation_id="+$("input.variation_id").val();
            }
            if($("input[name=quantity]").length>0){
                datastring=datastring+"&current_qty="+$("input[name=quantity]").val();
            }
            $.ajax({
                type: "POST",
                url: ewc_ajax_url+"?action=update_shipping_method",
                data: datastring,
                success: function (data) {
                    $(".loaderimage").hide();
                    element.parent().parent().find('.shippingmethod_container').html(data);
                }
            });
            
        }); 
        $('.shipping_postcode input,.shipping_state input').blur(function () {
            $(".loaderimage").show();
            element=$(this);
            var datastring = $(this).closest(".woocommerce-shipping-calculator").serialize();
            if($("input.variation_id").length>0){
                datastring=datastring+"&variation_id="+$("input.variation_id").val();
            }
            if($("input[name=quantity]").length>0){
                datastring=datastring+"&current_qty="+$("input[name=quantity]").val();
            }
            $.ajax({
                type: "POST",
                url: ewc_ajax_url+"?action=update_shipping_method",
                data: datastring,
                success: function (data) {
                    $(".loaderimage").hide();
                    element.parent().parent().find('.shippingmethod_container').html(data);
                }
            });
            return false;
        });
    });
})(jQuery);