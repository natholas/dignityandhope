dah.service('Statistics', function($http, Storage) {

    this.data = {};
    var data = this.data;

    this.get_statistics = function() {
        // Checking to see if the statistics are already saved in the localstorage
        var new_data = Storage.load("statistics");
        if (new_data) {

            // The statistics were saved in localstorage
            data.investments = new_data.investments;
            data.products = new_data.products;

        } else {

            // The statistics are not in localstorage or not valid anymore
            // Getting statistics from API
            $http.post("/admin/api/reporting/get_statistics.php").success(function(response, status) {
                if (response && response.status == "success") {
                    data.investments = response.investments;
                    data.products = response.products;

                    // Saving statistics for the next 2 hours
                    Storage.save("statistics", data, 2);

                }
            });
        }
    }

    this.get_statistics();
});
