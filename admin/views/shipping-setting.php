
 
<div class="wrap">

    <h1>Shipping Calculator</h1>

    <form action="" method="post">

        <table class="form-table">

            <tbody>

                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="woocommerce_store_address">API url<span class="woocommerce-help-tip"></span></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="text" value="<?= Msb_livraison_shipping_calculator::$APIurl ?>" name="APIurl" id="APIurl" class="regular-text" placeholder="">
                        <p class="description" id="tagline-description">API url</p>
                    </td>
                </tr> 

                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="woocommerce_store_address">License<span class="woocommerce-help-tip"></span></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="text" value="<?= Msb_livraison_shipping_calculator::$license ?>" name="license" id="license" class="regular-text" placeholder="">
                        <p class="description" id="tagline-description">DISPATCH RTS license code</p>
                    </td>
                </tr> 
                
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="woocommerce_store_address">Login<span class="woocommerce-help-tip"></span></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="text" value="<?= Msb_livraison_shipping_calculator::$login ?>" name="login" id="login" class="regular-text" placeholder="">
                        <p class="description" id="tagline-description">web user login</p>
                    </td>
                </tr> 
                
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="woocommerce_store_address">Password<span class="woocommerce-help-tip"></span></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="text" value="<?= Msb_livraison_shipping_calculator::$password ?>" name="password" id="password" class="regular-text" placeholder="">
                        <p class="description" id="tagline-description">web user password</p>
                    </td>
                </tr> 
                
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="woocommerce_store_address">Client<span class="woocommerce-help-tip"></span></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="text" value="<?= Msb_livraison_shipping_calculator::$client ?>" name="client" id="client" class="regular-text" placeholder="">
                        <p class="description" id="tagline-description">Dispatch customer code, alloewed customer code are returned by authentication, you can get customer list with a dedicated method</p>
                    </td>
                </tr> 

            </tbody>
        </table>


        <p class="submit">
            <button name="btn-msb_livraison-submit" class="button-primary" type="submit" value="Save changes">Save changes</button> 
            <input type="hidden" name="msb-calculator-setting" id="msb-calculator-setting">
        </p>
        
    </form>

</div>