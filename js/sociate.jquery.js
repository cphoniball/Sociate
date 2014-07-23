// This has additions and changes from the regular version of social-buttons.jquery.js that make it compatible with smallbusiness.com
// Sociate base class
// Handles API calls and data manipulation
var Sociate = Sociate || function(url, postid) {

	// for development purposes, rewrites URL to that of live site
	function devUrl(url) {
		if (typeof url === 'string') {
			if (url.substring(0, 35) === 'http://localhost:8888/smallbusiness') {
				url = 'http://smallbusiness.com' + url.substring(35);
			} else if (url.substring(0, 28) === 'http://dev.smallbusiness.com') {
				url = 'http://smallbusiness.com' + url.substring(28);
			}
		}
		return url;
	}

	this.url = devUrl(url);
	this.postid = postid;
	this.internalEndpoint = Sociate_Ajax.ajaxUrl;

	/****************
	// SharedCount specific methods
	****************/

	// Makes the sharedcount call and returns the jqXHR object
	// Uses the jQuery plugin provided on sharedcount.com, as the service does not like the jQuery jsonp wrapper
	// Args:
	//    url: customUrl; if called internally, you may specify a different URL to get data for. Otherwise, use the url defined in the class declaration
	// Returns: a jqXHR object representing the request
	this.getSharedcount = function(customUrl, fn) {
		if (typeof customUrl === 'string') { var url = customUrl; }
		else { var url = this.url; }

		// begin sharedcount jquery plugin
		var domain = "//free.sharedcount.com/"; /* SET DOMAIN */
    var apikey = "9ef2fa799694361a5e98395b145714ccb80f6a5a"; /*API KEY HERE*/
    var arg = {
	    data: {
	    	url : url,
	    	apikey : apikey
	    },
        url: domain,
        cache: true,
        dataType: "json"
    };
    if ('withCredentials' in new XMLHttpRequest) {
        arg.success = fn;
    }
    else {
        var cb = "sc_" + url.replace(/\W/g, '');
        window[cb] = fn;
        arg.jsonpCallback = cb;
        arg.dataType += "p";
    }
    return jQuery.ajax(arg);

		// url = encodeURIComponent(devUrl(url) || location.href);

		// var arg = {
		// 	url: "//" + (location.protocol == "https:" ? "sharedcount.appspot" : "api.sharedcount") + ".com/?url=" + url,
		// 	cache: true,
		// 	dataType: "json"
		// };

		// if ('withCredentials' in new XMLHttpRequest) {
		// 	arg.success = fn;
		// } else {
		// 	var cb = "sc_" + url.replace(/\W/g, '');
		// 	window[cb] = fn;
		// 	arg.jsonpCallback = cb;
		// 	arg.dataType += "p";
		// }

		// return $.ajax(arg);
	};


	// Returns total share count for Facebook, Twitter, Pinterest, and LinkedIn
	// Args:
	//   data: Data returned by the request to sharedcount.com
	this.extractAllData = function(data) {
		return {
			'twitter': data.Twitter,
			'facebook': data.Facebook.total_count || 0,
			'pinterest': data.Pinterest,
			'google-plus': data.GooglePlusOne,
			'linkedin': data.LinkedIn
		};
	}

	// Returns share count for individual site, given sharedcount data
	// Args:
	//   site: name of site you'd like information for, one of 'facebook', 'pinterest', 'linkedin', 'google-plus', and 'twitter'
	//   data: response from sharedcount.com
	this.extractSiteData = function(site, data) {
		if (!site || !data) return false;

		if (site === 'facebook') {
			return data.Facebook.total_count;
		} else if (site === 'pinterest') {
			return data.Pinterest;
		} else if (site === 'linkedin') {
			return data.LinkedIn;
		} else if (site === 'google-plus') {
			return data.GooglePlusOne;
		} else if (site === 'twitter') {
			return data.Twitter;
		} else {
			return false;
		}
	};

	/****************
	// Direct calls to social service APIs
	****************/

	// Makes a request to get a social media share count from an individual site
	// This makes the request directly to the service and does not go through sharedcount.com
	// GooglePlus is not available, as they do not have a readily accessible http endpoint to get shared counts
	// Args:
	//   site: one of 'facebook', 'twitter', 'linkedin', or 'pinterest'
	//   url: url to get share count for
	this.getSiteCount = function(site) {
		if (site === 'facebook') {
			return $.ajax({
				url: 'http://graph.facebook.com/?id=' + url,
				dataType: 'jsonp'
			});
		} else if (site === 'twitter') {
			return $.ajax({
				url: 'http://cdn.api.twitter.com/1/urls/count.json?url=' + url,
				dataType: 'jsonp'
			});
		} else if (site === 'linkedin') {
			return $.ajax({
				url: 'http://www.linkedin.com/countserv/count/share?url=' + url,
				dataType: 'jsonp'
			});
		} else if (site === 'pinterest') {
			return $.ajax({
				url: 'http://api.pinterest.com/v1/urls/count.json?url=' + url,
				dataType: 'jsonp'
			});
		} else return false;
	};

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



}; // END Sociate

// Inherits Sociate as prototype and adds functions that deal with the social buttons component of the plugin
var SociateButtons = SociateButtons || function(url, postid, $sociate, options) {

	Sociate.apply(this, Array.prototype.slice.call(arguments));

	$.extend(this, {
		selector: '.sociate-button',
		url: $sociate.data('url'), // note that the URL must contain a trailing slash, or twitter will add a / to the end of the tweet
		title: $sociate.data('title'),
		postid: $sociate.data('postid'),
		summary: $sociate.data('summary'), //
		twitterAccount: $sociate.data('twitteraccount'), // will be used as the twitter .via
		targetBlank: true, // sets target = blank on all buttons
		internalEndpoint: Sociate_Ajax.ajaxUrl,
		insertCounts: false,
		updateInterval: 15 // time in minutes until the social count should be updated
	});

	this.$buttons = $(this.selector);
	this.$post = $('#post-' + this.postid);

	// if a featured photo has been set, use that as the image - otherwise get the first image in the post
	this.imageUrl = (function() {
		console.log($sociate.data('imageurl')); 
		if ($sociate.data('imageurl')) { return $sociate.data('imageurl'); }
		else {
			return $post.find('[class^="wp-image"]').first().attr('href');
		}
	})();

	// overwrite settings with custom options if needed
	$.extend(this, options);

	// Displays social count on buttons
	// Buttons must have the data attribute 'showcount' set to true
	// Additionally, you may optionally filter by selector 'filterSelector'
	// Args:
	//   showLoader: true if you want a spinning loader to appear while the function waits for ajax call to finish
	//   data: (optional) optionally you may pass in data to be used to show the counts. otherwise, a new call to sharedcount will be made
	//   filterSelector: (optional) filters buttons to only show the count on some of them
	//   callback: (optional) runs a callback after social counts have been added, passing in the count data to the function
	this.insertSocialCounts = function(data, showLoader, callback) {
		// insert loader if set to true
		if (showLoader) {
			$buttons.each(function(i, e) {
				$(this).append('<i class="icon-spinner icon-spin"></i>');
			});
		}

		var insertCounts = function(data) {
			$buttons.each(function(i, e) {
				var site = $(this).data('site');
				var count = data[site];
				if (showLoader) { $(this).find('.icon-spinner').remove(); }
				if (count !== '0') {
					$(this).find('.sociate-count').remove();
					$(this).append('<span class="sociate-count">' + count + '</span>');
				}
			});
		};

		// if data was not passed in
		if (!data) {
			this.getInternalData().done(function(data, status, xhr) {
				insertCounts(this.extractAllData(data));
			 	if (callback) callback(data);
			});
		} else { // use passed in data
			insertCounts(data);
			if (callback) callback(data);
		}
	}


	/****************
	// Update count methods
	****************/

	// Get internal count first - if that count is older than the settings.updateInterval, update the internal count
	this.checkSocialCount = function() {
		var instance = this;
		this.getInternalData().done(function(data) {
			if ((new Date().getTime() / 60000 ) - (data['updated'] / 60) > instance.updateInterval) {
					instance.getSharedcount().done(function(data) {
					instance.updateInternalData(instance.extractAllData(data));
				});
			}
		});
	};

	// Generates the correct http endpoint for each of the social sharing services
	// Args:
	//   site: social network to generate url for, one of 'facebook', 'twitter', 'google-plus' or 'linkedin'
	//   options: should be the same object that is passed in to $.fn.initSocialButtons, see that documention for options
	this.generateShareUrl = function(site) {
		if (site === 'facebook') {
			return 'http://www.facebook.com/sharer/sharer.php?u=' + this.url;
		} else if (site === 'pinterest') {
			var shareUrl = 'http://pinterest.com/pin/create/button/?url=' + this.url;
			if (typeof this.summary === 'string') {
				shareUrl += '&description=' + this.summary;
			}
			if (typeof this.imageUrl === 'string') {
				shareUrl += '&media=' + this.imageUrl;
			}
			return shareUrl;
		} else if (site === 'linkedin') {
			var shareUrl = 'http://www.linkedin.com/shareArticle?mini=true&url=' + this.url + '&source=' + this.url;
			if (typeof this.title === 'string') {
				shareUrl += '&title=' + this.title;
			}
			if (typeof this.summary === 'string') {
				shareUrl += '&summary=' + this.summary;
			}
			return shareUrl;
		} else if (site === 'google-plus') {
			return 'http://plus.google.com/share?url=' + this.url;
		} else if (site === 'twitter') {
			var shareUrl = 'https://twitter.com/intent/tweet?url=' + this.url;
			if (typeof this.title === 'string') {
				shareUrl += '&text=' + this.title;
			}
			if (typeof this.twitterAccount === 'string') {
				shareUrl += '&via=' + this.twitterAccount
			}
			return shareUrl;
		}
	}

	var init = function() {
		// set buttons to open in another window
		if (this.targetBlank) {
			this.$buttons.each(function(i, e) {
				$(this).attr('target', '_blank');
			});
		}

		if (typeof this.url !== 'undefined') {
			// for development purposes, so URLs update and retrieve real counts
			if (this.url.substring(0, 35) === 'http://localhost:8888/smallbusiness') {
				this.url = 'http://smallbusiness.com' + this.url.substring(35);
			} else if (this.url.substring(0, 28) === 'http://dev.smallbusiness.com') {
				this.url = 'http://smallbusiness.com' + this.url.substring(28);
			}
		}


		// Set URLs on each of the buttons to the proper share endpoint
		this.$buttons.each(function(i, e) {
			var site = $(e).data('site');
			var shareUrl = this.generateShareUrl(site);
			$(e).attr('href', shareUrl);
		}.bind(this));

		// Update counts from sharedcount
		this.checkSocialCount();

		// add counts if options set to
		if (this.insertCounts) {
			this.insertSocialCounts(data);
		}

		this.$buttons.attr('data-initialized', 'true');
	}.bind(this);

	init();

};

$(document).ready(function() {
	SociateButtons.prototype = new Sociate();	
}); 


// Custom event 'sociateButtons is fired whenever the buttons are included in the page - this will prevent the plugin from initializing below IE9'
document.addEventListener('sociateButtons', function() {
	$('.sociate-buttons[data-initialized="false"]').each(function() {
		var sociateButtons = new SociateButtons($(this).data('url'), $(this).data('postid'), $(this));
	});
});