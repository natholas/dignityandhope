dah.controller("membershipsCtrl", function($scope, Memberships, Data, Currency) {

	$scope.currency = Currency.data.currentCurrency;
	$scope.memberships = Memberships;
	$scope.data = Data.membership_types;

});
