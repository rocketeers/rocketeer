module.exports = function(grunt) {

	// Load modules
	require('load-grunt-tasks')(grunt);

	/**
	 * Loads all available tasks options
	 *
	 * @param {String} folder
	 *
	 * @return {Object}
	 */
	function loadConfig(folder) {
		var glob = require('glob');
		var path = require('path');
		var key;

		glob.sync('**/*.js', {cwd: folder}).forEach(function(option) {
			key = path.basename(option, '.js');
			config[key] = require(folder + option);
		});
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////// CONFIGURATION /////////////////////////
	////////////////////////////////////////////////////////////////////

	var config = {
		pkg  : grunt.file.readJSON('package.json'),
		name : '<%= pkg.name %>',

		grunt      : '.grunt',
		app        : 'public/app',
		builds     : 'public/builds',
		components : 'public/components',

		paths: {
			original: {
				css  : '<%= app %>/css',
				js   : '<%= app %>/js',
				sass : '<%= app %>/sass',
				img  : '<%= app %>/img',
				svg  : '<%= app %>/svg',
			},
			compiled: {
				css : '<%= builds %>/css',
				js  : '<%= builds %>/js',
				img : '<%= builds %>/img',
				svg : '<%= builds %>/svg',
			},
			components: {
				jquery    : '<%= components %>/jquery/dist/jquery.js',
				icomoon   : '<%= components %>/icomoon/style.css',
				backbone  : '<%= components %>/backbone/backbone.js',
				bootstrap : {
					css   : '<%= components %>/bootstrap/dist/css/bootstrap.css',
					fonts : '<%= components %>/bootstrap/dist/fonts',
					js    : '<%= components %>/bootstrap/dist/js/bootstrap.js',
				},
			}
		},
	};

	// Load all tasks
	var gruntPath = './'+config.grunt+'/';
	loadConfig(gruntPath);
	grunt.initConfig(config);

	// Load custom tasks
	require(gruntPath + 'tasks.js')(grunt);

};