dah.controller("FooterCtrl", function($scope, Currency) {
    $scope.currency = Currency.data.currentCurrency;
    $scope.curr = Currency;
});
