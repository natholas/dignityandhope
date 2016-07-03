dah.controller("orderCtrl", function($scope, $http, Orders, AccountData, Settings, Prompts, $routeParams) {

    $scope.user = AccountData;

    if ($routeParams.order_id) {
        Orders.findOrderFromOrderId($routeParams.order_id).then(function(response) {
            $scope.order = response;
        });
    }

    $scope.updateOrder = function() {

        var settings = {
            "order_id": $scope.order.order_id,
            "status": $scope.order.status
        }

        $http.post("/admin/api/orders/update_order.php", settings).then(function(response) {
            console.log(response.data);
            console.log(settings);
        });

    }

});
