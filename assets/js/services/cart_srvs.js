dah.service("Cart", function(Currency, Storage, Investments, $q) {

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

                    // The investment is already in the cart so we just replace the amount
                    data.items[pos].amount = fix_amount(amount);

                }
                data.show = true;
            });
        }
    }

    this.remove = function (index) {
        if (data.items[index].count) {
            data.items[index].count -= 1
            if (data.items[index].count <= 0) {
                data.items.splice(index,1);
            }
        } else {
            data.items.splice(index,1);
        }
    }

    this.cart_item_info = function (type, id) {

        var deferred = $q.defer();

        var found = false;

        if (data.items.length) {
            for (var i = 0; i < this.data.items.length; i++) {
                if (this.data.items[i].type == type && this.data.items[i][type + "_id"] == id) {
                    var found = true;
                    deferred.resolve(data.items[i]);
                }
            }
        }

        if (!found) {
            deferred.resolve({"amount": 0});
        }

        return deferred.promise;

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

    this.total = function () {
        var total = 0;
        for (var i = 0; i < data.items.length; i++) {
            if (data.items[i].count) total += data.items[i].amount * ata.items[i].count;
            else total += data.items[i].amount;
        }
        return total;
    }

    this.empty = function () {
        data.items = [];
    }

    this.search = search;

});
