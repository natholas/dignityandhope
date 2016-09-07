dah.controller("ConfirmationCtrl", function($scope, Checkout, Currency) {

    $scope.order = Checkout.data.order;
    $scope.currency = Currency.data.currentCurrency;

})
