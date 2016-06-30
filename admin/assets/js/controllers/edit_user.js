dah.controller('edit_userCtrl', function($scope, $http, Users, $routeParams, AccountData, EaM, Loading) {

    $scope.me = AccountData;

    if ($routeParams.user_id) {
        Users.findUserFromUserId($routeParams.user_id).then(function(response) {
            $scope.user = response;
        });
    }


    $scope.updateUser = function() {

        var data = JSON.parse(JSON.stringify($scope.user));
        Loading.startLoading();
        $http.post("/admin/api/users/edit_user.php", data).success(function(response, status) {
            Loading.stopLoading();
            console.log(response);
            if (response.status && response.status == "success") {
                EaM.showMessage("saved");
                Users.updateUser($scope.user);
            } else {
                EaM.showError("error");
            }
        });

    }

    $scope.removeUser = function() {
        var data = {
            "user_id": $scope.user.user_id,
            "password": $scope.confirm_password
        }
        Loading.startLoading();
        $http.post("/admin/api/users/remove_user.php", data).success(function(response, status) {
            Loading.stopLoading();
            console.log(response)
            $scope.confirm_password = "";
            if (response.status && response.status == "success") {
                EaM.showMessage("deleted");
                Users.remove($scope.employee.user_id);
                window.location.href="#/employees";
            } else {
                EaM.showError("error");
            }
        });
    }


});
