dah.controller('edit_accountCtrl', function($scope, AccountData, $http, Employees, Organizations, EaM, Account, Loading) {

    $scope.profile = AccountData;
    $scope.organizations = Organizations.data.organizations;

    Employees.get_employee_details($scope.profile.user_id).then(function(response) {
        $scope.profile = response;

        for (var i = 0; i < $scope.organizations.length; i ++) {
            if ($scope.organizations[i].organization_id == $scope.profile.organization_id) {
                $scope.profile.organization = $scope.organizations[i].name;
                break;
            }
        }
    });


    $scope.updateAccount = function() {
        if (!$scope.profile.newpassword || !$scope.profile.newpassword.length || $scope.profile.newpassword == $scope.profile.repeat_newpassword) {
            var data = JSON.parse(JSON.stringify($scope.profile));
            data.send_mail = false;

            Loading.startLoading();
            $http.post("/admin/api/user_org_manage/edit_user_details.php", data).success(function(response, status) {
                Loading.stopLoading();
                console.log(response);
                if (response.status && response.status == "success") {
                    EaM.showMessage("saved");
                    Employees.updateEmployee($scope.profile);
                    Account.info(true);
                } else {
                    EaM.showError("error");
                }
            });
        } else {
            EaM.showError("passwordsdontmatch")
        }
    }

});
