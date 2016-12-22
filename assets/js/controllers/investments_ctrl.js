dah.controller("investmentsCtrl", function($scope, Investments, Currency) {
    $scope.investments = Investments.data;
    $scope.currency = Currency.data.currentCurrency;
	$scope.bg_image = "/assets/images/hero-image.jpg";
});
