dah.factory('Account', function($http, Storage, AccountData, Prompts, EaM, $q) {

    var user = AccountData;

    return {
        info: function(ignore) {

            // This little function gets our login status and our permissions.
            // It's possible that the info data is already there.
            // Lets check
            var data = Storage.load("account_data");
            if (data && !ignore) {

                // The account data is already there.
                // Lets just use that instead of getting it from the server again
                user.username = data.username;
                user.organization_id = data.organization_id;
                user.permissions = data.permissions;
                user.user_id = data.user_id;

                // And we can close the login prompt incase it's open
                Prompts.close_prompt("login");

            } else {

                // Nope, well it looks like we have to get it from the API.
                $http.post("/admin/api/account/check_login.php").success(function(data, status) {
                    if (data && data.status == "success") {

                        // Look at that! The server has told us that we are logged in!
                        // We can get rid of the stupid status..
                        delete data.status;

                        // Saving our account details in localstorage for the next 15 minutes
                        Storage.save("account_data", data, 0.25);

                        // Setting the user details for all controllers
                        user.username = data.username;
                        user.organization_id = data.organization_id;
                        user.permissions = data.permissions;
                        user.user_id = data.user_id;

                        // Closing the login prompt incase its open
                        Prompts.close_prompt("login");

                    } else {

                        // Looks like we are not logged in.
                        // open up the old login prompt
                        Prompts.open_prompt("login");
                    }
                });
            }
        },

        login: function(username, password) {

            // Preparing the data to send
            var data = {
                "username": username,
                "password": password
            };

            // Asking the API very nicely to let us in
            $http.post("/admin/api/account/login.php", data).success(function(data, status) {

                if (data.status == "success") {

                    // Saving our account details in localstorage for the next two hours
                    Storage.save("account_data", data, 2);
                    // Setting the user details for all controllers
                    user.username = data.username;
                    user.organization_id = data.organization_id;
                    user.permissions = data.permissions;
                    user.user_id = data.user_id;

                    // Closing the login prompt incase its open
                    Prompts.close_prompt("login");
                    EaM.showMessage("loggedin");
                    window.location.reload();

                } else {
                    EaM.showError("wronguserorpass");
                }

            });

        },

        send_reset: function(email) {

            var deferred = $q.defer();

            // This function is quite simple.
            // Saying goodbye to the API
            var data = {
                email: email
            }
            $http.post("/admin/api/account/send_password_reset.php", data).success(function(data, status) {

                deferred.resolve(data);

            });

            return deferred.promise;

        },

        check_code: function(code, newpassword) {

            var deferred = $q.defer();

            // This function is quite simple.
            // Saying goodbye to the API
            var data = {
                code: code,
                newpassword: newpassword
            }
            $http.post("/admin/api/account/reset_password.php", data).success(function(data, status) {

                deferred.resolve(data);

            });

            return deferred.promise;

        },

        logout: function() {

            // This function is quite simple.
            // Saying goodbye to the API
            $http.post("/admin/api/account/logout.php").success(function(data, status) {

                // Removing our data
                Storage.removeall();
                delete user.username;
                delete user.permissions;

                // And open up the login prompt
                Prompts.open_prompt("login");
                window.location.reload();

            });

        }
    }
});


dah.factory('AccountData', function() {

    // In this factory we store our account data.
    // Any controller that uses this service will be returned a reference to it
    return {};

});

dah.service('Settings', function(Storage) {

    // In this factory we store our settings.
    // Any controller that uses this service will be returned a reference to it
    this.data = {
        "settings": {}
    };

    var data = this.data;

    this.get_settings = function() {
        this.data.settings = Storage.load("settings");
        if (!this.data.settings) {
            this.data.settings = {};
        }
    }
    this.get_settings();
    setInterval(function () {
        Storage.save("settings", data.settings, 24);
    }, 5000);

});
