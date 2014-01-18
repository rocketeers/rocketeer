module.exports = {
	options: {
		livereload : true,
		interrupt  : true,
	},

	grunt: {
		files: 'Gruntfile.js',
		tasks: 'default',
	},
	markdown: {
		files: ['wiki/*.md', 'rocketeer/README.md'],
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