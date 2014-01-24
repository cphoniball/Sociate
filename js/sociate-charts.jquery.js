/*
 * Handles the chart functionality for the Sociate backend

*/


	// Load the Visualization API library and the piechart library.
	google.load('visualization', '1.0', { 'packages': ['corechart'] } );
	google.setOnLoadCallback(drawAreaChart);

	function drawAreaChart() {
		$.ajax({
			url: Sociate_Ajax.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: {
				action: 'table_get_post_data',
				postid: '6760'
			}
		}).done(function(data, status, xhr) {
			console.log(data);

			var sifted = [['Time', 'Total']];

			data.forEach(function(e, i) {
				sifted.push([e.time, parseInt(e.total)]);
			});

			// Google stuff here
			var input = new google.visualization.arrayToDataTable(sifted);

			var options = {
				'title': 'Post social media totals over time',
				'width': 600,
				'height': 300,
				'hAxis': { 'title': 'Timestamp' }
			};

			var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
			chart.draw(input, options);
		});
	}

	function drawChart() {
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Topping');
		data.addColumn('number', 'Slices');

		data.addRows([
			['Mushrooms', 3],
			['Onions', 1],
			['Olives', 1],
			['Zucchini', 1],
			['Pepperoni', 2]
		]);

		var options = { 'title': 'How Much Pizza I Ate Last Night', 'width': 400, 'height': 300 };

		var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
		chart.draw(data, options);
	}