dah.controller("FooterCtrl", function($scope, Currency) {
    $scope.currency = Currency.data.currentCurrency.name;
    $scope.curr = Currency;
});
