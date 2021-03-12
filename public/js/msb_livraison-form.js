var j = jQuery.noConflict();
moment().locale('de');

var app = new Vue({
    el: "#msb-geocoding",
    components: {
        // Calendar, 
    },
    data() {
        return {
            isShowPopupDisabled: true,
            isDeliverySlots: true,
            isHiddenPopup: true,
            openCodeAutocomplete: true,
            calc_shipping: null,
            shipping_avalablity_message: "",
            shipping_cost: "",
            shipping_time: 2,
            calc_shipping_message: "",
            shipping_methods: [],

            location_label: '',
            location_infos: {
                PostalCode: null,
                CityName: null,
            },
            searchResults: [],
            disabled_dates: { weekdays: [1, 7] },
            selected: null,

            search_val: '',

            delivery_slot_value: "",
            delivery_slot_id: "",

            credential: {
                License: msb_livraison_object.credential.license,
                Login: msb_livraison_object.credential.login,
                Password: msb_livraison_object.credential.password
            },
            clientCode: msb_livraison_object.clientCode,

            searchParam: {
                clientCode: msb_livraison_object.clientCode,
                shipmentId: '',
                serviceCode: ''
            },

            countryCode: 'FR',
            city: {
                name: '',
                cityID: 0,
                postalCode: ''
            },
            street: '',
            number: 0,
            coordinates: {
                Latitude: '',
                Longitude: '',
            },
            request: '',
            response: '',
            selectedDate: null,
            days: [],
            locale: { id: 'fr', firstDayOfWeek: 3, masks: { weekdays: 'WWW' } }
        }
    },
    created() {
        //setInterval(this.setDate(4), 1000);

    },

    computed: {

        dates() {
            return this.days.map(day => day.date);
        },

        dates_ids() {
            return this.days.map(day => day.id);
        },

        formated_dates() {
            return this.days.map(function(day) {
                return moment(String(day.date)).format('dddd D MMMM');
            });
        },

        attributes() {
            return this.dates.map(date => ({
                highlight: {
                    color: 'orange',
                    fontWeight: 900,
                    fontSize: '14px',
                    fillMode: 'light',
                },
                dates: date,
            }));
        },

        delivery_slots() {
            return [{
                    "id": 1,
                    "slot_name": "10-13H",
                    "selected": true
                },
                {
                    "id": 2,
                    "slot_name": "14-17H",
                    "selected": false
                },
                {
                    "id": 3,
                    "slot_name": "18-21H",
                    "selected": false
                },
            ]
        },

        min_date() {

            var date = new Date();
            var shipping_time = 2;
            var decal_shipping_time = 0;

            date.setDate(date.getDate() + shipping_time);
            if (date.getDay() == 6 || date.getDay() == 0) {
                decal_shipping_time = 2;
            }
            date.setDate(date.getDate() + decal_shipping_time);
            return date;
        },


        shipping_date() {

            if (this.days[0]) {

                let _days = _.cloneDeep(this.days);
                let _date = _days[0].date;

                var _decal_shipping_time = 0;
                _date.setDate(_date.getDate() + this.shipping_time);

                if (this.shipping_time == 2) {
                    if (_date.getDay() == 6 || _date.getDay() == 0) {
                        _decal_shipping_time = 2;
                    }
                } else if (this.shipping_time == 3) {
                    if (_date.getDay() == 1) {
                        _decal_shipping_time = 1;
                    } else if (_date.getDay() == 6) {
                        _decal_shipping_time = 3;
                    } else if (_date.getDay() == 0) {
                        _decal_shipping_time = 2;
                    }
                }

                _date.setDate(_date.getDate() + _decal_shipping_time);

                return _date;
            }
        },

    },

    watch: {
        location_label(val) {
            this.searchCity(val);
        }
    },

    filters: {

        formatDate(value) {
            if (value) {
                return moment(String(value)).format('dddd D MMMM');
            }
        },
        stringDate(value) {
            if (value) {
                return moment(String(value)).format('D/MM/yyyy');
            }
        },

    },

    methods: {

        calcShipping() {
            // And here is our jQuery ajax call
            j.ajax({
                type: "POST",
                url: msb_livraison_object.ajax_url + "?action=check_zip_code_availability",
                data: {
                    'product_id': 41,
                    'calc_shipping_country': app.countryCode,
                    'calc_shipping_postcode': app.location_infos.PostalCode,
                    'calc_shipping_method': "flat_rate:1",
                },
                success: function(response) {

                    var { code, message, cost, id } = JSON.parse(response);
                    if (code == 'success') {
                        app.isShowPopupDisabled = false;
                        if (id == 4) {
                            app.disabled_dates = { weekdays: [1, 2, 7] };
                            app.shipping_time = 3;
                            app.isShowPopupDisabled = false;
                            app.isDeliverySlots = true;
                        } else if (id == 5) {
                            app.disabled_dates = { weekdays: [1, 7] };
                            app.shipping_time = 2;
                            app.isShowPopupDisabled = false;
                            app.isDeliverySlots = true;
                        } else {
                            app.disabled_dates = { weekdays: [1, 7] };
                            app.shipping_time = 2;
                            app.isShowPopupDisabled = false;
                            app.isDeliverySlots = true;
                        }
                    } else {
                        app.isShowPopupDisabled = true;
                        app.isDeliverySlots = false;
                    }
                    app.shipping_avalablity_message = message;
                    app.shipping_cost = cost;
                    console.log(id);
                },
                error: function(error) {
                    app.shipping_avalablity_message = 'There seems to be an error with this search.';
                }
            });

        },

        handleDeliverySlot(e) {
            this.delivery_slot_value = e.target.value;
            this.delivery_slot_id = e.target.id;
        },

        setDate(days) {
            var date = new Date();
            date.setDate(date.getDate() + days);
            this.min_date = date;
        },

        searchCity(val) {

            this.openCodeAutocomplete = true;

            var request = {
                Credential: this.credential,
                Country: this.countryCode,
                Prefix: val
            };

            var _this = this;
            this.request = request;

            var apiurl = msb_livraison_object.base_api_url + '/Cities';

            axios.post(apiurl, request).then(response => {
                _this.response = response;
                response = response.data;
                var results = [];
                if (response.Status === 200) {
                    results = response.CityList.map(function(city) {
                        return {
                            label: city.PostalCode + ' - ' + city.CityName,
                            infos: city
                        };
                    });
                    app.searchResults = results;
                }
            }).catch(error => console.log(error));
        },

        citySelected(item) {
            this.city.name = item.infos.CityName;
            this.city.postalCode = item.infos.PostalCode;
            this.city.cityID = item.infos.CityId;
            this.countryCode = item.infos.Country;

            this.$refs.autocompletecp.forceQuery(this.city.postalCode);
            this.$refs.autocompletecity.forceQuery(this.city.name);
        },

        geocodeAddress() {
            var request = {
                Credential: this.credential,
                Address: {
                    Country: this.countryCode,
                    PostalCode: this.city.postalCode,
                    City: this.city.name,
                    CityID: this.city.cityID,
                    Street: this.street,
                    No: this.number
                }
            };

            this.request = request;
            api_url = msb_livraison_object.base_api_url + '/GeocodeAddress';
            var _this = this;
            axios.post(api_url, request)
                .then(response => {
                    _this.response = response;
                    if (response.Status === 200) {
                        _this.coordinates = response.Coordinates;
                    }
                })
                .catch(error => console.log(error));

        },

        onDayClick(day) {

            var ele = day.el;
            if (ele.ariaDisabled == 'true') {
                return;
            }

            const idx = this.days.findIndex(d => d.id === day.id);

            if (idx >= 0) {

                this.days.splice(idx, 1);

            } else {

                this.days.splice(day.id, 1);
                // var new_shipping_date = this.shipping_dates(day.date);

                this.days.push({
                    id: day.id,
                    date: day.date,
                });

            }

        },

        setLocation(label, infos) {

            this.openCodeAutocomplete = false;
            this.location_label = label;
            this.location_infos = infos;
            this.calcShipping();
        },

        shipping_dates(date) {

            var decal_shipping_time = 0;
            date.setDate(date.getDate() + this.shipping_time);

            if (this.shipping_time == 2)
                if (date.getDay() == 6 || date.getDay() == 0)
                    decal_shipping_time = 2;

                else if (this.shipping_time == 3)
                if (date.getDay() == 4)
                    decal_shipping_time = 2;
                else if (date.getDay() == 5)
                decal_shipping_time = 1;



            date.setDate(date.getDate() + decal_shipping_time);
            return date;
        },


    },


});