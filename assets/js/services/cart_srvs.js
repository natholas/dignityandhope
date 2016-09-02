dah.service("Cart", function(Currency, Storage, Investments) {

    this.data = {
        "items": [],
        "show": false
    }

    data = this.data;

    // This function loads the cart from localstorage
    this.loadCart = function () {
        var old_cart = Storage.load("cart");
        if (old_cart) {
            this.data.items = old_cart.items;
        }
    }

    this.loadCart();

    // This function adds something to the cart.
    // Based on the type we can determin what.
    this.add = function (type, id, amount) {

        if (type == "investment") {

            var inv = false;
            Investments.get_one(id).then(function(response) {
                inv = response;

                // If the type is an investment we need to check if there is already an investment to this investment
                var pos = search("investment", "investment_id", id);
                if (pos === false) {

                    // If this investment is not yet in the cart then we add it
                    data.items.push({
                        "type": "investment",
                        "investment_id": id,
                        "amount": fix_amount(amount),
                        "data": inv
                    });

                } else {

                    // If this investment is already in the cart we add the value to the existing one
                    // However we need to check if we are not adding more to the cart then is needed for this investment
                    if (inv.amount_needed - inv.amount_invested > data.items[pos].amount + fix_amount(amount)) {

                        // The amount that we need to add is less than the investment needs so we just add the amount
                        data.items[pos].amount += fix_amount(amount);

                    } else {

                        // The amount is too much. Lets just add what is needed and then let the client know with a warning message

                        data.items[pos].amount = inv.amount_needed - inv.amount_invested;

                        // EaM.add("error"); // i know..

                    }
                }
                data.show = true;
            });
        }
    }

    this.cart_item_info = function (type, id) {

        return data.items[search(type, type + "_id", id)];

    }

    // This function searches through the cart and returns if and where the items being searched for is.
    search = function (type, key, term) {

        for (var i = 0; i < this.data.items.length; i++) {
            if (this.data.items[i].type == type && this.data.items[i][key] == term) return i;
        }
        return false;
    }

    function fix_amount(input) {
        return parseFloat((input * Currency.data.currentCurrency.value).toFixed(2));
    }

    this.search = search;

});
