dah.controller('edit_productCtrl', function($scope, $http, AccountData, Products, investments, Investments, $routeParams, EaM, Loading, $timeout, Confirm) {

    $scope.products = Products.data.products;
    $scope.investments = investments;
    $scope.user = AccountData;

    $scope.suggested_investments = [];

    // Getting the index for this product
    if ($routeParams.product_id) {

        Products.findProductFromProductId($routeParams.product_id).then(function(response) {
            $scope.product = response;
            $scope.product.remove_images = [];
            $scope.product.new_images = [];
            $scope.product.new_images.push("");
            $scope.pick_investment($scope.product.creator_id, 0, true);
        });
    }

    $scope.removeImage = function(id) {
        if ($scope.product.images.length + $scope.product.new_images.length > 1) {
            $scope.product.remove_images.push($scope.product.images[id]);
            $scope.product.images.splice(id, 1);
        }
    }

    $scope.removeNewImage = function(id) {
        if ($scope.product.images.length + $scope.product.new_images.length > 1) {
            $scope.product.new_images.splice(id, 1);
        }
    }

    $scope.applyImage = function(id, data) {

        // Resizing the chosen image and saving it as base64
        var img = new Image;

        img.src = "data:image/jpeg;base64," + data;
        var new_img = {
            "id": id,
            "data": "",
            "settings": {}
        };
        img.onload = function() {

            // Determining the dimentions to resize the image to
            var dimensions = $scope.calcWidthHeight(img.width, img.height, 800);
            // Resizingt eh image
            new_img.data = imageToDataUri(img, dimensions.width, dimensions.height);
            // Applying the new image
            $scope.product.new_images[id] = new_img;
            // Adding a new blank image (upload button)
            $scope.product.new_images.push("");
            $scope.$apply();
        }
    }

    $scope.rotateImage = function(id, type) {

        // Rotating image
        if (type == "old") {
            if (!$scope.product.images[id].settings.rotation) {
                $scope.product.images[id].settings.rotation = 90;
            } else if ($scope.product.images[id].settings.rotation == 270) {
                $scope.product.images[id].settings.rotation = 0;
            } else {
                $scope.product.images[id].settings.rotation += 90;
            }
        } else {
            if (!$scope.product.new_images[id].settings.rotation) {
                $scope.product.new_images[id].settings.rotation = 90;
            } else if ($scope.product.new_images[id].settings.rotation == 270) {
                $scope.product.new_images[id].settings.rotation = 0;
            } else {
                $scope.product.new_images[id].settings.rotation += 90;
            }
        }

    }

    $scope.changeImageAlignment = function(id, type) {

        if (type == "old") {
            var pos = $scope.product.images[id].settings.position;
        } else {
            var pos = $scope.product.new_images[id].settings.position;
        }

        var new_pos = "";
        if (pos == "center" || !pos) {
            new_pos = "top right";
        } else if (pos == "top right") {
            new_pos = "center right";
        } else if (pos == "center right") {
            new_pos = "bottom right";
        } else if (pos == "bottom right") {
            new_pos = "bottom center";
        } else if (pos == "bottom center") {
            new_pos = "bottom left";
        } else if (pos == "bottom left") {
            new_pos = "center left";
        } else if (pos == "center left") {
            new_pos = "top left";
        } else if (pos == "top left") {
            new_pos = "top center";
        } else if (pos == "top center") {
            new_pos = "center";
        }

        if (type == "old") {
            $scope.product.images[id].settings.position = new_pos;
        } else {
            $scope.product.new_images[id].settings.position = new_pos;
        }
        console.log(new_pos)
    }

    $scope.calcWidthHeight = function(width, height, max) {

        // Calculating the width and height of an image
        var output = {};
        var aspectRatio = width / height;

        if (aspectRatio < 0) {
            aspectRatio = -aspectRatio;
        }

        if (width < height) {
            output.width = max;
            output.height = max * aspectRatio;
        } else {
            output.height = max;
            output.width = max * aspectRatio;
        }

        return output;
    }

    $scope.updateProduct = function() {

        if (!$scope.product.creator_id) {
            $scope.edit_product.investment_search_term.$setValidity("required", false);
            return false;
        }

        var data = JSON.parse(JSON.stringify($scope.product));

        for (var i = 0; i < data.new_images.length; i ++) {
            if (!data.new_images[i].data) {
                data.new_images.splice(i,1);
                i -= 1;
            }
        }

        Loading.startLoading();
        $http.post("/admin/api/products/edit_product.php", data).success(function(response, status) {
            Loading.stopLoading();
            console.log(data);
            if (response.status && response.status == "success") {
                EaM.showMessage("saved");
                $scope.product.images = response.images;
                $scope.product.new_images = [];
                Products.updateProduct($scope.product);
                $scope.product.new_images.push("");
            } else {
                EaM.showError("error");
            }
        });
    }


    $scope.pick_investment = function(investment_id, wait, first_time) {

        $timeout(function() {
            if (!$scope.product.creator_id || first_time) {

                if (investment_id == null && $scope.suggested_investments.length) {
                    investment_id = $scope.suggested_investments[0].investment_id;
                }
                $scope.suggested_investments = [];

                if (investment_id != null) {
                    Investments.findInvestmentFromInvestmentId(investment_id).then(function(response) {
                        $scope.product.creator_id = response.investment_id;
                        $scope.investment_search_term = response.name;
                         $scope.product.creator = response.name;
                    });
                }
            }
        }, wait);
    }

    $scope.find_investments = function() {
        $scope.product.creator_id = null;
        if ($scope.investment_search_term && $scope.investment_search_term.length > 2) {
            Investments.findInvestmentsFromString($scope.investment_search_term).then(function(response) {
                $scope.suggested_investments = response;
            })
        }
    }

    $scope.removeProduct = function() {

        Confirm.get_confirmation("Remove product").then(function(response) {
            console.log(response);
            if (response) {
                var data = {
                    "product_id": $scope.product.product_id
                }

                Loading.startLoading();
                $http.post("/admin/api/products/remove_product.php", data).success(function(response, status) {
                    Loading.stopLoading();
                    if (response.status && response.status == "success") {
                        EaM.showMessage("deleted");
                        Products.remove($scope.product.product_id);
                        window.location.href="#/products";
                    } else {
                        EaM.showError("error");
                    }
                });
            }
        })
    }

});
