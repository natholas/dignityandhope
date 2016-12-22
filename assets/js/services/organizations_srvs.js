dah.service('Organizations', function($http, $q, $interval) {

	this.data = {"organizations":[]};
	var data = this.data;

	this.get_one = function (organization_id) {
		var deferred = $q.defer();

		var get_one_timer = $interval(function() {
			if (data.organizations.length) {
				$interval.cancel(get_one_timer);
				var index = find_in_array(data.organizations, organization_id, 'organization_id');
				if (index >= 0) deferred.resolve(data.organizations[organization_id]);
				else deferred.resolve();
			}
		},50)


		return deferred.promise;
	}

	this.get_organizations = function () {
		$http.post("/api/organizations/get_organizations.php").then(function(response) {
			data.organizations = response.data.organizations;
		})
	}

	this.get_organizations();

});
