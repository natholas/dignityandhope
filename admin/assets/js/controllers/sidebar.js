dah.controller('sidebarCtrl', function($scope, Prompts, AccountData, Settings, Unprocessed) {

    // Getting a reference to our account data
    $scope.user = AccountData;
    $scope.settings = Settings.data.settings;
    $scope.unprocessed = Unprocessed.data;

    // Setting up the page info object.
    $scope.page_info = {};
    $scope.$on('$routeChangeStart', function(from, to) {
        if (to.$$route) {
            var newCtrl = to.$$route.controller;
            if (newCtrl) {
                $scope.page_info.controller = newCtrl;
            }
        }
    });

});
