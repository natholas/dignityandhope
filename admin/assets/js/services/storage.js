dah.factory('Storage', function() {
    return {
        save: function(name, data, hours_valid) {

            // This function saves data to the localstorage
            // We use try here because some browsers don't support local storage. In this case we just return null
            try {

                var data_to_save = {};
                data_to_save.saved_data = data;

                // See if we need to set an expiry date
                if (hours_valid) {

                    // We need to calculate when this item will expire
                    var now = new Date();
                    data_to_save.best_before = now.addHours(hours_valid).getTime() / 1000;

                }

                // Now we can save the item
                localStorage.setItem(name, JSON.stringify(data_to_save));

            }
            catch(error) {
                console.error(error);
            }
        },

        update: function(name, data) {

            // This function updates a localstorage item without changing the expiry
            try {
                data_to_save = {};

                // Adding the new data
                data_to_save.saved_data = data;

                // Getting the old best before date
                data_to_save.best_before = JSON.parse(localStorage.getItem(name)).best_before;

                // Now we can re-save the item
                localStorage.setItem(name, JSON.stringify(data_to_save));

            }
            catch(error) {
                console.error(error);
            }

        },

        load: function(name) {

            // This function returns data that was saved to localStorage
            // We use try here because some browsers don't support local storage. In this case we just return null
            try {

                // First we check to make sure that this item exists
                if (localStorage.getItem(name)) {

                    var raw_data = JSON.parse(localStorage.getItem(name));

                    // Each item stored contains a best_before item. We check this to make sure that the data is still valid
                    if (!raw_data.best_before || raw_data.best_before > new Date().getTime() / 1000) {

                        // This data is still good. Lets return the data

                        return raw_data.saved_data;

                    } else {

                        // This data is no longer valid
                        // In an effort to keep the localstorage clean, lets remove this item.
                        localStorage.removeItem(name);

                    }
                }
            }
            catch(error) {
                console.error(error);
            }

            // This data either doesnt exist or is too old. Lets return null
            return null;
        },

        remove: function(name) {

            // Nom nom nom
            try {
                localStorage.removeItem(name);
            }
            catch(error) {
                console.error(error);
            }
        },

        removeall: function() {
            try {
                localStorage.clear();
            }
            catch(error) {
                console.error(error);
            }
        }
    }
});
