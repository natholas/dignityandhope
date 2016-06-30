dah.controller('metaCtrl', function($scope, Account, AccountData, Prompts) {


    // Getting a reference to our account data
    $scope.user = AccountData;

    $scope.logout = function() {
        // Something something logout
        Account.logout();
        // The end.
    }

});
