dah.controller("ConfirmationCtrl", function($scope, Checkout, Currency, $routeParams, Storage, Investments, Cart) {

    $scope.currency = Currency.data.currentCurrency;

	$scope.order_id = $routeParams.order_id;

	Storage.remove("order_history");
	Cart.empty();
	Investments.get(0,18, true);

})
