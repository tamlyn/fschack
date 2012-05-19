/**
 * Global app object holds all functions and app state
 */
var app = app || {};

$.extend(app, {
	templates: {}
});

/**
 * Initialisation functions
 */
app.init = {
	//application entry point, called at end of this file
	bootstrap: function() {
		//run conditional init functions if selector exists on page
		for (var selector in app.init.selectors) {
			if ($(selector).length) app.init.selectors[selector]();
		}

	},

	//selector-based init functions, called from bootstrap
	selectors: {
		'#chart': function() {
			google.load('visualization', '1.0', {'packages':['corechart']});

			google.setOnLoadCallback(app.charts.draw);
		}
	}
};

app.charts = {
	draw: function() {

		// Create the data table.
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Measurement');
		data.addColumn('number', 'Depth');
		data.addRows([
			['1', 3],
			['2', 1],
			['3', 1],
			['4', 1],
			['5', 2]
		]);

		// Set chart options
		var options = {
			'title':'Chart 1',
			'width':400,
			'height':300
		};

		// Instantiate and draw our chart, passing in some options.
		var chart = new google.visualization.LineChart(document.getElementById('chart'));
		chart.draw(data, options);
	}
}

//start it up
app.init.bootstrap();