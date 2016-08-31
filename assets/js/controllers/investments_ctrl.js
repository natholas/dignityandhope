dah.controller("investmentsCtrl", function($scope, Investments) {
    $scope.investments = Investments.data;
});
