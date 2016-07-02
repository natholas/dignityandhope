dah.service('Confirm', function($q) {

    this.data = {
        "text": null
    };
    var data = this.data;

    this.get_confirmation = function(text) {
        var deferred = $q.defer();

        data.text = text;

        var loop = setInterval(function () {
            console.log("loop");
            if (data.confirmed) {
                deferred.resolve(true);
                data.confirmed = false;
                data.text = null;
                clearInterval(loop);
            } else if (data.cancelled) {
                data.cancelled = false;
                deferred.resolve(false);
                data.text = null;
                clearInterval(loop);
            }

        }, 100);

        return deferred.promise;
    }

});
