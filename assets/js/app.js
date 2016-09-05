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
            "investments": function(Investments) {
                return Investments.get(0,20);
            }
        }
    })
    .when('/investment/:investment_id',
    {
        templateUrl: '/assets/html/pages/investment_page.html',
        controller: "investmentCtrl",
        resolve: {
            "investment": function(Investments, $route) {
                return Investments.get_one($route.current.params.investment_id);
            }
        }
    })
    .when('/checkout/',
    {
        templateUrl: '/assets/html/pages/checkout_page.html',
        controller: "CheckoutCtrl"
    })

    .otherwise(
    {
        template: '<h2>Page not found!</h2>'
    });
})
