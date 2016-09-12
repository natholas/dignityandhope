var dah = angular.module('dah', ['ngRoute'])

.config(function($routeProvider)
{
    $routeProvider
    .when('/',
    {
        templateUrl: '/assets/html/pages/home_page.html',
        controller: "homeCtrl"
    })
    .when('/investments',
    {
        templateUrl: '/assets/html/pages/investments_page.html',
        controller: "investmentsCtrl",
        resolve: {
            orders: function(Orders) {
                return Orders.get_order_history();
            },
            investments: function(Investments) {
                return Investments.get(0,18);
            }
        }
    })
    .when('/all_investments',
    {
        templateUrl: '/assets/html/pages/all_investments_page.html',
        controller: "investmentsCtrl",
        resolve: {
            orders: function(Orders) {
                return Orders.get_order_history();
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
                return Orders.get_order_history();
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
                return Orders.get_order_history();
            },
            investment: function(Investments, $route) {
                return Investments.get_one($route.current.params.investment_id);
            }
        }
    })
    .when('/checkout/',
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
                return Orders.get_order_history();
            }
        }
    })

    .otherwise(
    {
        template: '<space-limiter><h2>Page not found!</h2></space-limiter>'
    });
})
