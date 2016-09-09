dah.service("Checkout", function($http, AccountData, Storage, Cart, Orders) {


    this.data = {
        "order": {}
    };

    var data = this.data;


    this.checkout = function () {

        // Preparing the checkout parameters
        var params = JSON.parse(JSON.stringify(AccountData.personal_info));
        params.cart = Cart.data.items;
        params.dob = dobToTimestamp(params.dob);

        // Doing the call to complete the order
        $http.post("/api/checkout/checkout.php", params).then(function(response) {
            if (response.data.status == "success") {

                Cart.empty();
                data.order = JSON.parse(JSON.stringify(params));
                data.order_id = response.data.order_id;
                Storage.remove("order_history");
                Orders.data.orders = [];
                window.location.href = "#/confirmation";

            } else {
                // Error notice
            }
        });

    }

})
