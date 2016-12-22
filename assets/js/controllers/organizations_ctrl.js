dah.controller("organizationsCtrl", function($scope, Organizations, organization) {

	if (organization) $scope.organizations = [organization];
	else $scope.organizations = Organizations;
});
