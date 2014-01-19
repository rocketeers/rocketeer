module.exports = {
	options: {
		livereload : true,
		interrupt  : true,
	},

	grunt: {
		files: ['Gruntfile.js', '.grunt/**/*'],
		tasks: 'default',
	},
	markdown: {
		files: ['index.template.html', 'wiki/*.md', 'rocketeer/README.md'],
		tasks: 'md',
	},
	scripts: {
		files: '<%= paths.original.js %>/**/*',
		tasks: 'js',
	},
	stylesheets: {
		files: '<%= paths.original.sass %>/**/*',
		tasks: 'css',
	},
};