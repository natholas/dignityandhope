dah.directive('lineChart', function(Reporting, EaM) {
    return {
        restrict: 'E',
        transclude: true,
        observe: true,
        replace: true,
        scope: {},
        templateUrl: '/admin/assets/html/elements/line-chart.html',
        link: function(scope, element, attrs) {


            var from = new Date().addDays(-30);
            var to = new Date();

            scope.from = {
                "d": from.getDate(),
                "m": from.getMonth() + 1,
                "y": from.getFullYear()
            }

            scope.to = {
                "d": to.getDate(),
                "m": to.getMonth() + 1,
                "y": to.getFullYear()
            }

            scope.points = ["","",""];
            scope.data = [0,0,0];

            scope.get_data = function() {

                var date_from = new Date(scope.from.y, scope.from.m - 1, scope.from.d)
                var timestamp_from = date_from.getTime() / 1000;

                var date_to = new Date(scope.to.y, scope.to.m - 1, scope.to.d)
                var timestamp_to = date_to.getTime() / 1000;

                if (date_from.addDays(1) < date_to) {

                    var date_diff = Math.floor((timestamp_to - timestamp_from)/(60*60*24));

                    scope.points = [];
                    for (var i = 1; i <= date_diff + 1; i++) {
                        var new_date = date_from.addDays(i);
                        new_date = new_date.getDate() + "/" + new_date.getMonth();
                        scope.points.push(new_date);
                    }

                    scope.data = [];

                    Reporting.get_data(date_from, date_to).then(function(response) {
                        scope.data = response.days;
                        scope.apply_data();
                    });
                } else {
                    EaM.showError("todatebeforefromdate")
                }

            }

            scope.get_data();


            scope.apply_data = function() {

                // Finding the minimum and maximum values in the data
                scope.max = 0;
                for (var i = 0; i < scope.data.length; i++) {
                    if (scope.data[i] > scope.max) {
                        scope.max = scope.data[i];
                    }
                }

                scope.min = 0;
                for (var i = 0; i < scope.data.length; i++) {
                    if (scope.data[i] < scope.min) {
                        scope.min = scope.data[i];
                    }
                }

                // Adding spacing on the top and bottom
                scope.max = scope.max * 1.1;
                if (scope.min < 0) {
                    scope.min = scope.min * 1.1;
                }

                // Creating the svg code for the chart
                scope.path = "M0 100 ";
                for (var i = 0; i < scope.points.length; i ++) {
                    scope.path += "L" + (300 / (scope.points.length - 1) * i) + " " + (100 - (scope.data[i] / scope.max * 100)) + " ";
                }
                scope.path += "L 300 100 Z";

                // Placing markers on the side
                scope.markers = [];
                var markerDiff = Math.floor((scope.max - scope.min) / 10);
                var markerCount = Math.floor(scope.max / markerDiff);

                for (var i = markerCount; i > -1; i--) {
                    scope.markers.push(markerDiff * i);
                }

            };
        }
    }
})

.directive('ngD', function() {
    return function(scope, element, attrs) {
        scope.$watch(attrs.ngD, function(value) {
            if (value && value[10] != "N") {
                element.attr('d', value);
            }
        });
    };
})
