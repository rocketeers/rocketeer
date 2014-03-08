module.exports = function(grunt) {

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// COMMANDS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	grunt.registerTask('default', 'Build assets for local', [
		'css',
		'js',
		'md',
		'copy',
	]);

	grunt.registerTask('rebuild', 'Build assets from scratch', [
		'compass',
		'clean',
		'default',
	]);

	grunt.registerTask('production', 'Build assets for production', [
		'clean',
		'replace',
		'md',
		'copy',
		'concat', 'minify',
		'shell:phar'
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
		'csscss',
		'autoprefixer',
		'concat:css',
	]);

};