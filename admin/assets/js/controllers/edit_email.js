dah.controller('edit_emailCtrl', function($scope, $http, AccountData, Emails, $routeParams, EaM, Account, Users, $timeout, Loading) {

    $scope.user = AccountData;
    $scope.emails = Emails.data;
    $scope.suggested_users = [];

    $scope.pick_user = function(user_id, wait) {
        $timeout(function() {
            if (!$scope.email.to) {
                if (user_id == null && $scope.suggested_users.length) {
                    user_id = $scope.suggested_users[0].user_id;
                }
                $scope.suggested_users = [];

                if (user_id != null) {
                    Users.findUserFromUserId(user_id).then(function(response) {
                        $scope.email.user = response;
                        $scope.user_search_term = response.email;
                    });
                }
            }
        }, wait)
    }

    // Getting the index for this email
    if ($routeParams.email_id) {

        Emails.load_email($routeParams.email_id).then(function(response) {
            $scope.email = response;
            $scope.user_search_term = $scope.email.user.email;
        });

    }

    $scope.find_users = function() {
        $scope.email.to = null;
        if ($scope.user_search_term && $scope.user_search_term.length > 2) {
            Users.findUsersFromString($scope.user_search_term).then(function(response) {
                $scope.suggested_users = response;
            })
        }
    }

    $scope.updateEmail = function() {

        var data = JSON.parse(JSON.stringify($scope.email));

        Loading.startLoading();
        $http.post("/admin/api/emails/update_email.php", data).success(function(response, status) {
            Loading.stopLoading();
            console.log(response);
            if (response.status && response.status == "success") {
                Account.info(true);
                if ($scope.email.status == "SENT") {
                    EaM.showMessage("sent");
                    location.href = "#/emails";
                } else {
                    EaM.showMessage("saved");
                }
            } else {
                EaM.showError("error");
            }
        });
    }

    $scope.removeEmail = function() {
        var data = {
            "email_id": $scope.email.email_id
        }

        $http.post("/admin/api/emails/remove_email.php", data).success(function(response, status) {
            console.log(response);
            if (response.status && response.status == "success") {
                EaM.showMessage("deleted");
                Emails.load_emails();
                window.location.href="#/emails";
            } else {
                EaM.showError("error");
            }
        });
    }

});
