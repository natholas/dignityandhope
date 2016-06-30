dah.service('Reporting', function($http, $q) {

    this.data = {};

    this.get_data = function(from, to) {
        var deferred = $q.defer();
        var data = {from: from.getTime() / 1000, to: to.getTime() / 1000};
        $http.post("/admin/api/reporting/get_totals.php", data).success(function(response, status) {
            if (response && response.status == "success") {
                deferred.resolve(response);
            }
        });
        return deferred.promise;
    }

});
