dah.service('Admin_messages', function($http, Storage) {

    this.data = {};
    var data = this.data;

    this.get_messages = function() {
        $http.post("/admin/api/messages/get_messages.php").success(function(response, status) {
            console.log(response);
            if (response && response.status == "success") {
                data.messages = response.messages;
            }
        });
    }

    this.add_message = function(new_data) {
        this.data.messages.unshift(new_data);

    }

    this.get_messages();
});
