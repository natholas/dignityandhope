dah.controller("investmentCtrl", function($scope, investment, Currency, Cart) {
    $scope.investment = investment;
    $scope.currency = Currency.data.currentCurrency;

    $scope.cart = Cart;

    $scope.addToCart = function () {
        Cart.add('investment', $scope.investment.investment_id, $scope.invest_amount);
        $scope.cart_item_info = Cart.cart_item_info("investment", investment.investment_id);
    }

    // Checking if this investment is in the cart
    $scope.cart_item_info = Cart.cart_item_info("investment", investment.investment_id);

    // Setting up the recommended investment amount
    $scope.invest_amount = 10;
    if ($scope.investment.amount_needed - $scope.investment.amount_invested < ($scope.invest_amount * $scope.currency.value)) {
        $scope.invest_amount = parseFloat((($scope.investment.amount_needed - $scope.investment.amount_invested) / $scope.currency.value).toFixed(2));
    }

    $scope.change_invest_amount = function () {
        if ($scope.invest_amount < 1) {
            $scope.invest_amount = 1;
        } else if ($scope.investment.amount_needed - $scope.investment.amount_invested < ($scope.invest_amount * $scope.currency.value)) {
            $scope.invest_amount = parseFloat((($scope.investment.amount_needed - $scope.investment.amount_invested) / $scope.currency.value).toFixed(2));
        }
    }
})
