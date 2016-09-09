dah.controller("investmentCtrl", function($scope, investment, Currency, Cart, $timeout, Investments) {
    $scope.investment = investment;
    $scope.currency = Currency.data.currentCurrency;
    $scope.cart_item_info = {};
    $scope.invest_amount = 10;
    $scope.cart = Cart;

    $scope.addToCart = function () {
        $scope.fixInvestmentAmount();
        Cart.add('investment', $scope.investment.investment_id, $scope.invest_amount);
        $timeout(function () {
            Cart.cart_item_info("investment", investment.investment_id).then(function(response) {
                $scope.cart_item_info.data = response.data;
            })
        }, 100);
    }

    // Checking if this investment is in the cart
    Cart.cart_item_info("investment", investment.investment_id).then(function(response) {

        $scope.cart_item_info = response;

        if (!response.data) {
            $scope.invest_amount = 10;
        } else {
            $scope.invest_amount = parseFloat(($scope.cart_item_info.amount / $scope.currency.value).toFixed(2));
        }

        // Setting up the recommended investment amount
        $scope.fixInvestmentAmount();

    });

    $scope.fixInvestmentAmount = function () {
        if ($scope.invest_amount < 1) {
            $scope.invest_amount = 1;
        } else if ($scope.investment.amount_needed - $scope.investment.amount_invested < $scope.invest_amount * $scope.currency.value) {
            $scope.invest_amount = parseFloat((($scope.investment.amount_needed - $scope.investment.amount_invested) / $scope.currency.value).toFixed(2));
        }
    }
})
