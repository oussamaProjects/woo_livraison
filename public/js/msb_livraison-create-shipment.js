

var j = jQuery.noConflict();

var vueBasicShipmentEntry = new Vue({
    el: '#vueBasicShimentEntry',
    data: {
      credential: {
        License: msb_livraison_object.credential.license,
        Login: msb_livraison_object.credential.login,
        Lassword: msb_livraison_object.credential.password
      },
      clientCode: 'DOC',
      saving: false,
      shipmentResult: {},
      apiRequest: '',
      apiResponse: ''
    },
    methods: {
      /* Méthode de création de mission*/
      createShipment: function() {
        this.saving = true;
        var createShipmentRequest = {};
        //Identification
        createShipmentRequest.Credential = {
          License: this.credential.license,
          Login: this.credential.login,
          Password: this.credential.password,
          EMail: null,
          Language: 'fr-FR'
        };
  
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
   
        api_url = msb_livraison_object.base_api_url + '/CreateShipment';
        var _this = this;
        this.apiRequest = JSON.stringify(createShipmentRequest, null, '\t');
        console.log(api_url);
        console.log(createShipmentRequest); 
        console.log(this.apiRequest);
        axios.post(api_url, createShipmentRequest).then(response => {
          _this.saving = false;
          _this.apiResponse = JSON.stringify(response, null, '\t');; 
          response = response.data;
          var results = [];
          if (response.Status === 201) {
            _this.shipmentResult = response.Shipment;
          }
        }).catch(function(error) {
          _this.saving = false;
          console.log(error)
        });
 
      }
    }
  
  });