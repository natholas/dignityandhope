dah.controller('dashboardCtrl', function($scope, AccountData, Statistics) {

    // Lets first get the account status
    $scope.user = AccountData;
    $scope.statistics = Statistics.data;

    $scope.points = ["jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"]

    $scope.data = [9,21,1,2,5,1,9,5,1,2,5,1];

});
