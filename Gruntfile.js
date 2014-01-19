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
		var glob   = require('glob');
		var path   = require('path');
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
		app        : 'public/app',
		builds     : 'public/builds',
		components : 'public/components',

		paths: {
			original: {
				css  : '<%= app %>/css',
				js   : '<%= app %>/js',
				sass : '<%= app %>/sass',
				img  : '<%= app %>/img',
			},
			compiled: {
				css : '<%= builds %>/css',
				js  : '<%= builds %>/js',
				img : '<%= builds %>/img',
			},
		},
	};

	// Load all tasks
	loadConfig('./.grunt/');
	grunt.initConfig(config);

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// COMMANDS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	grunt.registerTask('default', 'Build assets for local', [
		'css', 'js', 'md',
		'copy',
	]);

	grunt.registerTask('production', 'Build assets for production', [
		'replace',
		'md',
		'copy',
		'concat', 'minify',
		'shell:phar'
	]);

	grunt.registerTask('rebuild', 'Build assets from scratch', [
		'compass',
		'clean',
		'default',
	]);

	// Flow
	////////////////////////////////////////////////////////////////////

	grunt.registerTask('minify', 'Minify assets', [
		'cssmin',
		'uglify',
	]);

	grunt.registerTask('images', 'Recompress images', [
		'svgmin',
		'tinypng',
	]);

	// By filetype
	////////////////////////////////////////////////////////////////////

	grunt.registerTask('md', 'Build contents', [
		'concat:md',
		'markdown',
		'prettify',
	]);

	grunt.registerTask('js', 'Build scripts', [
		'jshint',
		'concat:js',
	]);

	grunt.registerTask('css', 'Build stylesheets', [
		'compass:compile',
		'csslint',
		'concat:css'
	]);
};