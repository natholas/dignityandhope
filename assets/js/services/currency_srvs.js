dah.service("Currency", function($http, Storage) {

    this.data = {
        "currencies": {},
        "currentCurrency": {}
    }
	var $this = this;
    var data = this.data;

    this.get_currency_data = function () {
        var old_currency_data = Storage.load("currencies");
        if (old_currency_data) {
            data.currencies = old_currency_data;
            load_currency_prefs();
        } else {
            $http.post("/api/info/get_currency_info.php").then(function(response) {
                data.currencies = {};
                for (var i = 0; i < response.data.currencies.length; i++) {
                    data.currencies[response.data.currencies[i].currency_code] = response.data.currencies[i];
                }
                load_currency_prefs();
            });
        }
    }

    this.changeCurrency = function (newCurrency) {
        if (data.currencies[newCurrency]) {
            Storage.save("currency", newCurrency);
            data.currentCurrency.currency_code = data.currencies[newCurrency].currency_code;
            data.currentCurrency.sign = data.currencies[newCurrency].sign;
            data.currentCurrency.value = data.currencies[newCurrency].value;
        }
    }

    var load_currency_prefs = function () {
        var old_currency = Storage.load("currency");
        if (old_currency) changeCurrency(old_currency);
        else changeCurrency("CHF");
    }

    var changeCurrency = this.changeCurrency;
    this.get_currency_data();


})
