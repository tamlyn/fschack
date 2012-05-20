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
            var $element = $(selector);
			if ($element.length) app.init.selectors[selector]($element);
		}

	},

	//selector-based init functions, called from bootstrap
	selectors: {
		'#chart': function($element) {
			google.load('visualization', '1.0', {'packages':['corechart']});

			google.setOnLoadCallback(app.charts.draw);
		},

		'.dtable': function($element) {
			$element.dataTable({
				bPaginate:false
			});
		},

        'input#date': function($element) {
            $element.datepicker({
                dateFormat: 'dd-mm-yy'
            });
        }
	}
};

app.charts = {
	draw: function() {

		// Create the data table.
		data = new google.visualization.DataTable();
		data.addColumn('string', 'Measurement');
		data.addColumn('number', 'Depth');
		data.addRows(window.graphData);

		// Set chart options
		var options = {
			hAxis:{
				title: 'Measurements'
			},
			vAxis: {
				title: 'Depth (m)',
				direction: -1
			},
			legend: {
				position: 'none'
			}
		};

		// Instantiate and draw our chart, passing in some options.
		var chart = new google.visualization.LineChart(document.getElementById('chart'));
		chart.draw(data, options);
	}
}

//start it up
app.init.bootstrap();