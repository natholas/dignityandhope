dah.service("Checkout", function($http, AccountData, Storage, Cart) {


    this.data = {
        "order": {}
    };

    var data = this.data;


    this.loadCheckoutData = function () {
        var old_checkout_data = Storage.load("checkout_data");
        if (old_checkout_data) {
            AccountData.checkout_data = old_checkout_data;
        }
    }

    this.checkout = function () {

        // Saving the checkout data to localstorage
        Storage.save("checkout_data", AccountData.checkout_data, 4);

        // Preparing the checkout parameters
        var params = JSON.parse(JSON.stringify(AccountData.checkout_data));
        params.cart = Cart.data.items;
        params.dob = dobToTimestamp(params.dob);

        // Doing the call to complete the order
        $http.post("/api/checkout/checkout.php", params).then(function(response) {
            if (response.data.status == "success") {

                Cart.empty();
                data.order = JSON.parse(JSON.stringify(params));
                data.order_id = response.data.order_id;
                window.location.href = "#/confirmation";

            } else {
                // Error notice
            }
        });

    }

    this.loadCheckoutData();

})
