var j = jQuery.noConflict();
// the global _ will now be underscore
var lodash = _.noConflict();
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
            displayCreneauMobile: false,
            openCodeAutocomplete: true,
            calc_shipping: null,
            shipping_avalablity_message: "",
            shipping_cost: "",
            shipping_time: 2,
            calc_shipping_message: "",
            shipping_methods: [],
            min_date: new Date(),
            searchResults: [],
            location_label: '',
            location_infos: {
                PostalCode: null,
                CityName: null,
            },

            response: '',
            apiRequest: '',
            apiResponse: '',
            connection_token: '',

            disabled_dates: [
                { weekdays: [1, 7] },
            ],
            holidays_dates: [],
            holidays_days: [
                new Date(2021, 01 - 1, 01),
                new Date(2021, 03 - 1, 20),
                new Date(2021, 03 - 1, 28),
                new Date(2021, 04 - 1, 02),
                new Date(2021, 04 - 1, 04),
                new Date(2021, 04 - 1, 05),
                new Date(2021, 05 - 1, 01),
                new Date(2021, 05 - 1, 08),
                new Date(2021, 05 - 1, 13),
                new Date(2021, 05 - 1, 23),
                new Date(2021, 05 - 1, 24),
                new Date(2021, 05 - 1, 30),
                new Date(2021, 06 - 1, 20),
                new Date(2021, 06 - 1, 21),
                new Date(2021, 07 - 1, 14),
                new Date(2021, 08 - 1, 15),
                new Date(2021, 09 - 1, 22),
                new Date(2021, 10 - 1, 31),
                new Date(2021, 11 - 1, 01),
                new Date(2021, 11 - 1, 11),
                new Date(2021, 12 - 1, 21),
                new Date(2021, 12 - 1, 24),
                new Date(2021, 12 - 1, 25),
                new Date(2021, 12 - 1, 26),
                new Date(2021, 12 - 1, 31)
            ],
            selected: null,

            search_val: '',

            delivery_slot_value: "",
            delivery_slot_id: "",

            credential: {
                License: msb_livraison_object.credential.license,
                Login: msb_livraison_object.credential.login,
                Password: msb_livraison_object.credential.password,
                ConnectionToken: ''
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
        // setInterval(this.setDate(4), 1000);

        this.holidays_days.forEach(holiday => {
            this.holidays_dates.push({
                start: holiday,
                end: holiday
            })
        });

        this.disabled_dates.push(...this.holidays_dates);
        this.authentication();
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
                    "slot_display_name": "Entre 10 et 13H",
                    "selected": true
                },
                {
                    "id": 2,
                    "slot_name": "14-17H",
                    "slot_display_name": "Entre 14 et 17H",
                    "selected": false
                },
                {
                    "id": 3,
                    "slot_name": "18-21H",
                    "slot_display_name": "Entre 18 et 21H",
                    "selected": false
                },
            ]
        },

        // min_date() {

        //     var date = new Date();
        //     var shipping_time = 2;
        //     var decal_shipping_time = 0;

        //     date.setDate(date.getDate() + shipping_time);
        //     if (date.getDay() == 6 || date.getDay() == 0) {
        //         decal_shipping_time = 2;
        //     }
        //     date.setDate(date.getDate() + decal_shipping_time);
        //     return date;

        // },

        shipping_date() {

            if (this.days[0]) {
                let _days = lodash.cloneDeep(this.days);
                return this.get_shipping_dates(_days[0].date);
            }
        },

    },

    methods: {

        authentication() {
            console.log('authentication');
            //Creation d'un objet de type requestType : 
            var authentificationRequest = {};
            authentificationRequest.Credential = this.credential;

            var _this = this;
            var apiurl = msb_livraison_object.base_api_url + '/Authentify';

            _this.apiRequest = authentificationRequest;
            axios.post(apiurl, authentificationRequest).then(response => {
                _this.response = response;
                _this.apiResponse = response;
                response = response.data;

                if (response.Status === 200 && response.Authentified === true) {
                    console.log('Authentification success Token is ' + response.ConnectionToken);
                    _this.connection_token = response.ConnectionToken;
                    _this.credential.ConnectionToken = response.ConnectionToken;
                }

            }).catch(error => console.log(error));


        },

        get_shipping_dates(_date) {

            console.log('Initial date');
            console.log(_date);

            _date.setDate(_date.getDate() + this.shipping_time);
            _shipping_date = this.calc_shipping_dates(_date);

            this.holidays_days.forEach(holiday => {

                if (holiday.getDate() === _shipping_date.getDate() && holiday.getMonth() === _shipping_date.getMonth()) {

                    console.log('Holidays_days');
                    console.log(_shipping_date);

                    _shipping_date.setDate(_shipping_date.getDate() + 1);
                    _shipping_date = this.calc_shipping_dates(_shipping_date);

                }
            });

            console.log('Final date');
            console.log(_shipping_date);
            console.log('          -----------------------------           ');
            return _shipping_date;
        },

        calc_shipping_dates(_date) {

            var _decal_shipping_time = 0;
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

            if (_decal_shipping_time != 0) {
                console.log('Weekend');
                console.log(_date);
                _date.setDate(_date.getDate() + _decal_shipping_time)
            }

            return _date;
        },

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
                            app.disabled_dates = [{ weekdays: [1, 2, 7] }, ...app.holidays_dates];
                            app.shipping_time = 3;
                            app.isShowPopupDisabled = false;
                            app.isDeliverySlots = true;
                        } else if (id == 5) {
                            app.disabled_dates = [{ weekdays: [1, 7] }, ...app.holidays_dates];
                            app.shipping_time = 2;
                            app.isShowPopupDisabled = false;
                            app.isDeliverySlots = true;
                        } else {
                            app.disabled_dates = [{ weekdays: [1, 7] }, ...app.holidays_dates];
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

        // setDate(days) {
        //     var date = new Date();
        //     date.setDate(date.getDate() + days);
        //     this.min_date = date;
        // },

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
                console.log(_this.response);
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
                return moment(String(value)).format('DD/MM/yyyy');
            }
        },

    },
});