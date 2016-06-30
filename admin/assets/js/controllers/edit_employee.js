dah.controller('edit_employeeCtrl', function($scope, $http, AccountData, Employees, organizations, $routeParams, EaM, Account, Loading) {

    $scope.organizations = organizations;
    $scope.user = AccountData;


    // Getting the index for this employee
    if ($routeParams.user_id) {
        Employees.get_employee_details($routeParams.user_id).then(function(response) {
            $scope.employee = response;
            $scope.changeOrg();
        });

    }

    $scope.updateEmployee = function() {

        var data = JSON.parse(JSON.stringify($scope.employee));

        Loading.startLoading();
        $http.post("/admin/api/user_org_manage/edit_user_details.php", data).success(function(response, status) {
            Loading.stopLoading();
            console.log(response);
            if (response.status && response.status == "success") {
                EaM.showMessage("saved");
                Employees.updateEmployee($scope.employee);
                Account.info(true);
            } else {
                EaM.showError("error");
            }
        });
    }

    $scope.changeOrg = function() {
        for (var i = 0; i < $scope.organizations.length; i ++) {
            if ($scope.organizations[i].organization_id == $scope.employee.organization_id) {
                $scope.employee.organization = $scope.organizations[i].name;
                break;
            }
        }
    }

    $scope.removeEmployee = function() {
        var data = {
            "user_id": $scope.employee.user_id,
            "password": $scope.confirm_password
        }
        Loading.startLoading();
        $http.post("/admin/api/user_org_manage/remove_user.php", data).success(function(response, status) {
            Loading.stopLoading();
            console.log(response)
            $scope.confirm_password = "";
            if (response.status && response.status == "success") {
                EaM.showMessage("deleted");
                Employees.remove($scope.employee.user_id);
                window.location.href="#/employees";
            } else {
                EaM.showError("error");
            }
        });
    }

});
