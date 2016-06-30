dah.controller('organizationsCtrl', function($scope, Organizations, AccountData, Settings, Prompts) {

    $scope.organizations = Organizations.data;
    $scope.user = AccountData;
    $scope.settings = Settings.data.settings;

    $scope.scroll_to_top = function() {
        document.body.scrollTop = document.documentElement.scrollTop = 0;
    }

});
