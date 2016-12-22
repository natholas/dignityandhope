dah.filter('limit', function() {
	return function(input, limit, key, value) {
		var out = [];
		for (var i in input) {
			if (limit && out.length >= limit) break;
			if (input[i][key] == value) out.push(input[i]);
		}
		return out;
	};
})
