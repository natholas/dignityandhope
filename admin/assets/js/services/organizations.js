dah.service('Organizations', function($http, Storage, $q, AccountData) {

    this.data = {};
    var data = this.data;

    this.load_organizations = function() {
        // Checking to see if the organizations are already saved in the localstorage
        var new_data = Storage.load("organizations");
        if (new_data) {

            // The organizations were saved in localstorage
            data.organizations = new_data.organizations;

        } else {

            // The organizations are not in localstorage or not valid anymore
            // Getting the organizations from API
            $http.post("/admin/api/user_org_manage/get_organizations.php").success(function(response, status) {

                if (response && response.status == "success") {

                    data.organizations = response.organizations;

                    // Saving organizations for the next day
                    Storage.save("organizations", data, 24);

                }
            });
        }
    }

    this.get_organizations = function() {
        var deferred = $q.defer();
        var loop = null;

        if (data.organizations) {
            deferred.resolve(data.organizations);
        } else {
            loop = setInterval(function () {
                if (data.organizations) {
                    clearInterval(loop);
                    loop = null;
                    deferred.resolve(data.organizations);
                }
            }, 50);
        }

        return deferred.promise;
    }

    this.getOrganizationIndex = function(organization_id) {
        for (var i = 0; i < data.organizations.length; i ++) {
            if (data.organizations[i].organization_id == organization_id) {
                return i;
            }
        }
    }

    this.updateOrganization = function(new_data) {

        // Looping through the list of all the current organizations
        for (var i = 0; i < data.organizations.length; i ++) {

            if (new_data.organization_id == data.organizations[i].organization_id) {
                // Updating organization
                data.organizations[i] = new_data;
                Storage.update("organizations", data);
                break;
            }
        }
    }

    this.getOrganizationIndex = function(organization_id) {
        for (var i = 0; i < data.organizations.length; i ++) {
            if (data.organizations[i].organization_id == organization_id) {
                return i;
            }
        }
    }

    this.addOrganization = function(new_data) {
        // Adding organization
        data.organizations.push(new_data);
        // Updating the localstorage
        Storage.update("organizations", data);
    }

    this.new_organization = function() {
        var deferred = $q.defer();

        var new_organization = {
            "status": "DRAFT",
            "money_split": [],
            "new_images": [""],
            "images": [],
            "amount_needed": 0,
            "amount_invested": 0
        }


        if (AccountData.organization_id != null) {
            new_organization.organization_id = AccountData.organization_id.toString();
            deferred.resolve(new_organization);
        } else {
            loop = setInterval(function () {
                if (AccountData.organization_id != null) {
                    clearInterval(loop);
                    loop = null;
                    new_organization.organization_id = AccountData.organization_id.toString();
                    deferred.resolve(new_organization);
                }
            }, 50);
        }
        return deferred.promise;
    }

    this.remove = function(id) {
        for (var i = 0; i < data.organizations.length; i ++) {
            if (id == data.organizations[i].organization_id) {
                if (AccountData.permissions.view_removed_organizations) {
                    data.organizations[i].status = "REMOVED";
                } else {
                    data.organizations.splice(i,1);
                }
                Storage.update("organizations", data);
            }
        }
    }

    this.load_organizations();
});
