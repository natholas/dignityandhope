dah.service('Products', function($http, Storage, $q, AccountData) {

    this.data = {};
    var data = this.data;
    data.autoLoading = false;

    data.settings = {
        limit: "10",
        offset: 0,
        order_by: "product_id DESC",
        pages_loaded: 0,
        filter: {
            organization_id: "any",
            drafts: true,
            pending: true,
            live: true,
            removed: false,
            search: ""
        }
    }

    this.clearFilter = function() {
        data.settings.filter = {
            organization_id: "any",
            drafts: true,
            pending: true,
            live: true,
            removed: false,
            search: ""
        }
    }

    this.findProductsFromString = function(string) {
        var deferred = $q.defer();

        // Finding front end users from a string
        var settings = {
            "string": string
        }

        $http.post("/api/products/find_products.php", settings).success(function(response, status) {

            if (response.status == "success") {
                for (var i = 0; i < response.products.length; i++) {
                    response.products[i].image = JSON.parse(response.products[i].images)[0];
                }
                response.image =
                deferred.resolve(response.products);
            } else {
                return [];
            }
        });

        return deferred.promise;

    }

    this.findProductFromProductId = function(product_id) {
        var deferred = $q.defer();

        // Finding an product from the product_id
        var settings = {
            "product_id": product_id
        }

        $http.post("/admin/api/products/get_product.php", settings).success(function(response, status) {
            if (response.status == "success") {
                deferred.resolve(response.product);
            } else {
                return {};
            }
        });
        return deferred.promise;
    }

    this.load_products = function(ignore_cache, autoLoad) {

        // Checking to see if the products are already saved in the localstorage
        var new_data = Storage.load("products");
        if (new_data && !ignore_cache) {

            // The products were saved in localstorage
            data.products = new_data.products;
            data.settings = new_data.settings;
            data.count = new_data.count;

        } else {

            // The products are not in localstorage or not valid anymore
            // Getting the products from API
            var settings = {
                "limit": data.settings.limit,
                "offset": data.settings.offset,
                "order_by": data.settings.order_by,
                "filter": JSON.stringify(data.settings.filter)
            }

            if (!autoLoad) {
                settings.getcount = 1;
            }

            $http.post("/admin/api/products/get_products.php", settings).success(function(response, status) {

                if (response && response.status == "success") {

                    for (var i=0;i<response.products.length;i++) {
                        var dob = new Date(response.products[i].dob * 1000);
                        response.products[i].dob = {
                            "d": dob.getDate(),
                            "m": dob.getMonth() + 1,
                            "y": dob.getFullYear(),
                        }
                    }

                    if (autoLoad) {
                        data.products = data.products.concat(response.products);
                        data.autoLoading = false;
                    } else {
                        data.products = response.products;
                        data.count = response.count;
                    }

                    // Saving products for the next hour
                    //Storage.save("products", data, 2);

                }
            });
        }
    }

    this.get_products = function() {
        var deferred = $q.defer();
        var loop = null;

        if (data.products) {
            deferred.resolve(data.products);
        } else {
            loop = setInterval(function () {
                if (data.products) {
                    clearInterval(loop);
                    loop = null;
                    deferred.resolve(data.products);
                }
            }, 50);
        }

        return deferred.promise;
    }

    this.getProductIndex = function(product_id) {
        for (var i = 0; i < data.products.length; i ++) {
            if (data.products[i].product_id == product_id) {
                return i;
            }
        }
    }

    this.updateProduct = function(new_data) {

        // Looping through the list of all the current products
        for (var i = 0; i < data.products.length; i ++) {

            if (new_data.product_id == data.products[i].product_id) {
                // Updating product
                data.products[i] = new_data;
                // Updating the localstorage
                //Storage.update("products", data);
                break;
            }
        }
    }

    this.addProduct = function(new_data) {
        // Adding product
        data.products.push(new_data);
        // Updating the localstorage
        //Storage.update("products", data);
    }

    this.new_product = function() {
        var deferred = $q.defer();

        var new_product = {
            "status": "DRAFT",
            "new_images": [""],
            "images": [],
            "added_time": 0,
        }


        if (AccountData.organization_id != null) {
            new_product.organization_id = AccountData.organization_id.toString();
            deferred.resolve(new_product);
        } else {
            loop = setInterval(function () {
                if (AccountData.organization_id != null) {
                    clearInterval(loop);
                    loop = null;
                    new_product.organization_id = AccountData.organization_id.toString();
                    deferred.resolve(new_product);
                }
            }, 50);
        }
        return deferred.promise;
    }

    this.remove = function(id) {
        for (var i = 0; i < data.products.length; i ++) {
            if (id == data.products[i].product_id) {
                if (AccountData.permissions.view_removed_products) {
                    data.products[i].status = "REMOVED";
                } else {
                    data.products.splice(i,1);
                }
                //Storage.update("products", data);
            }
        }
    }

    this.load_products();
});
