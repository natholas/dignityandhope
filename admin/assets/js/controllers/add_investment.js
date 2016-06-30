dah.controller('add_investmentCtrl', function($scope, $http, organizations, AccountData, Investments, new_investment, EaM, Loading) {

    $scope.user = AccountData;
    $scope.investment = new_investment;
    $scope.organizations = organizations;


    $scope.calcAmount = function() {

        // Calculating the total from all of the money splits
        $scope.investment.amount_needed = 0;
        for (var i = 0; i < $scope.investment.money_split.length; i ++) {
            if (!isNaN($scope.investment.money_split[i].amount)) {
                $scope.investment.amount_needed += $scope.investment.money_split[i].amount*1;
            }
        }
    }

    $scope.addSplit = function() {

        // Adding a new money split
        $scope.investment.money_split.push({
            "name": "",
            "amount": 0
        });
    }

    $scope.removeSplit = function(id) {

        // Removing a money split
        $scope.investment.money_split.splice(id, 1);
        $scope.calcAmount();
    }

    $scope.removeNewImage = function(id) {

        // Removing one of the new images
        if ($scope.investment.new_images.length > 1) {
            $scope.investment.new_images.splice(id, 1);
        }
    }

    $scope.rotateImage = function(id) {

        // Rotating image
        if (!$scope.investment.new_images[id].settings.rotation) {
            $scope.investment.new_images[id].settings.rotation = 90;
        } else if ($scope.investment.new_images[id].settings.rotation == 270) {
            $scope.investment.new_images[id].settings.rotation = 0;
        } else {
            $scope.investment.new_images[id].settings.rotation += 90;
        }

    }

    $scope.changeImageAlignment = function(id) {

        var pos = $scope.investment.new_images[id].settings.position;

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

        $scope.investment.new_images[id].settings.position = new_pos;
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
            $scope.investment.new_images[id] = new_img;
            // Adding a new blank image (upload button)
            $scope.investment.new_images.push("");
            $scope.$apply();
        }

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

    $scope.saveInvestment = function() {

        // Uploading the new investment to the server
        var data = JSON.parse(JSON.stringify($scope.investment));

        for (var i = 0; i < data.new_images.length; i ++) {
            if (!data.new_images[i].data) {
                data.new_images.splice(i,1);
                i -= 1;
            }
        }

        var dob = new Date(data.dob.y, data.dob.m - 1, data.dob.d);
        data.dob = Math.floor(dob.getTime() / 1000);

        Loading.startLoading();
        $http.post("/admin/api/investments/add_investment.php", data).success(function(response, status) {
            Loading.stopLoading();
            console.log(response);
            if (response.status && response.status == "success") {
                EaM.showMessage("saved");
                $scope.investment.investment_id = response.investment_id;
                $scope.investment.images = response.images;
                $scope.investment.new_images = [];
                Investments.addInvestment($scope.investment);
                window.location.href = "#/edit_investment/" + $scope.investment.investment_id;
            } else {
                EaM.showError("error");
            }
        });
    }

    $scope.changeOrg = function() {

        // Changing the organization name of the investment
        for (var i = 0; i < $scope.organizations.length; i ++) {
            if ($scope.organizations[i].organization_id == $scope.investment.organization_id) {
                $scope.investment.organization = $scope.organizations[i].name;
                break;
            }
        }
    }

    // Focusing the first input
    document.getElementById("first_input").focus();

    // Adding an image split
    $scope.addSplit();


});
