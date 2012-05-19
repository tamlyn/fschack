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

	}
};

//start it up
app.init.bootstrap();