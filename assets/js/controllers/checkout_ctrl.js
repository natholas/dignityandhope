dah.controller("CheckoutCtrl", function($scope, Currency, Storage, $http, AccountData, Cart, Checkout, Prompts) {

    $scope.customerinfo = AccountData;
    $scope.currency = Currency.data.currentCurrency;
    $scope.cart = Cart;

    $scope.checkout = Checkout.checkout;

    $scope.countries = [
        "Country 1",
        "Country 2"
    ]

    $scope.show_login = function () {
        Prompts.open_prompt("login");
    }

})
