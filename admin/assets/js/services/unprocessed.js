dah.service('Unprocessed', function($http, AccountData, $timeout) {

    var user = AccountData;
    this.data = {};
    this.data.investments = 0;
    this.data.products = 0;
    this.data.email = 0;

    var data = this.data;
    var refresher = null;

    var get_unprocessed = function() {

        $http.post("/admin/api/approval/get_unprocessed_count.php").success(function(response, status) {
            if (response.status && response.status == "success") {
                data.investments = response.investments;
                data.products = response.products;
                data.emails = response.emails;
            }
            refresher = $timeout(get_unprocessed, 30000);
        });
    }

    window.onblur = function() {
        $timeout.cancel(refresher)
        refresher = null;
    };

    window.onfocus = function() {
        get_unprocessed();
    };

    get_unprocessed();

});
