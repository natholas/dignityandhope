dah.service("Notifications", function() {

	this.data = {
		"notifications":[]
	}
	var data = this.data;

	this.add = function (text, type) {
		data.notifications.push({
			"text": text,
			"type": type
		})
	}

})
