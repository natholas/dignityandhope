dah.service('Users', function($http, Storage, $q) {

    this.data = {};
    var data = this.data;


    data.settings = {
        limit: "10",
        offset: 0,
        order_by: "user_id DESC",
        pages_loaded: 0,
        filter: {
            search: ""
        }
    }

    this.clearFilter = function() {
        data.settings.filter = {
            search: ""
        }
    }


    this.load_users = function(ignore_cache, autoLoad) {

        // Checking to see if the investments are already saved in the localstorage
        var new_data = Storage.load("users");
        if (new_data && !ignore_cache) {

            // The investments were saved in localstorage
            data.users = new_data.users;
            data.settings = new_data.settings;
            data.count = new_data.count;

        } else {

            // The investments are not in localstorage or not valid anymore
            // Getting the investments from API
            var settings = {
                "limit": data.settings.limit,
                "offset": data.settings.offset,
                "order_by": data.settings.order_by,
                "filter": JSON.stringify(data.settings.filter)
            }

            if (!autoLoad) {
                settings.getcount = 1;
            }

            $http.post("/admin/api/users/get_users.php", settings).success(function(response, status) {

                if (response && response.status == "success") {

                    if (autoLoad) {
                        data.users = data.users.concat(response.users);
                        data.autoLoading = false;
                    } else {
                        data.users = response.users;
                        data.count = response.count;
                    }

                    // Saving investments for the next 2 hours
                    Storage.save("users", data, 2);
                }
            });
        }
    }

    this.load_users();



    this.updateUser = function(new_data) {

        // Looping through the list of all the current employees
        for (var i = 0; i < data.users.length; i ++) {

            if (new_data.user_id == data.users[i].user_id) {
                // Updating user
                console.log(1);
                data.users[i] = new_data;
                Storage.update("users", data);
                break;
            }
        }
    }

    this.remove = function(id) {
        for (var i = 0; i < data.users.length; i ++) {
            if (id == data.users[i].user_id) {
                if (AccountData.permissions.remove_user) {
                    data.users.splice(i,1);
                }
                Storage.update("users", data);
                break;
            }
        }
    }



    this.findUserFromUserId = function(user_id) {
        var deferred = $q.defer();

        // Finding a front end user from their user_id
        var settings = {
            "user_id": user_id
        }

        $http.post("/api/users/get_user.php", settings).success(function(response, status) {
            if (response.status == "success") {
                console.log();
                deferred.resolve(response.user);
            } else {
                deferred.resolve({});
            }
        });
        return deferred.promise;
    }

    this.findUsersFromString = function(string) {
        var deferred = $q.defer();

        // Finding front end users from a string
        var settings = {
            "string": string
        }

        $http.post("/api/users/find_users.php", settings).success(function(response, status) {
            if (response.status == "success") {
                deferred.resolve(response.users);
            } else {
                return [];
            }
        });

        return deferred.promise;

    }

    this.findUsersFromInvestment = function(investment_id) {

        // Finding a front end users that are associated with an investment
        var settings = {
            "investment_id": investment_id
        }

        $http.post("/api/users/get_users.php", settings).success(function(response, status) {
            if (response.status == "success") {
                return response.users;
            } else {
                return false;
            }
        });
    }

});
