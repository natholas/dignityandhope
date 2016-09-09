dah.service("Investments", function($http, $q, Orders) {

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
                find_my_investments();
                deferred.resolve(this.data);
            }
        });

        return deferred.promise;
    }

    // This function finds a spesific investment
    this.get_one = function (investment_id) {

        var found = false,
            deferred = $q.defer();

        // First lets check if we already have this investment
        for (var i = 0; i < data.investments.length; i++) {
            if (data.investments[i].investment_id == investment_id) {
                deferred.resolve(this.data.investments[i]);
                found = true;
                break;
            }
        }

        if (!found) {

            var params = {
                "investment_id": investment_id
            }

            $http.post("/api/investments/get_investment.php", params).then(function(response) {
                if (response.data.status == "success") {
                    response.data.investment.invested = find_my_investment(response.data.investment.investment_id);
                    deferred.resolve(response.data.investment);
                }
            });
        }

        return deferred.promise;
    }

    this.find_my_investments = function () {
        for (var i = 0; i < data.investments.length; i++) {
            for (var ii = 0; ii < Orders.data.investments.length; ii++) {
                if (Orders.data.investments[ii].investment_id == data.investments[i].investment_id) {
                    data.investments[i].invested = true;
                }
            }
        }
    }

    this.find_my_investment = function (investment_id) {
        for (var ii = 0; ii < Orders.data.investments.length; ii++) {
            if (Orders.data.investments[ii].investment_id == investment_id) {
                return true;
            }
        }
        return false;
    }

    var find_my_investments = this.find_my_investments;
    var find_my_investment = this.find_my_investment;


});
