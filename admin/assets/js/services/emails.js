dah.service('Emails', function($http, Storage, $q, AccountData) {

    this.data = {
        "emails": []
    };
    var data = this.data;

    data.settings = {
        limit: "10",
        offset: 0,
        order_by: "email_id DESC",
        pages_loaded: 0,
        filter: {
            organization_id: "any",
            drafts: true,
            pending: true,
            sent: false,
            search: ""
        }
    }

    this.clearFilter = function() {
        data.settings.filter = {
            organization_id: "any",
            drafts: true,
            pending: true,
            sent: false,
            search: ""
        }
    }

    this.get = function() {
        var deferred = $q.defer();

        if (data.emails.length) {
            deferred.resolve(data.emails);
        } else {
            var loop = setInterval(function () {
                if (data.emails.length) {
                    deferred.resolve(data);
                    clearInterval(loop);
                }
             }, 50);
        }

        return deferred.promise;
    }

    this.load_emails = function(ignore_cache, autoLoad) {
        // The emails are not in localstorage or not valid anymore
        // Getting the emails from API
        var settings = {
            "limit": data.settings.limit,
            "offset": data.settings.offset,
            "order_by": data.settings.order_by,
            "filter": JSON.stringify(data.settings.filter)
        }

        if (!autoLoad) {
            settings.getcount = 1;
        }

        $http.post("/admin/api/emails/get_emails.php", settings).success(function(response, status) {

            if (response && response.status == "success") {
                if (autoLoad) {
                    data.emails = data.emails.concat(response.emails);
                    data.autoLoading = false;
                } else {
                    data.emails = response.emails;
                    if (!autoLoad) {
                        data.count = response.count;
                    }
                }

            }
        });
    }

    this.load_email = function(email_id) {
        var deferred = $q.defer();
        // We are looking for a specific email
        for (var i = 0; i < data.emails.length; i++) {
            if (data.emails[i].email_id == email_id) {
                deferred.resolve(data.emails[i]);
                return deferred.promise;
                break;
            }
        }

        // Didn't find it. Lets get it from the API
        var settings = {"email_id": email_id};
        $http.post("/admin/api/emails/get_email.php", settings).success(function(response, status) {
            console.log(response);
            if (response.status == "success") {
                deferred.resolve(response.email);
            } else {
                deferred.resolve({});
            }
        });

        return deferred.promise;
    }

    this.new_email = function() {
        return {
            "status": "DRAFT"
        }
    }

    this.load_emails();

});
