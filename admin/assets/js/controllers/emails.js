dah.controller('emailCtrl', function($scope, AccountData, Account, Emails, Prompts) {
        $scope.email_data = Emails.data;

        $scope.showFilter = function() {
            Prompts.open_prompt("email_filter");
        }

        $scope.clearFilter = function() {
            $scope.email_data.settings.pages_loaded = 0;
            $scope.email_data.settings.offset = 0;
            Emails.clearFilter();
            Emails.load_emails(true, false);
        }

        $scope.clearFilter();

});
