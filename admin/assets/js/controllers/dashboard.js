dah.controller('dashboardCtrl', function($scope, $http, AccountData, Admin_messages) {

    // Lets first get the account status
    $scope.user = AccountData;
    $scope.messages = Admin_messages.data;

    $scope.submit_admin_message = function() {
        $http.post("/admin/api/messages/add_message.php", $scope.new_message).then(function(response) {
            if (response.status = "success") {
                $scope.new_message.post_time = new Date().getTime() / 1000;
                $scope.new_message.username = $scope.user.username;
                Admin_messages.add_message($scope.new_message);
            }
        });
    }

});
