dah.service("Investments", function($http, $q) {

    this.data = {
        "investments": []
    };
    var data = this.data;

    this.get = function (offset, limit) {
        var deferred = $q.defer();

        var params = {
            "offset": offset,
            "limit": limit
        }

        $http.post("/api/investments/get_investments.php", params).then(function(response) {
            console.log(response);
            if (response.data.status == "success") {
                if (offset == 0) {
                    data.investments = response.data.investments;
                } else {
                    data.investments.concat(response.data.investments);
                }
                deferred.resolve(this.data);
            }
        })

        return deferred.promise;
    }
});
