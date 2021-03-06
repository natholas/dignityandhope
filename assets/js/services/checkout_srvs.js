dah.service("Checkout", function($http, AccountData, Storage, Cart, Orders, Investments, Currency, Notifications) {


    this.data = {
        "order": {}
    };

    var data = this.data;

    this.checkout = function () {

        // Preparing the checkout parameters
        var params = JSON.parse(JSON.stringify(AccountData.personal_info));
        params.cart = Cart.data.items;
        params.dob = dobToTimestamp(params.dob.string);
        params.currency = Currency.data.currentCurrency.currency_code;

        // Doing the call to complete the order
        $http.post("/api/checkout/checkout_init.php", params).then(function(response) {
			console.log(response);
            if (response.data.status == "success") {
                window.location.href = response.data.RedirectUrl;
            } else {
                Notifications.add("Technical error. Please try again later", "bad");
            }
        });

    }

})
