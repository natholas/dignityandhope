dah.controller("AccountCtrl", function($scope, Currency, AccountData, Orders, Account) {

    $scope.customerinfo = AccountData;
    $scope.currency = Currency.data.currentCurrency;

    $scope.orders = Orders.data;

    $scope.countries = [
        "Country 1",
        "Country 2"
    ];

    $scope.cancel_change_details = function () {
        if ($scope.personal_details_form.$dirty) {
            $scope.personal_details_form.$dirty = false;
            Account.info(true);
        }
    }

    $scope.update_personal_details = function () {
        Account.update_personal_details($scope.new_account_data);
        $scope.change_personal_details = false;
    }

})
