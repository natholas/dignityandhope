dah.controller('employeesCtrl', function($scope, Employees, Organizations, AccountData) {

    $scope.user = AccountData;
    $scope.employees = Employees.data.employees;
    $scope.organizations = Organizations.data.organizations;

    for (var i = 0; i < $scope.employees.length; i ++) {
        for (var ii = 0; ii < $scope.organizations.length; ii ++) {
            if ($scope.employees[i].organization_id == $scope.organizations[ii].organization_id) {
                $scope.employees[i].organization = $scope.organizations[ii].name;
                break;
            }
        }
    }

});
