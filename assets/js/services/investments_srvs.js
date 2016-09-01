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

    this.get_one = function (investment_id) {
        var deferred = $q.defer();

        var params = {
            "investment_id": investment_id
        }

        $http.post("/api/investments/get_investment.php", params).then(function(response) {
            if (response.data.status == "success") {
                deferred.resolve(response.data.investment);
            }
        })

        return deferred.promise;
    }
});
