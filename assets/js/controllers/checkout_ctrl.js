dah.controller("CheckoutCtrl", function($scope, Currency, Storage, $http, AccountData, Cart, Checkout) {

    $scope.customerinfo = AccountData;
    $scope.currency = Currency.data.currentCurrency;
    $scope.cart = Cart;

    $scope.checkout = Checkout.checkout;

    $scope.countries = [
        "Country 1",
        "Country 2"
    ]

})
