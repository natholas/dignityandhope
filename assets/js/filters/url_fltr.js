dah.filter('urlify', function() {
	return function(input) {
		return encodeURIComponent(input);
	};
})
