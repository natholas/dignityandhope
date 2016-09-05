dah.service("Currency", function($http, Storage) {

    this.data = {
        "currencies": {
            "usd": {
                "name": "usd",
                "sign": "$",
                "value": 1
            },
            "gbp": {
                "name": "gbp",
                "sign": "£",
                "value": 1.4
            },
            "chf": {
                "name": "chf",
                "sign": "CHF ",
                "value": 1.1
            }
        },
        "currentCurrency": {
            "name": "gbp",
            "sign": "£",
            "value": 1.4
        }
    }

    this.changeCurrency = function (newCurrency) {
        Storage.save("currency", newCurrency);
        this.data.currentCurrency.name = this.data.currencies[newCurrency].name;
        this.data.currentCurrency.sign = this.data.currencies[newCurrency].sign;
        this.data.currentCurrency.value = this.data.currencies[newCurrency].value;
    }

    var old_currency = Storage.load("currency");
    if (old_currency) this.changeCurrency(old_currency);

})
