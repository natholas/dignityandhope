var dah = angular.module('dah', ['ngRoute', 'naif.base64'])

.config(function($routeProvider)
{
    $routeProvider
    .when('/',
    {
        templateUrl: '/admin/assets/html/pages/dashboard.html',
        controller: "dashboardCtrl"
    })
    .when('/investments/:preset?',
    {
        templateUrl: '/admin/assets/html/pages/investments.html',
        controller: "investmentsCtrl",
        resolve: {
            investments: function(Investments) {
                return Investments.get_investments();
            }
        }
    })
    .when('/add_investment/',
    {
        templateUrl: '/admin/assets/html/pages/add_investment.html',
        controller: "add_investmentCtrl",
        resolve: {
            new_investment: function(Investments) {
                return Investments.new_investment();
            },
            organizations: function(Organizations) {
                return Organizations.get_organizations();
            }
        }
    })
    .when('/edit_investment/:investment_id',
    {
        templateUrl: '/admin/assets/html/pages/edit_investment.html',
        controller: "edit_investmentCtrl",
        resolve: {
            investments: function(Investments) {
                return Investments.get_investments();
            },
            organizations: function(Organizations) {
                return Organizations.get_organizations();
            }
        }
    })
    .when('/products/',
    {
        templateUrl: '/admin/assets/html/pages/products.html',
        controller: "productsCtrl"
    })
    .when('/add_product/',
    {
        templateUrl: '/admin/assets/html/pages/add_product.html',
        controller: "add_productCtrl",
        resolve: {
            new_product: function(Products) {
                return Products.new_product();
            },
            investments: function(Investments) {
                return Investments.get_investments();
            }
        }
    })
    .when('/edit_product/:product_id',
    {
        templateUrl: '/admin/assets/html/pages/edit_product.html',
        controller: "edit_productCtrl",
        resolve: {
            products: function(Products) {
                return Products.get_products();
            },
            investments: function(Investments) {
                return Investments.get_investments();
            }
        }
    })
    .when('/employees/',
    {
        templateUrl: '/admin/assets/html/pages/employees.html',
        controller: "employeesCtrl",
        resolve: {
            organizations: function(Organizations) {
                return Organizations.get_organizations();
            },
            employees: function(Employees) {
                return Employees.get_employees();
            }
        }
    })
    .when('/add_employee/',
    {
        templateUrl: '/admin/assets/html/pages/add_employee.html',
        controller: "add_employeeCtrl",
        resolve: {
            organizations: function(Organizations) {
                return Organizations.get_organizations();
            },
            employees: function(Employees) {
                return Employees.get_employees();
            }
        }
    })
    .when('/edit_employee/:user_id',
    {
        templateUrl: '/admin/assets/html/pages/edit_employee.html',
        controller: "edit_employeeCtrl",
        resolve: {
            organizations: function(Organizations) {
                return Organizations.get_organizations();
            },
            employees: function(Employees) {
                return Employees.get_employees();
            }
        }
    })
    .when('/organizations/',
    {
        templateUrl: '/admin/assets/html/pages/organizations.html',
        controller: "organizationsCtrl"
    })
    .when('/add_organization/',
    {
        templateUrl: '/admin/assets/html/pages/add_organization.html',
        controller: "add_organizationCtrl",
        resolve: {
            new_organization: function(Organizations) {
                return Organizations.new_organization();
            }
        }
    })
    .when('/edit_organization/:organization_id',
    {
        templateUrl: '/admin/assets/html/pages/edit_organization.html',
        controller: "edit_organizationCtrl",
        resolve: {
            organizations: function(Organizations) {
                return Organizations.get_organizations();
            }
        }
    })
    .when('/edit_account',
    {
        templateUrl: '/admin/assets/html/pages/edit_account.html',
        controller: "edit_accountCtrl",
        resolve: {
            organizations: function(Organizations) {
                return Organizations.get_organizations();
            }
        }
    })
    .when('/reporting',
    {
        templateUrl: '/admin/assets/html/pages/reporting.html',
        controller: "reportingCtrl"
    })
    .when('/emails',
    {
        templateUrl: '/admin/assets/html/pages/emails.html',
        controller: "emailCtrl"
    })
    .when('/add_email/:type?/:to?',
    {
        templateUrl: '/admin/assets/html/pages/add_email.html',
        controller: "add_emailCtrl",
        resolve: {
            new_email: function(Emails) {
                return Emails.new_email();
            }
        }
    })
    .when('/edit_email/:email_id',
    {
        templateUrl: '/admin/assets/html/pages/edit_email.html',
        controller: "edit_emailCtrl",
        resolve: {
            emails: function(Emails) {
                return Emails.get();
            }
        }
    })
    .when('/users',
    {
        templateUrl: '/admin/assets/html/pages/users.html',
        controller: "usersCtrl"
    })
    .when('/edit_user/:user_id',
    {
        templateUrl: '/admin/assets/html/pages/edit_user.html',
        controller: "edit_userCtrl"
    })

    .otherwise(
    {
        template: '<h2>Page not found!</h2>'
    });
})
