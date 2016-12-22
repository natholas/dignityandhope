var dah = angular.module('dah', ['ngRoute'])

.config(function($routeProvider)
{
    $routeProvider
    .when('/',
    {
        templateUrl: '/assets/html/pages/home_page.html',
        controller: "investmentsCtrl",
		resolve: {
            orders: function(Orders) {
                return Orders.get_order_history().then(function(response) {
					console.log(1);
					return response;
				})
            },
            investments: function(Investments) {
                return Investments.get(0,18).then(function(response) {
					console.log(2);
					return response;
				});
            }
        }
    })
    .when('/investments',
    {
        templateUrl: '/assets/html/pages/investments_page.html',
        controller: "investmentsCtrl",
        resolve: {
            orders: function(Orders) {
                return Orders.get_order_history().then(function(response) {
					return response;
				})
            },
            investments: function(Investments) {
                return Investments.get(0,18);
            }
        }
    })
    .when('/organizations/:organization_id?',
    {
        templateUrl: '/assets/html/pages/organizations.html',
        controller: "organizationsCtrl",
        resolve: {
            organization: function(Organizations, $route) {
				if ($route.current.params.organization_id) {
	                return Organizations.get_one($route.current.params.organization_id).then(function(response) {
						return response;
					})
				} else {
					return false;
				}
            }
        }
    })
    .when('/all_investments',
    {
        templateUrl: '/assets/html/pages/all_investments_page.html',
        controller: "investmentsCtrl",
        resolve: {
			orders: function(Orders) {
                return Orders.get_order_history().then(function(response) {
					return response;
				})
            },
            investments: function(Investments) {
                return Investments.get(0,18);
            }
        }
    })
    .when('/success_stories',
    {
        templateUrl: '/assets/html/pages/ended_investments_page.html',
        controller: "investmentsCtrl",
        resolve: {
			orders: function(Orders) {
                return Orders.get_order_history().then(function(response) {
					return response;
				})
            },
            investments: function(Investments) {
                return Investments.get(0,18);
            }
        }
    })
    .when('/investment/:investment_id',
    {
        templateUrl: '/assets/html/pages/investment_page.html',
        controller: "investmentCtrl",
        resolve: {
			orders: function(Orders) {
                return Orders.get_order_history().then(function(response) {
					return response;
				})
            },
            investment: function(Investments, $route) {
                return Investments.get_one($route.current.params.investment_id);
            }
        }
    })
    .when('/checkout/:status?',
    {
        templateUrl: '/assets/html/pages/checkout_page.html',
        controller: "CheckoutCtrl"
    })
    .when('/confirmation/:order_id',
    {
        templateUrl: '/assets/html/pages/confirmation_page.html',
        controller: "ConfirmationCtrl"
    })
    .when('/account/',
    {
        templateUrl: '/assets/html/pages/account_page.html',
        controller: "AccountCtrl",
        resolve: {
			orders: function(Orders) {
                return Orders.get_order_history().then(function(response) {
					return response;
				})
            }
        }
    })
    .when('/memberships/',
    {
        templateUrl: '/assets/html/pages/memberships.html',
        controller: "membershipsCtrl"
    })

    .otherwise(
    {
        template: '<space-limiter><h2>Page not found!</h2></space-limiter>'
    });
})
