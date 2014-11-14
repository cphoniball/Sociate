// Handles admin-side functionality for sociate

var SociateAdmin = function() {

	this.updateAllPosts = function(finished) {
		var instance = this;
		$.ajax({
			url: this.internalEndpoint,
			data: {
				action: 'get_all_posts'
			},
			method: 'POST'
		}).done(function(data, status, xhr) {
			var data = JSON.parse(data);
			var startTime = new Date().getTime();
			var response = {
				updated: 0,
				errors: 0,
				timeElapsed: 0
			};

			// create an async counter to figure out when all posts have been updated
			function createAsyncCounter(count) {
				function finished() {
					response.timeElapsed = (new Date().getTime() - startTime) / 1000;
					callback(response);
				}
				count = count || 1;
				return function() { --count || finished(); };
			}

			var countDown = createAsyncCounter(data.length);

			var postUpdated = new CustomEvent('sociatePostUpdated', {'detail': response});

			// data will be an array of json objects, each consisting of { postid: id, posturl: http://something.com/ }
			data.forEach(function(e, i) {
				// instantiate a socialButtons object for each post
				instance.getSharedcount(e['posturl']).done(function(data) {
					instance.updateInternalData(instance.extractAllData(data), e['postid']).done(function(data) {
						response.updated++;
					}).always(function(data) {
						document.dispatchEvent(postUpdated);
						countDown();
					}).fail(function() {
						reponse.errors++;
					});
				});
			});
		});
	};
}

