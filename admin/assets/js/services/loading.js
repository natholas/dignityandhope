dah.service('Loading', function($rootScope) {

    this.startLoading = function() {
        $rootScope.waitingForServer = true;
    }

    this.stopLoading = function() {
        $rootScope.waitingForServer = false;
    }

});
