dah.controller("AccountCtrl", function($scope, Currency, AccountData, Orders, Account, Data, $timeout, Data) {

    $scope.customerinfo = AccountData;
    $scope.currency = Currency.data.currentCurrency;
    $scope.curr = Currency.data;
	$scope.membership_types = Data.membership_types;

    $scope.orders = Orders.data;

    $scope.countries = Data.countries;

	$scope.new_date = function() {
		return new Date();
	}

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

	$scope.format_date = function(date, name) {
		var cursor_start;
		if (document.getElementsByName(name)[0] == document.activeElement) cursor_start = document.activeElement.selectionStart;
		if (date.string) {
			date.string = date.string.split("");
			var numbers = 0;
			for (var i = 0; i < date.string.length; i++) {
				if (((date.string[i] != "/" && date.string[i] != ".") || date.string[i-1] == "." || date.string[i-1] == "/") && !is_numeric(date.string[i])) {
					date.string.splice(i,1);
					continue;
				}
				if (!is_numeric(date.string[i]) && ((date.string[i] != "/" && date.string[i] != ".") || (i != 2 && i != 5))) {
					if ((date.string[i] == "/" || date.string[i] == ".") && (i == 1 || i == 4) && date.string.length <= 5) {
						date.string.splice(i-1, 0, "0");
						cursor_start += 1;
					}
				}
				if (is_numeric(date.string[i])) numbers += 1;
			}
			date.string = date.string.join("");
		}
		if (document.getElementsByName(name)[0] == document.activeElement) $timeout(function() {document.activeElement.setSelectionRange(cursor_start, cursor_start);}, 1);
		if (numbers == 8) $scope.personal_details_form.dob_field.$setValidity("invalid", true);
		else $scope.personal_details_form.dob_field.$setValidity("invalid", false);
	}

})
