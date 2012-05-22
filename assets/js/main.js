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

app.setTitle = function (title) {
	$('#main-header h1').text(title);
};

app.charts = {
	chart: null,
	currentIndex: 0,

	init: function() {
        if (window.graphData.type == 'depth') {
    		app.charts.chart = new google.visualization.AreaChart(document.getElementById('chart'));
        } else {
    		app.charts.chart = new google.visualization.LineChart(document.getElementById('chart'));
        }
		app.charts.drawDepthSeries(app.charts.currentIndex);

		$('input.next').click(function(){
			app.charts.drawDepthSeries(++app.charts.currentIndex);
			app.charts.updateButtonState();
		});
		$('input.prev').click(function () {
			app.charts.drawDepthSeries(--app.charts.currentIndex);
			app.charts.updateButtonState();
		});

		app.charts.updateButtonState();
	},

	updateButtonState: function () {
		if (app.charts.currentIndex == (window.graphData.series.length - 1)) {
			$('input.next').attr('disabled', 'disabled');
		} else {
			$('input.next').removeAttr('disabled');
		}
		if (app.charts.currentIndex == 0) {
			$('input.prev').attr('disabled', 'disabled');
		} else {
			$('input.prev').removeAttr('disabled');
		}
	},

	drawDepthSeries: function(index) {
		var series = window.graphData.series[index];

		app.setTitle(series.title);
		var data = new google.visualization.DataTable();
		for (var i in series.columns) {
			data.addColumn(series.columns[i].type, series.columns[i].label);
		}
		for (var j in series.points) {
			if (window.graphData.options.hAxis.title == 'Date') {
                series.points[j][0] = new Date(series.points[j][0]);
            }
		}
		data.addRows(series.points);

		app.charts.chart.draw(data, window.graphData.options);
	}

}

//start it up
app.init.bootstrap();