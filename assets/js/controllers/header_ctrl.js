dah.controller("headerCtrl", function($scope, Prompts, AccountData, Account, Cart, Storage, Currency) {

    $scope.currency = Currency.data.currentCurrency;
    $scope.login_info = {};
    $scope.prompt_info = {};

    $scope.errorandmessages = {};

    // Getting a reference to our account data
    $scope.user = AccountData;

    $scope.cart = Cart;
    $scope.cartdata = Cart.data;

    $scope.prompts = Prompts.data;
    $scope.prompt_info = Prompts.count;

    //$scope.errorandmessages = EaM.data;

    $scope.hide_prompts = Prompts.close_prompt;

    // Lets get the status of our account
    Account.info();

    $scope.show_login = function () {
        Prompts.open_prompt("login");
    }

    $scope.$watch("cartdata", function(new_cart_data) {
        Storage.save("cart", new_cart_data, 48);
    }, true);

    $scope.login = function() {
        Account.login($scope.login_info.email, $scope.login_info.password);
    }

    $scope.sendreset = function() {

        Account.send_reset($scope.login_info.email).then(function(response) {
            if (response.status == "success") {

                $scope.sent_reset_email = true;
                $scope.sendresetemail = false;

            } else {
                //EaM.showError("emailnotfound");
            }
        });
    }

    $scope.check_code = function() {

        if ($scope.login_info.newpassword == $scope.login_info.repeat_newpassword) {
            Account.check_code($scope.login_info.code, $scope.login_info.newpassword).then(function(response) {
                if (response.status == "success") {

                    $scope.sent_reset_email = false;
                    $scope.sendresetemail = false;
                    //EaM.showMessage("success");

                } else {
                    //EaM.showError("wrongcode");
                }
            });
        } else {
            //EaM.showError("passwordsdontmatch");
        }
    }

    $scope.logout = function () {
        Account.logout();
    }


});
