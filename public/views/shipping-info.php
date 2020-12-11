
 
    <div id="msb-geocoding"> 
        <div class="msb_form">  

        <div class="zipCodeSelection__wrapper">
            
                <div class="zipCodeSelection">
                    <div class="shipping_date_container">
                        <div class="image">
                            <img src="" alt="" srcset="">
                        </div>
                        <div class="shipping_date">
                            Commander aujourd’hui pour être livré
                            <div class="shipping_date_message" >{{ min_date | formatDate }}</div> 
                        </div> 
                    </div> 
                    
                    <div class="product_price"><?=  $price ?></div>
                    <div class="cost_deliver"><?= __('LIVRAISON OFFERTE','msb_livraison'); ?></div>
                    <div class="where_to_deliver"><?= __('Où faire livrer mon bouquet ? ','msb_livraison'); ?></div>
                    <div class="we_deliver"><?= __('Nous livrons dans Paris (75), 92, 93, 94','msb_livraison'); ?></div>   
                </div>

              