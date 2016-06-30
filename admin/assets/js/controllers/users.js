dah.controller('usersCtrl', function($scope, Users, Prompts) {

    $scope.users = Users.data;

    $scope.showFilter = function() {
        Prompts.open_prompt("users_filter");
    }

    $scope.clearFilter = function() {
        $scope.users.settings.pages_loaded = 0;
        $scope.users.settings.offset = 0;
        Users.clearFilter();
        Users.load_users(true, false, true);
    }

});
