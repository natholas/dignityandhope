dah.controller('add_employeeCtrl', function($scope, $http, AccountData, Employees, organizations, $routeParams, EaM, Account, Loading) {

    $scope.organizations = organizations;
    $scope.user = AccountData;

    $scope.employee = {
        "password": uniqueString(8),
        "send_mail": true
    };

    $scope.saveEmployee = function() {

        var data = JSON.parse(JSON.stringify($scope.employee));
        
        Loading.startLoading();
        $http.post("/admin/api/user_org_manage/create_new_user.php", data).success(function(response, status) {
            Loading.stopLoading();
            if (response.status && response.status == "success") {
                EaM.showMessage("saved");
                Employees.updateEmployee($scope.employee);
                window.location.href = "#/edit_employee/" + response.user_id;
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
            "user_id": $scope.employee.user_id
        }

        $http.post("/admin/api/user_org_manage/remove_user.php", data).success(function(response, status) {

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
