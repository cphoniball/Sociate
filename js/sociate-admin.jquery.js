$(document).ready(function() {

	// admin functionality here
	if ($('.sociate-menu-wrap').length) {

		// Mass refresh
		$('#refresh-all-social-scores').click(function() {
			var $button = $(this);
			var $loadingMessage = $('.replacing-scores');
			var confirmMessage = "Are you sure you wish to refresh all social scores? This is a costly operation that may take several minutes to finish running. Once you begin the operation, do not refresh or close out the page.";

			if (window.confirm(confirmMessage)) {
				//show loading message
				$button.addClass('hidden');
				$loadingMessage.removeClass('hidden');

				var sociate = new Sociate();

				var progress = {
					updated: 0,
					errors: 0
				};

				document.addEventListener('sociatePostUpdated', function(e) {
					console.log(e);
					$loadingMessage.text('Update in progress, ' + e.detail.updated + ' posts have been updated, ' + e.detail.errors + ' posts have returned an error.');
				});

				sociate.updateAllPosts(function() {
					$loadingMessage.text('Update finished. ' + response.updated + ' posts were updated in ' + response.timeElapsed + ' seconds, with ' + response.errors + ' errors. Please refresh the page to see results.');
				});
			}
		});

		// Individual refresh
		$('.refresh-post-social').each(function() {
			var $row = $(this).parents('tr');

			$(this).click(function() {
				var postid = $(this).data('postid');
				var url = $(this).data('url');
				var $text = $(this).find('.text');
				var $loader = $(this).find('img');

				$text.addClass('hidden');
				$loader.removeClass('hidden');

				var social = new Sociate(url, postid);

				social.getSharedcount().done(function(data) {
					social.updateInternalData(social.extractAllData(data)).done(function(data) {
						$loader.addClass('hidden');
						$text.text('Social data refreshed!').removeClass('hidden');
						// update rows to reflect new data
						var columns = ['total', 'trending', 'facebook', 'twitter', 'google-plus', 'linkedin', 'pinterest'];
						columns.forEach(function(e, i) {
							$row.find('.' + e).text(data[e]);
						});
					}).fail(function() {
						$loader.addClass('hidden');
						$text.text('Error').removeClass('hidden');
					});
				});
			});
		});

	}
});