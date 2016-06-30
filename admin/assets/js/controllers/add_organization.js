dah.controller('add_organizationCtrl', function($scope, $http, Organizations, AccountData, new_organization, EaM, Loading) {

    $scope.user = AccountData;
    $scope.organization = new_organization;

    $scope.saveOrganization = function() {

        // Uploading the new organization to the server
        var data = JSON.parse(JSON.stringify($scope.organization));

        Loading.startLoading();
        $http.post("/admin/api/user_org_manage/create_organization.php", data).success(function(response, status) {
            Loading.stopLoading();
            console.log(response);
            if (response.status && response.status == "success") {
                EaM.showMessage("saved");
                $scope.organization.organization_id = response.organization_id;
                $scope.organization.status = 1;
                Organizations.addOrganization($scope.organization);
                window.location.href = "#/edit_organization/" + $scope.organization.organization_id;
            } else {
                EaM.showError("error");
            }
        });
    }

    // Focusing the first input
    document.getElementById("first_input").focus();


});
