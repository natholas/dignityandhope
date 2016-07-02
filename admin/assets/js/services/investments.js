dah.service('Investments', function($http, Storage, $q, AccountData) {

    this.data = {};
    var data = this.data;
    data.autoLoading = false;

    data.settings = {
        limit: "10",
        offset: 0,
        order_by: "investment_id DESC",
        pages_loaded: 0,
        filter: {
            organization_id: "any",
            drafts: true,
            pending: true,
            live: true,
            ended: true,
            removed: false,
            search: ""
        }
    }

    this.clearFilter = function() {
        data.settings.filter = {
            organization_id: "any",
            drafts: true,
            pending: true,
            live: true,
            ended: true,
            removed: false,
            search: ""
        }
    }

    this.findInvestmentsFromString = function(string) {
        var deferred = $q.defer();

        // Finding front end users from a string
        var settings = {
            "string": string
        }

        $http.post("/api/investments/find_investments.php", settings).success(function(response, status) {
            if (response.status == "success") {
                for (var i = 0; i < response.investments.length; i++) {
                    response.investments[i].image = JSON.parse(response.investments[i].images)[0];
                }
                response.image =
                deferred.resolve(response.investments);
            } else {
                return [];
            }
        });

        return deferred.promise;

    }

    this.findInvestmentFromInvestmentId = function(investment_id) {
        var deferred = $q.defer();

        // Finding an investment from the investment_id
        var settings = {
            "investment_id": investment_id
        }

        $http.post("/admin/api/investments/get_investment.php", settings).success(function(response, status) {
            if (response.status == "success") {
                deferred.resolve(response.investment);
            } else {
                return {};
            }
        });
        return deferred.promise;
    }

    this.load_investments = function(ignore_cache, autoLoad) {

        // Checking to see if the investments are already saved in the localstorage
        var new_data = Storage.load("investments");
        if (new_data && !ignore_cache) {

            // The investments were saved in localstorage
            data.investments = new_data.investments;
            data.settings = new_data.settings;
            data.count = new_data.count;

        } else {

            // The investments are not in localstorage or not valid anymore
            // Getting the investments from API
            var settings = {
                "limit": data.settings.limit,
                "offset": data.settings.offset,
                "order_by": data.settings.order_by,
                "filter": JSON.stringify(data.settings.filter)
            }

            if (!autoLoad) {
                settings.getcount = 1;
            }

            $http.post("/admin/api/investments/get_investments.php", settings).success(function(response, status) {

                if (response && response.status == "success") {

                    for (var i=0;i<response.investments.length;i++) {
                        var dob = new Date(response.investments[i].dob * 1000);
                        response.investments[i].dob = {
                            "d": dob.getDate(),
                            "m": dob.getMonth() + 1,
                            "y": dob.getFullYear(),
                        }
                    }

                    if (autoLoad) {
                        data.investments = data.investments.concat(response.investments);
                        data.autoLoading = false;
                    } else {
                        data.investments = response.investments;
                        data.count = response.count;
                    }

                    // Saving investments for the next hour
                    //Storage.save("investments", data, 2);

                }
            });
        }
    }

    this.get_investments = function() {
        var deferred = $q.defer();
        var loop = null;

        if (data.investments) {
            deferred.resolve(data.investments);
        } else {
            loop = setInterval(function () {
                if (data.investments) {
                    clearInterval(loop);
                    loop = null;
                    deferred.resolve(data.investments);
                }
            }, 50);
        }

        return deferred.promise;
    }

    this.getInvestmentIndex = function(investment_id) {
        for (var i = 0; i < data.investments.length; i ++) {
            if (data.investments[i].investment_id == investment_id) {
                return i;
            }
        }
    }

    this.updateInvestment = function(new_data) {

        // Looping through the list of all the current investments
        for (var i = 0; i < data.investments.length; i ++) {

            if (new_data.investment_id == data.investments[i].investment_id) {
                // Updating investment
                data.investments[i] = new_data;
                // Updating the localstorage
                Storage.update("investments", data);
                break;
            }
        }
    }

    this.addInvestment = function(new_data) {
        // Adding investment
        data.investments.push(new_data);
        // Updating the localstorage
        Storage.update("investments", data);
    }

    this.new_investment = function() {
        var deferred = $q.defer();

        var new_investment = {
            "status": "DRAFT",
            "money_split": [],
            "new_images": [""],
            "images": [],
            "amount_needed": 0,
            "amount_invested": 0
        }


        if (AccountData.organization_id != null) {
            new_investment.organization_id = AccountData.organization_id.toString();
            deferred.resolve(new_investment);
        } else {
            loop = setInterval(function () {
                if (AccountData.organization_id != null) {
                    clearInterval(loop);
                    loop = null;
                    new_investment.organization_id = AccountData.organization_id.toString();
                    deferred.resolve(new_investment);
                }
            }, 50);
        }
        return deferred.promise;
    }

    this.remove = function(id) {
        for (var i = 0; i < data.investments.length; i ++) {
            if (id == data.investments[i].investment_id) {
                if (AccountData.permissions.view_removed_investments) {
                    data.investments[i].status = "REMOVED";
                } else {
                    data.investments.splice(i,1);
                }
                Storage.update("investments", data);
                break;
            }
        }
    }

    this.load_investments();
});
