
<?php 
    global $post;
    $post_id = $post->ID;
    $product = wc_get_product( $post_id );
    $title = get_the_title($post_id);
    $price = $product->get_price_html();
?>

            <div class="zipCodeSelector__wrapper js-zip-code-selector zipCodeSelector__wrapper--frontShow" data-action="base-autocomplete:selected->base-zip-code-selector#newSelection" data-controller="base-zip-code-selector">
                
                <div class="form__field--">
                    <div class="form__field__input">
                        <div class="autocompleteField__wrapper js-autocompleteField__wrapper autocompleteField__wrapper--frontShow" data-controller="base-autocomplete" data-next-url="/livraison-fleurs-et-plantes/set_params" data-search-url="/zip-code-search">
                            <div class="js-input-wrapper">
                                <div class="form__field__inputIcon">
                                    <div class="svgIcon__wrapper svgIcon__wrapper__lPin__wrapper svgIcon__wrapper--inline">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 43.1c-93.7 0-169.9 75.2-169.9 168.9 0 46.9 19.5 90.8 53.7 123L256 451.3l116.2-116.2c34.2-32.2 53.7-76.2 53.7-123 0-93.8-76.2-169-169.9-169zm0 461.9l-2-2-141.5-141.6C70.5 322.4 48 269.6 48 212 48 97.8 140.8 5 256 5s208 92.8 208 207c0 57.6-22.5 110.3-64.5 150.4L256 505zm0-336.9c-24.4 0-44.9 20.5-44.9 44.9 0 25.4 20.5 45.9 44.9 45.9s44.9-20.5 44.9-45.9c0-24.4-20.5-44.9-44.9-44.9m0 128.9c-45.9 0-83-38.1-83-84s37.1-83 83-83 83 37.1 83 83-37.1 84-83 84"></path></svg>
                                    </div>
                                </div>
                                <input type="hidden" name="postal_code" id="postal_code" v-bind:value="location_infos.PostalCode">
                                <input type="hidden" name="city_name" id="city_name" v-bind:value="location_infos.CityName">
                                <input v-model="location_label" placeholder="Code postal / Ville" autocomplete="off" class="frontShow" type="text" id="sub_order_zip_code_id">
                                
                            </div>
                            <div v-if="openCodeAutocomplete" class="js-results">
                                <div class="js-results-container autocompleteField__wrapper__results autocompleteField__wrapper__results--frontShow">
                                    <div v-for="{label, infos} in searchResults" :key="i" v-on:click="setLocation(label, infos)" class="autocompleteField__result js-result">
                                        <div class="code">{{ infos.PostalCode }}</div>
                                        <div class="text">{{ infos.CityName }}</div>
                                        <div class="sep"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="shipping_avalablity_message" :value="shipping_avalablity_message">
            <input type="hidden" name="shipping_cost" :value="shipping_cost">

            <div class="zipCodeSelection__ctaContainer">
                <div class="content-separator"></div> 
                <div class="we_deliver">
                    <?= __('Besoin d’une livraison ailleurs ? Appelez-nous au 01 41 05 99 00.','msb_livraison'); ?>
                </div>

                <a class="oe_custom_button" v-on:click="isHiddenPopup = false; return false;" :class="{hidden: !isDeliverySlots}" :disabled="isShowPopupDisabled">
                    <?= __('Choisir une date de livraison', 'msb_livraison'); ?>
                </a>

                <button type="submit" name="add-to-cart" value="<?= $post_id; ?>" class="oe_custom_button" :class="{hidden: isDeliverySlots}"><?= __('Ajouter au panier', 'msb_livraison'); ?></button>

                <!-- <br> -->
                <!-- <a class="oe_custom_button s" v-on:click="createShipment"><?= __('Create Shipment', 'msb_livraison'); ?></a>  -->
                <!-- <br> -->
                <!-- <a class="oe_custom_button s" v-on:click="shipmentSearch"><?= __('Search Shipment', 'msb_livraison'); ?></a>  -->

                <div class="shipping_avalablity_message" v-html="shipping_avalablity_message"></div> 
            </div>

        </div>

        <!-- v-if="!isHiddenPopup" -->
        <div  class="form__field--container" :class="{ showed: !isHiddenPopup}">
            <div class="form__field--popup">
                <div class="close" v-on:click="isHiddenPopup = true">
                    <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB2aWV3Qm94PSIwIDAgNTEyLjAwMSA1MTIuMDAxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMDAxIDUxMi4wMDE7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxnPg0KCQk8cGF0aCBkPSJNMjg0LjI4NiwyNTYuMDAyTDUwNi4xNDMsMzQuMTQ0YzcuODExLTcuODExLDcuODExLTIwLjQ3NSwwLTI4LjI4NWMtNy44MTEtNy44MS0yMC40NzUtNy44MTEtMjguMjg1LDBMMjU2LDIyNy43MTcNCgkJCUwzNC4xNDMsNS44NTljLTcuODExLTcuODExLTIwLjQ3NS03LjgxMS0yOC4yODUsMGMtNy44MSw3LjgxMS03LjgxMSwyMC40NzUsMCwyOC4yODVsMjIxLjg1NywyMjEuODU3TDUuODU4LDQ3Ny44NTkNCgkJCWMtNy44MTEsNy44MTEtNy44MTEsMjAuNDc1LDAsMjguMjg1YzMuOTA1LDMuOTA1LDkuMDI0LDUuODU3LDE0LjE0Myw1Ljg1N2M1LjExOSwwLDEwLjIzNy0xLjk1MiwxNC4xNDMtNS44NTdMMjU2LDI4NC4yODcNCgkJCWwyMjEuODU3LDIyMS44NTdjMy45MDUsMy45MDUsOS4wMjQsNS44NTcsMTQuMTQzLDUuODU3czEwLjIzNy0xLjk1MiwxNC4xNDMtNS44NTdjNy44MTEtNy44MTEsNy44MTEtMjAuNDc1LDAtMjguMjg1DQoJCQlMMjg0LjI4NiwyNTYuMDAyeiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K" />
                </div>
                <div class="form__field--popup-container">
                    <div class="form__field js-calendar" :class="{ mobile_hidden: displayCreneau}">
                        <div class="title">Je choisis la date de livraison de mon bouquet</div> 
                        <div class="input-group msb-calendar">
                            <input type="hidden" name="shipping_dates" id="shipping_dates" :value="shipping_date | stringDate">
                            <!-- <input type="hidden" name="shipping_dates" id="shipping_dates" :value="formated_dates"> -->
                            <!-- <input type="hidden" name="shipping_dates_ids" id="shipping_dates_ids" :value="dates_ids"> -->
                            <v-calendar :attributes="attributes" @dayclick="onDayClick" title-position="left" is-expanded is-range :locale="locale" :disabled-dates="disabled_dates"></v-calendar>
                        </div>

                        <div class="form__field--step">
                            <button type="button" class="oe_custom_button" v-on:click="displayCreneau = true" id="goto-creneau"><?= __('Choisir un créneau horaire', 'msb_livraison'); ?></button>
                        </div> 
                    </div> 

                    <div class="form__field js-radio-group" :class="{ mobile_hidden: !displayCreneau}">
                        <div class="title">Je choisis mon créneau de livraison</div> 
                        <div class="js-radio-options">
                            <div class="contentGroup__wrapper contentGroup__wrapper--column ontentGroup__wrapper--column--center">
                                
                                <label class="form__field__input" v-for="{id, slot_name, selected} in delivery_slots" v-bind:key="id">
                                    <input class="js-radio-value-hide" v-on:click="handleDeliverySlot" type="radio" :value="slot_name" :checked="delivery_slot_value==slot_name" name="delivery_slot" :id="id" v-model="delivery_slot_value">
                                    <div class="radioItem__wrapper js-radio-item" :class="{selected: delivery_slot_id == id}">
                                        <div class="infoCard__wrapper ">
                                            <div class="infoCard__line">
                                                {{ slot_name }}
                                            </div>
                                        </div>
                                    </div>
                                </label>

                            </div>
                        </div>
                        
                        <div class="title">Mon message personnel <small>(facultatif)</small></div>
                        
                        <div class="js-textarea">
                            <textarea name="product_note"> </textarea> 
                        </div>

                        <div class="form__field--step">
                            <button type="submit" name="add-to-cart" value="<?= $post_id; ?>" class="oe_custom_button ajouter-au-panier"><?= __('Ajouter au panier', 'msb_livraison'); ?></button>
                        
                            <span class="return_to_shipping" v-on:click="displayCreneau = false"> <i class="fa fa-chevron-left"></i> &nbsp;<span><?= __('Changer ma date de livraison', 'msb_livraison'); ?></span> </span>
                        </div> 
                    </div>
                </div>
                <div class="bande-grise">
                    <div class="amoureuse---double">
                        <?= $title; ?>
                    </div>	
                    <div class="js-price">
                        <?= $price; ?>
                    </div> 
                    <button type="submit" name="add-to-cart" value="<?= $post_id; ?>" class="oe_custom_button ajouter-au-panier"><?= __('Ajouter au panier', 'msb_livraison'); ?></button>
                </div>
            </div>
        </div>

    </div>
    <!-- <div>
        <h3>Requête API</h3>
        <pre>{{request | json}}</pre>
        <h3>Réponse API</h3>
        <pre>{{response | json}}</pre>
    </div> -->
</div>
