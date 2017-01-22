dah.service("Orders", function($http, $q, Storage, Account) {

    this.data = {
        "orders": [],
		"investments": []
    }

    var data = this.data;
	var $this = this;

    this.get_order_history = function () {
        var deferred = $q.defer();
		Account.info().then(function(response) {
			console.log(response);
			if (response) {
				// First lets see if we still have the order history
		        var old_order_history;
		        if (data.orders.length) {

		            // If we still have the order history then we dont need to get it again
		            deferred.resolve(true);




		        // } else if (old_order_history = Storage.load("order_history")) {
				//
		        //     // If we still have the order history in local storage then we dont need to get it again
		        //     data.orders = old_order_history;
		        //     get_invested();
		        //     deferred.resolve(true);




		        } else {

		            $http.post("/api/account/get_order_history.php").then(function(response) {
						console.log(response);
		                if (response.data.status == "success") {
							$this.process_order_data(response.data.orders);
		                    // data.orders = response.data.orders;
		                    // for (var i = 0; i < data.orders.length; i++) {
		                    //     data.orders[i].order_id = pad_length(data.orders[i].order_id, 6);
		                    // }
		                    get_invested();
		                    Storage.save("order_history", data.orders, 2);
		                }
		                deferred.resolve(true);
		            });
		        }
			} else deferred.resolve(false);
		})

        return deferred.promise;
    }

	this.process_order_data = function (order_data) {

		var orders = [];
		for (var i in order_data) {
			var index = find_in_array(orders, order_data[i].order_id, 'order_id');
			if (index < 0) orders.push(order_data[i]);
			else orders[index].order_items.push(order_data[i].order_items[0]);
		}

		data.orders = orders;
	}

    this.get_invested = function () {

        // Getting all of the investments this account has invested in.
        data.investments = [];
        for (var i = 0; i < data.orders.length; i++) {
            for (var ii = 0; ii < data.orders[i].order_items.length; ii++) {
                if (data.orders[i].order_items[ii].type == "investment" && data.orders[i].order_status == "COMPLETED") {
                    var found = false;
                    for (var iii = 0; iii < data.investments.length; iii++) {
                        if (data.investments[iii].investment_id == data.orders[i].order_items[ii].investment_id) found = iii;
                    }
                    if (found !== false) data.investments[found].amount_paid += data.orders[i].order_items[ii].amount_paid;
                    else data.investments.push(JSON.parse(JSON.stringify(data.orders[i].order_items[ii])));
                }
            }
        }
    }

    var get_invested = this.get_invested;


})
