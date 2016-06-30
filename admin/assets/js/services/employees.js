dah.service('Employees', function($http, Storage, $q, AccountData) {

    this.data = {};
    var data = this.data;

    this.load_employees = function() {
        // Checking to see if the employees are already saved in the localstorage
        var new_data = Storage.load("employees");
        if (new_data) {

            // The employees were saved in localstorage
            data.employees = new_data.employees;

        } else {

            // The employees are not in localstorage or not valid anymore
            // Getting the employees from API
            $http.post("/admin/api/user_org_manage/get_users.php").success(function(response, status) {

                if (response && response.status == "success") {

                    data.employees = response.users;
                    // Saving employees for the next 4 hours
                    Storage.save("employees", data, 4);

                }
            });
        }
    }

    this.get_employee_details = function(id) {
        var deferred = $q.defer();
        var data = {user_id: id};
        $http.post("/admin/api/user_org_manage/get_user_details.php", data).success(function(response, status) {
            if (response && response.status == "success") {
                response.user.organization_id = response.user.organization_id.toString();
                deferred.resolve(response.user);
            }
        });
        return deferred.promise;
    }

    this.get_employees = function() {
        var deferred = $q.defer();
        var loop = null;

        if (data.employees) {
            deferred.resolve(data.employees);
        } else {
            loop = setInterval(function () {
                if (data.employees) {
                    clearInterval(loop);
                    loop = null;
                    deferred.resolve(data.employees);
                }
            }, 50);
        }

        return deferred.promise;
    }

    this.getEmployeeIndex = function(user_id) {
        for (var i = 0; i < data.employees.length; i ++) {
            if (data.employees[i].user_id == user_id) {
                return i;
            }
        }
    }

    this.updateEmployee = function(new_data) {

        // Looping through the list of all the current employees
        for (var i = 0; i < data.employees.length; i ++) {

            if (new_data.user_id == data.employees[i].user_id) {
                // Updating user
                data.employees[i] = new_data;
                Storage.update("employees", data);
                break;
            }
        }
    }

    this.remove = function(id) {
        for (var i = 0; i < data.employees.length; i ++) {
            if (id == data.employees[i].user_id) {
                if (AccountData.permissions.remove_admin_user) {
                    data.employees.splice(i,1);
                }
                Storage.update("employees", data);
                break;
            }
        }
    }

    this.load_employees();
});
