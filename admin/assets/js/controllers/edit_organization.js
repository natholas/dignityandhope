dah.controller('edit_organizationCtrl', function($scope, $http, AccountData, Organizations, $routeParams, EaM, Loading) {


    $scope.organizations = Organizations.data.organizations;
    $scope.user = AccountData;

    // Getting the index for this organization
    if ($routeParams.organization_id) {
        $scope.organization = $scope.organizations[Organizations.getOrganizationIndex($routeParams.organization_id)];
    }

    $scope.updateOrganization = function() {

        var data = JSON.parse(JSON.stringify($scope.organization));

        Loading.startLoading();
        $http.post("/admin/api/user_org_manage/edit_organization.php", data).success(function(response, status) {
            Loading.stopLoading();
            console.log(response);
            if (response.status && response.status == "success") {
                EaM.showMessage("saved");
                Organizations.updateOrganization($scope.organization);
            } else {
                EaM.showError("error");
            }
        });
    }

    $scope.removeOrganization = function() {
        var data = {
            "organization_id": $scope.organization.organization_id,
            "remove_users": false,
            "move_active_investments": false,
            "move_inactive_investments": false,
            "password": $scope.confirm_password
        }

        Loading.startLoading();
        $http.post("/admin/api/user_org_manage/remove_organization.php", data).success(function(response, status) {
            Loading.stopLoading();
            console.log(response)
            if (response.status && response.status == "success") {
                EaM.showMessage("deleted");
                Organizations.remove($scope.organization.organization_id);
                window.location.href="#/organizations";
            } else {
                EaM.showError("error");
            }
        });
    }

});
