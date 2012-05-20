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

        /* Remove/delete link autoconfirm */

        $('a.autoConfirm').live('click', function(e) {
            e.preventDefault();
            $(this).replaceWith('<form method="post" action="'+$(this).attr('href')+'" class="'+$(this).attr('class')+'">' +
                'Are you sure? <input type="submit" value="'+$(this).text()+'" title="' + $(this).attr('title') + '"> <a href="">Cancel</a></form>');
        });

        $('form.autoConfirm a').live('click', function(e) {
            e.preventDefault();
            var $form = $(this).closest('form');
            $form.replaceWith('<a href="'+$form.attr('action')+'" class="'+$form.attr('class')+'" title="' + $form.find('input').attr('title') + '">' +
                $form.find('input').attr('value') + '</a>');
        });

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
	chart: null,
	currentSite: 0,
	init: function() {
		app.charts.chart = new google.visualization.LineChart(document.getElementById('chart'));
		app.charts.drawDepthSeries(app.charts.currentSite);
		$('input.next').click(function(){
			app.charts.currentSite = (app.charts.currentSite + 1) % window.graphData.series.length;
			app.charts.drawDepthSeries(app.charts.currentSite);
		});
		$('input.prev').click(function () {
			app.charts.currentSite = (app.charts.currentSite - 1) % window.graphData.series.length;
			app.charts.drawDepthSeries(app.charts.currentSite);
		});
	},

	drawDepthSeries: function(index) {
		var data = app.charts.drawers[window.graphData.type](window.graphData.series[index]);
		app.charts.chart.draw(data, window.graphData.options);
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