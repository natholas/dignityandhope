dah.factory('Account', function($http, Storage, AccountData, Prompts, $q) {

    var user = AccountData,
    loggingin = false,
    sendingreset = false,
    checkingcode = false;

    return {
        info: function(ignore) {

            // This little function gets our login status and our permissions.
            // It's possible that the info data is already there.
            // Lets check
            var data = Storage.load("account_data");
            if (data && !ignore) {

                // The account data is already there.
                // Lets just use that instead of getting it from the server again
                user.email = data.email;
                user.user_id = data.user_id;

                // And we can close the login prompt incase it's open
                Prompts.close_prompt("login");

            } else {

                // Nope, well it looks like we have to get it from the API.
                $http.post("/api/account/check_login.php").success(function(data, status) {

                    if (data && data.status == "success") {

                        // Look at that! The server has told us that we are logged in!
                        // We can get rid of the stupid status..
                        delete data.status;

                        // Saving our account details in localstorage for the next 15 minutes
                        //Storage.save("account_data", data, 0.25);

                        // Setting the user details for all controllers
                        user.email = data.email;
                        user.user_id = data.user_id;

                        // Closing the login prompt incase its open
                        Prompts.close_prompt("login");

                    }
                });
            }
        },

        login: function(email, password) {

            if (!loggingin) {

                loggingin = true;
                // Preparing the data to send
                var data = {
                    "email": email,
                    "password": password
                };

                // Asking the API very nicely to let us in
                $http.post("/api/account/login.php", data).success(function(data, status) {

                    loggingin = false;
                    if (data.status == "success") {

                        // Saving our account details in localstorage for the next two hours
                        //Storage.save("account_data", data, 2);
                        // Setting the user details for all controllers
                        user.email = data.email;
                        user.user_id = data.user_id;

                        // Closing the login prompt incase its open
                        Prompts.close_prompt("login");
                        window.location.reload();
                        //EaM.showMessage("loggedin");

                    } else {
                        //EaM.showError("wronguserorpass");
                    }

                });
            }

        },

        send_reset: function(email) {

            if (!sendingreset) {
                sendingreset = true;
                var deferred = $q.defer();

                // This function is quite simple.
                // Saying goodbye to the API
                var data = {
                    email: email
                }
                $http.post("/api/account/send_password_reset.php", data).success(function(data, status) {
                    sendingreset = false;
                    deferred.resolve(data);

                });

                return deferred.promise;
            }

        },

        check_code: function(code, newpassword) {

            if (!checkingcode) {

                checkingcode = true;
                var deferred = $q.defer();

                // This function is quite simple.
                // Saying goodbye to the API
                var data = {
                    code: code,
                    newpassword: newpassword
                }
                $http.post("/api/account/reset_password.php", data).success(function(data, status) {
                    checkingcode = false;
                    deferred.resolve(data);

                });

                return deferred.promise;
            }

        },

        logout: function() {

            // This function is quite simple.
            // Saying goodbye to the API
            $http.post("/api/account/logout.php").success(function(data, status) {

                // Removing our data
                Storage.removeall();
                delete user.email;
                delete user.permissions;

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
