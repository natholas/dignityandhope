dah.controller('headerCtrl', function($scope, Account, AccountData, Prompts, EaM, Investments, Products, Organizations, $location, Emails, Users) {

    $scope.login_info = {};
    $scope.prompt_info = {};

    $scope.errorandmessages = {};

    $scope.investments = Investments.data;
    $scope.emails = Emails.data;
    $scope.products = Products.data;
    $scope.users = Users.data;
    $scope.organizations = Organizations.data;

    // Getting a reference to our account data
    $scope.user = AccountData;

    $scope.prompts = Prompts.data;
    $scope.prompt_info = Prompts.count;

    $scope.errorandmessages = EaM.data;

    $scope.hide_prompts = Prompts.close_prompt;

    // Lets get the status of our account
    Account.info();

    $scope.login = function() {
        // Attempting to login
        Account.login($scope.login_info.username, $scope.login_info.password);
    }

    $scope.sendreset = function() {

        Account.send_reset($scope.login_info.email).then(function(response) {
            if (response.status == "success") {

                $scope.sent_reset_email = true;
                $scope.sendresetemail = false;

            } else {
                EaM.showError("emailnotfound");
            }
        });
    }

    $scope.check_code = function() {

        if ($scope.login_info.newpassword == $scope.login_info.repeat_newpassword) {
            Account.check_code($scope.login_info.code, $scope.login_info.newpassword).then(function(response) {
                if (response.status == "success") {

                    $scope.sent_reset_email = false;
                    EaM.showMessage("success");

                } else {
                    EaM.showError("wrongcode");
                }
            });
        } else {
            EaM.showError("passwordsdontmatch");
        }
    }

    $scope.$on('$routeChangeStart', function() {
        $scope.current_page = $location.path().split("/")[1];
    });

    window.onscroll = function(ev) {
        if ($scope.current_page == "investments" && !$scope.investments.autoLoading && (window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
            $scope.investments.settings.pages_loaded += 1;
            $scope.investments.autoLoading = true;
            $scope.investments.settings.offset = $scope.investments.settings.pages_loaded * $scope.investments.settings.limit;
            Investments.load_investments(true, true);
            $scope.$apply();
        }

        else if ($scope.current_page == "products" && !$scope.products.autoLoading && (window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
            $scope.products.settings.pages_loaded += 1;
            $scope.products.autoLoading = true;
            $scope.products.settings.offset = $scope.products.settings.pages_loaded * $scope.products.settings.limit;
            Products.load_products(true, true);
            $scope.$apply();
        }

        else if ($scope.current_page == "emails" && !$scope.emails.autoLoading && (window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
            $scope.emails.settings.pages_loaded += 1;
            $scope.emails.autoLoading = true;
            $scope.emails.settings.offset = $scope.emails.settings.pages_loaded * $scope.emails.settings.limit;
            Emails.load_emails(true, true);
            $scope.$apply();
        }

        else if ($scope.current_page == "users" && !$scope.users.autoLoading && (window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
            $scope.users.settings.pages_loaded += 1;
            $scope.users.autoLoading = true;
            $scope.users.settings.offset = $scope.users.settings.pages_loaded * $scope.users.settings.limit;
            Users.load_users(true, true);
            $scope.$apply();
        }
    };


    $scope.load_investments = function() {
        $scope.investments.settings.pages_loaded = 0;
        $scope.investments.settings.offset = 0;
        Investments.load_investments(true, false);
        $scope.hide_prompts('investment_filter');
    }

    $scope.load_products = function() {
        $scope.products.settings.pages_loaded = 0;
        $scope.products.settings.offset = 0;
        Products.load_products(true, false);
        $scope.hide_prompts('product_filter');
    }

    $scope.load_emails = function() {
        $scope.emails.settings.pages_loaded = 0;
        $scope.emails.settings.offset = 0;
        Emails.load_emails(true, false);
        $scope.hide_prompts('email_filter');
    }

    $scope.load_users = function() {
        $scope.users.settings.pages_loaded = 0;
        $scope.users.settings.offset = 0;
        Users.load_users(true, false);
        $scope.hide_prompts('users_filter');
    }

});
