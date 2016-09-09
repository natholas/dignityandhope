dah.factory('Account', function($http, Storage, AccountData, Prompts, $q) {

    var user = AccountData,
    loggingin = false,
    sendingreset = false,
    checkingcode = false;

    return {
        info: function(ignore) {

            // This little function gets our login status
            $http.post("/api/account/check_login.php").then(function(response) {

                if (response.data && response.data.status == "success") {

                    // Look at that! The server has told us that we are logged in!

                    // Setting the user details for all controllers
                    user.email = response.data.email;
                    user.user_id = response.data.user_id;

                    // Closing the login prompt incase its open
                    Prompts.close_prompt("login");

                    // We will also need our personal info
                    // Lets first see if it is already in local Storage
                    var old_personal_info = Storage.load("personal_info");
                    if (old_personal_info && !ignore) {
                        user.personal_info = old_personal_info;
                    } else {

                        // Getting personal_info from the server
                        $http.post("/api/account/get_account_details.php").then(function(response) {

                            if (response.data && response.data.status == "success") {
                                response.data.personal_info.dob = timestampToDob(response.data.personal_info.dob);
                                user.personal_info = response.data.personal_info;
                                Storage.save("personal_info", user.personal_info, 24);
                            }

                        });
                    }
                }
            });
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

        },

        update_personal_details: function() {
            var params = AccountData.personal_info;
            params.dob = dobToTimestamp(params.dob);
            $http.post("/api/account/change_personal_details.php", params).then(function(response) {
                console.log(response);
            })
        }

    }
});


dah.factory('AccountData', function() {

    // In this factory we store our account data.
    // Any controller that uses this service will be returned a reference to it
    return {};

});
