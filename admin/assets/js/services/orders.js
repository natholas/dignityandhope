dah.service('Orders', function($http, Storage, $q, AccountData) {

    this.data = {};
    var data = this.data;
    data.autoLoading = false;

    data.settings = {
        limit: "10",
        offset: 0,
        order_by: "order_id DESC",
        pages_loaded: 0,
        filter: {
            organization_id: "any",
            canceled: false,
            failed: false,
            pending: true,
            completed: true,
            processed: false,
            search: ""
        }
    }

    this.clearFilter = function() {
        data.settings.filter = {
            organization_id: "any",
            canceled: false,
            failed: false,
            pending: true,
            completed: true,
            processed: false,
            search: ""
        }
    }

    this.findOrderFromOrderId = function(order_id) {
        var deferred = $q.defer();

        // Finding an order from the order_id
        var settings = {
            "order_id": order_id
        }

        $http.post("/admin/api/orders/get_order.php", settings).success(function(response, status) {
            if (response.status == "success") {
                deferred.resolve(response.order);
            } else {
                return {};
            }
        });
        return deferred.promise;
    }

    this.load_orders = function(ignore_cache, autoLoad) {

        // Checking to see if the orders are already saved in the localstorage
        var new_data = Storage.load("orders");
        if (new_data && !ignore_cache) {

            // The orders were saved in localstorage
            data.orders = new_data.orders;
            data.settings = new_data.settings;
            data.count = new_data.count;

        } else {

            // The orders are not in localstorage or not valid anymore
            // Getting the orders from API
            var settings = {
                "limit": data.settings.limit,
                "offset": data.settings.offset,
                "order_by": data.settings.order_by,
                "filter": JSON.stringify(data.settings.filter)
            }

            if (!autoLoad) {
                settings.getcount = 1;
            }

            $http.post("/admin/api/orders/get_orders.php", settings).success(function(response, status) {

                if (response && response.status == "success") {

                    if (autoLoad) {
                        data.orders = data.orders.concat(response.orders);
                        data.autoLoading = false;
                    } else {
                        data.orders = response.orders;
                        data.count = response.count;
                    }

                    // Saving orders for the next hour
                    //Storage.save("orders", data, 2);

                }
            });
        }
    }

    this.get_orders = function() {
        var deferred = $q.defer();
        var loop = null;

        if (data.orders) {
            deferred.resolve(data.orders);
        } else {
            loop = setInterval(function () {
                if (data.orders) {
                    clearInterval(loop);
                    loop = null;
                    deferred.resolve(data.orders);
                }
            }, 50);
        }

        return deferred.promise;
    }

    this.updateOrder = function(new_data) {

        // Looping through the list of all the current orders
        for (var i = 0; i < data.orders.length; i ++) {

            if (new_data.order_id == data.orders[i].order_id) {
                // Updating order
                data.orders[i] = new_data;
                // Updating the localstorage
                //Storage.update("orders", data);
                break;
            }
        }
    }

    this.load_orders();
});
