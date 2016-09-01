dah.controller("investmentCtrl", function($scope, investment, Currency) {
    $scope.investment = investment;
    $scope.currency = Currency.data.currentCurrency;
    $scope.invest_amount = 10;
})
