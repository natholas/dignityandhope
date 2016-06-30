dah.controller('productsCtrl', function($scope, Products, AccountData, Settings, Prompts) {

    $scope.products = Products.data;
    $scope.user = AccountData;
    $scope.settings = Settings.data.settings;

    $scope.showFilter = function() {
        Prompts.open_prompt("product_filter");
    }

    $scope.clearFilter = function() {
        $scope.products.settings.pages_loaded = 0;
        $scope.products.settings.offset = 0;
        Products.clearFilter();
        Products.load_products(true, false, true);
    }

    $scope.scroll_to_top = function() {
        document.body.scrollTop = document.documentElement.scrollTop = 0;
    }

});
