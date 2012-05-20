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

			google.setOnLoadCallback(app.charts.init);
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
	init: function() {
		var data = app.charts.drawers[window.graphData.type](window.graphData.series);

		var chart = new google.visualization.LineChart(document.getElementById('chart'));
		chart.draw(data, window.graphData.options);
	},

	drawers: {
		depth: function(series) {

			var data = new google.visualization.DataTable();
			data.addColumn('number', 'Width');
			data.addColumn('number', 'Depth');
			data.addRows(series);

			return data;
		}
	}
}

//start it up
app.init.bootstrap();