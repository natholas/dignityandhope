dah.controller('investmentsCtrl', function($scope, Investments, AccountData, Settings, Prompts, $routeParams) {

    $scope.investments = Investments.data;
    $scope.user = AccountData;
    $scope.settings = Settings.data.settings;

    $scope.showFilter = function() {
        Prompts.open_prompt("investment_filter");
    }

    $scope.clearFilter = function() {
        $scope.investments.settings.pages_loaded = 0;
        $scope.investments.settings.offset = 0;
        Investments.clearFilter();
        Investments.load_investments(true, false, true);
    }

    $scope.scroll_to_top = function() {
        document.body.scrollTop = document.documentElement.scrollTop = 0;
    }

    if ($routeParams.preset) {
        if ($routeParams.preset == "approve") {
            $scope.investments.settings.filter = {
                drafts:false,
                ended:false,
                live:false,
                organization_id:$scope.user.organization_id,
                pending:true,
                removed:false,
                search:""
            }

            Investments.load_investments(true, false);
        } else {
            Investments.clearFilter();
            Investments.load_investments(true, false, true);
        }
    }

});
