dah.controller('add_emailCtrl', function($scope, $http, AccountData, Account, Emails, $routeParams, new_email, Users, $timeout, Investments, EaM, Products) {

    $scope.email = new_email;
    $scope.suggested_users = [];
    $scope.suggested_investments = [];

    $scope.user = AccountData;

    $scope.pick_user = function(user_id, wait) {
        $timeout(function() {
            if (!$scope.email.to) {
                if (user_id == null && $scope.suggested_users.length) {
                    user_id = $scope.suggested_users[0].user_id;
                }
                $scope.suggested_users = [];

                if (user_id != null) {
                    Users.findUserFromUserId(user_id).then(function(response) {
                        $scope.email.to = response;
                        $scope.user_search_term = response.email;
                    });
                }
            }
        }, wait)
    }

    $scope.pick_investment = function(investment_id, wait) {
        $timeout(function() {
            if (!$scope.email.to) {

                if (investment_id == null && $scope.suggested_investments.length) {
                    investment_id = $scope.suggested_investments[0].investment_id;
                }
                $scope.suggested_investments = [];

                if (investment_id != null) {
                    Investments.findInvestmentFromInvestmentId(investment_id).then(function(response) {
                        $scope.email.to = response;
                        $scope.investment_search_term = response.name;
                    });
                }
            }
        }, wait);
    }

    $scope.pick_product = function(product_id, wait) {
        $timeout(function() {
            if (!$scope.email.to) {

                if (product_id == null && $scope.suggested_products.length) {
                    product_id = $scope.suggested_products[0].product_id;
                }
                $scope.suggested_products = [];

                if (product_id != null) {
                    Products.findProductFromProductId(product_id).then(function(response) {
                        $scope.email.to = response;
                        $scope.product_search_term = response.name;
                    });
                }
            }
        }, wait);
    }

    if ($routeParams.type && $routeParams.type == "investment") {
        $scope.type = "investment";
    } else if ($routeParams.type && $routeParams.type == "product") {
        $scope.type = "product";
    } else {
        $scope.type = "single";
    }

    if ($routeParams.to) {
        if ($scope.type == "single") {
            $scope.pick_user($routeParams.to);
        } else if ($scope.type == "investment") {
            $scope.pick_investment($routeParams.to);
        } else if ($scope.type == "product") {
            $scope.pick_product($routeParams.to);
        }
    }

    $scope.find_users = function() {
        $scope.email.to = null;
        if ($scope.user_search_term && $scope.user_search_term.length > 2) {
            Users.findUsersFromString($scope.user_search_term).then(function(response) {
                $scope.suggested_users = response;
            })
        }
    }

    $scope.find_investments = function() {
        $scope.email.to = null;
        if ($scope.investment_search_term && $scope.investment_search_term.length > 2) {
            Investments.findInvestmentsFromString($scope.investment_search_term).then(function(response) {
                $scope.suggested_investments = response;
            })
        }
    }

    $scope.find_products = function() {
        $scope.email.to = null;
        if ($scope.product_search_term && $scope.product_search_term.length > 2) {
            Products.findProductsFromString($scope.product_search_term).then(function(response) {
                $scope.suggested_products = response;
            })
        }
    }

    $scope.save_email = function() {
        if (!$scope.email.to) {
            $scope.add_email.user_search_term.$setValidity("required", false);
            return false;
        }

        var data = JSON.parse(JSON.stringify($scope.email));

        if ($scope.type == "single") {
            var url = "/admin/api/emails/send_email.php";
            data.user_id = data.to.user_id;
        } else if ($scope.type == "investment") {
            var url = "/admin/api/emails/send_group_email.php";
            data.investment_id = data.to.investment_id;
        } else {
            var url = "/admin/api/emails/send_group_email.php";
            data.product_id = data.to.product_id;
        }

        delete data.to;

        $http.post(url, data).success(function(response, status) {
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



});
