var j = jQuery.noConflict();

var app_shipping = new Vue({
    el: '#msb-create-shipping',
    data: {
        credential: {
            License: msb_livraison_object.credential.license,
            Login: msb_livraison_object.credential.login,
            Password: msb_livraison_object.credential.password
        },
        clientCode: msb_livraison_object.clientCode,
        saving: false,
        shipmentResult: {},
        items: {},
        apiRequest: '',
        apiResponse: '',

        searchParam: {
            clientCode: msb_livraison_object.clientCode,
            shipmentId: '',
            serviceCode: '',
            TrackId: 'ZW3YqIQ4pehcodb7482'
        },

    },
    methods: {
        createShipment() {
            this.saving = true;
            var createShipmentRequest = {};

            //Identification
            createShipmentRequest.Credential = this.credential;

            //Indique que l'on va saisir une mission et non un devis
            createShipmentRequest.Quote = false;
            //Indique la mission va être enregistré dans Dispatch
            createShipmentRequest.Save = true;

            var shipment = {};

            /*
             * à partir de la version 2.4.4 de dispatch et de la version 46 de l'API
             * Ce mode permet d'utiliser la class ShipmentSchedule de l'objet shipment,
             * Cette classe permet de manipuler plus facilement les dates de la mission et de définir des créneaux d'enlèvement livraison
             * Ce mode doit être utilisé pour tout nouveau développement 
             */
            shipment.AdvancedDateMode = true;
            //Code Client Dispatch
            shipment.ClientCode = this.clientCode;

            //Adresse d'enlèvement
            var pickupAddress = {};

            pickupAddress.Name = 'Enlèvement API Test';
            pickupAddress.PostalCode = '34000';
            pickupAddress.City = 'MONTPELLIER';
            //Obligation, si le paramètre est passé à null alors Dispatch tente de faire une correspondance de ville, si il échoue alors il peut y avoir des problèmes de tarification
            pickupAddress.CityID = null;
            pickupAddress.Sector = '';
            pickupAddress.Country = 'FR';

            shipment.FromAddress = pickupAddress;

            //Date d'enlèvement
            shipment.PickupSchedules = {};
            //Date livraison
            shipment.DeliverySchedules = {};

            //Adresse de livraison
            var deliveryAddress = {};
            deliveryAddress.Name = 'Livraison API Test';
            deliveryAddress.PostalCode = '30000';
            deliveryAddress.City = 'NIMES';
            deliveryAddress.Sector = '';
            //CityID dans le cas où l'on utilise pas une ville référensée dans Dispatch
            deliveryAddress.CityID = null;
            deliveryAddress.Country = 'FR';

            shipment.ToAddress = deliveryAddress;

            //Code Prestation Dispatch
            shipment.ServiceCode = 'T1';

            createShipmentRequest.Shipment = shipment;

            //La sauvegarde de mission renverra le prix ttc dans l'objet shipment
            createShipmentRequest.ComputePriceWithTaxes = true;

            var _this = this;
            var apiurl = msb_livraison_object.base_api_url + '/CreateShipment';
            axios.post(apiurl, createShipmentRequest).then(response => {
                _this.response = response;
                response = response.data;
                console.log(response)
            }).catch(error => console.log(error));


        },

        shipmentSearch() {

            this.searching = true;
            var searchRequest = {};
            searchRequest.Credential = this.credential;

            searchRequest.LoadShipmentHistory = true;
            //Critères de recherche
            searchRequest.SearchParams = {};

            if (this.searchParam.clientCode) {
                //Recherche par code client
                searchRequest.SearchParams.ClientList = [this.searchParam.clientCode];
            }
            if (this.searchParam.shipmentId) {
                //Recherche par numéro de mission
                searchRequest.SearchParams.IdList = [this.searchParam.shipmentId];
            }
            if (this.searchParam.serviceCode) {
                //recherche par code de prestation
                searchRequest.SearchParams.ServiceCodeList = [this.searchParam.serviceCode];
            }
            if (this.searchParam.TrackId) {
                //recherche par Track ID
                searchRequest.SearchParams.TrackIdList = [this.searchParam.TrackId];
            }
            var _this = this;
            _this.apiRequest = searchRequest;
            var apiurl = msb_livraison_object.base_api_url + '/Shipments';
            axios.post(apiurl, searchRequest).then(response => {
                _this.response = response;
                response = response.data;

                if (response.Status === 200) {
                    _this.apiResponse = response;
                    _this.shipmentResult = response.ShipmentList[0];
                    _this.items = response.ShipmentList[0].ShipmentEventList;

                }


            }).catch(error => console.log(error));
        },

        authentication() {
            //Creation d'un objet de type requestType : 
            var authentificationRequest = {};
            authentificationRequest.Credential = {};
            //Utilisation de la licence transporteur Dispatch
            authentificationRequest.Credential.License = this.credential.License;
            //Login et mot de passe donneur d'ordre
            authentificationRequest.Credential.Login = this.credential.Login;
            authentificationRequest.Credential.Password = this.credential.Password;
            authentificationRequest.Credential.EMail = null;
            authentificationRequest.Credential.Language = 'fr-FR';

            var _this = this;
            var apiurl = msb_livraison_object.base_api_url + '/Authentify';

            axios.post(apiurl, authentificationRequest).then(response => {
                _this.response = response;
                response = response.data;

                if (response.Status === 200 && response.Authentified === true) {
                    console.log(response);
                    alert('Authentification success Token is ' + response.ConnectionToken);
                }

            }).catch(error => console.log(error));


        }

    }

});