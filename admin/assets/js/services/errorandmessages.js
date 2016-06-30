dah.service('EaM',function($timeout) {

    var currentError,
    currentMessage,
    timeout;

    this.data = {
        message: null,
        error: null
    };

    var data = this.data;

    this.showError = function(name) {

        if (name == "error") {
            data.error = "Error";
            timeout = 3000;
        } else if (name == "wronguserorpass") {
            data.error = "Wrong username or password. Please try again";
            timeout = 5000;
        } else if (name == "emailnotfound") {
            data.error = "No users with this email address were found";
            timeout = 5000;
        } else if (name == "passwordsdontmatch") {
            data.error = "The passwords you entered don't match";
            timeout = 4000;
        } else if (name == "wrongcode") {
            data.error = "Wrong code";
            timeout = 4000;
        } else if (name == "todatebeforefromdate") {
            data.error = '"To" date must be later than "from" date';
            timeout = 4000;
        }


        $timeout.cancel(currentError);

        currentError = $timeout(function () {
            data.error = null;
        }, timeout);

    }

    this.showMessage = function(name) {

        if (!timeout) {
            timeout = 3000;
        }

        if (name == "saved") {
            data.message = "Saved";
            timeout = 3000;
        } else if (name == "sent") {
            data.message = "Sent";
            timeout = 3000;
        } else if (name == "deleted") {
            data.message = "Deleted";
            timeout = 3000;
        } else if (name == "loggedin") {
            data.message = "Login success";
            timeout = 3000;
        } else if (name == "success") {
            data.message = "Success";
            timeout = 3000;
        }

        $timeout.cancel(currentMessage);

        currentMessage = $timeout(function () {
            data.message = null;
        }, timeout);

    }

});
