dah.controller("ordersCtrl", function($scope, Orders, AccountData, Settings, Prompts) {

    $scope.user = AccountData;

    $scope.orders = Orders.data


    $scope.settings = Settings.data.settings;

    $scope.showFilter = function() {
        Prompts.open_prompt("orders_filter");
    }

    $scope.clearFilter = function() {
        $scope.orders.settings.pages_loaded = 0;
        $scope.orders.settings.offset = 0;
        Orders.clearFilter();
        Orders.load_orders(true, false, true);
    }

    $scope.scroll_to_top = function() {
        document.body.scrollTop = document.documentElement.scrollTop = 0;
    }

});
