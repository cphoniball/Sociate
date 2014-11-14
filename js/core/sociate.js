// Sociate module
// Handles actually retrieving data from various social networks
function Sociate(url) {
	
}


var Sociate = function (url) {	

		var instance = this; 

		this.url = url ||  window.location.protocol + "//" + window.location.hostname + "/" + window.location.pathname; 
		this.ajaxUrl = Sociate_Ajax.ajaxUrl;
		this.socialCounts = { 'facebook': null,  'google-plus': null,  'linkedin': null,  'pinterest': null,  'twitter': null }; 

		/****************
		// Retrieve settings
		****************/		

		function getWPSettings() {

		}

		/****************
		// Direct calls to social service APIs
		****************/

		function getFacebookCount() {
			return $.ajax({
				url: insertUrl('https://api.facebook.com/method/links.getStats?urls=%%URL%%&format=json')
				type: 'GET', 
				dataType: 'jsonp' 
			}).done(function(data) {
				this.socialCounts.facebook = data; 
			}); 
		}

		function getTwitterCount() {
			return $.ajax({
				url: insertUrl('http://urls.api.twitter.com/1/urls/count.json?url=%%URL%%&callback=twttr.receiveCount')
				type: 'GET', 
				dataType: 'jsonp' 
			}).done(function(data) {
				this.socialCounts.twitter = data; 
			}); 
		}

		function getLinkedinCount() {
			return $.ajax({
				url: insertUrl('http://www.linkedin.com/cws/share-count?url=%%URL%%')
				type: 'GET', 
				dataType: 'jsonp' 
			}).done(function(data) {
				this.socialCounts.linkedin = data; 
			}); 
		}

		function getPinterestCount() {
			return $.ajax({
				url: insertUrl('http://widgets.pinterest.com/v1/urls/count.json?source=6&url=%%URL%%')
				type: 'GET', 
				dataType: 'jsonp' 
			}).done(function(data) {
				this.socialCounts.pinterest = data; 
			}); 
		}

		function getGooglePlusCount() {

		}
		
		//*************************
		// Internal CRUD operations
		//*************************

		this.getInternalData = function() {
			return $.ajax({
				url: this.internalEndpoint,
				dataType: 'json',
				method: 'POST',
				data: {
					postid: this.postid,
					action: 'get_social'
				}
			});
		};

		// pass in social data from sharedcount
		this.updateInternalData = function(socialData, postid) {
			var data = $.extend(socialData, {
				action: 'update_social',
				postid: this.postid
			});

			if (typeof postid !== 'undefined') { data.postid = postid; }
			return $.ajax({
				url: this.internalEndpoint,
				dataType: 'json',
				method: 'POST',
				action: 'update_social',
				data: data
			});
		};

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

		// Generic helper functions

		function insertUrl(querystring) {
			return querystring.replace('URL', this.url); 
		}
}; 