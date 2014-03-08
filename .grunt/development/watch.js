module.exports = {
	options: {
		livereload : true,
		interrupt  : true,
	},

	grunt: {
		files: ['Gruntfile.js', '<%= grunt %>/**/*'],
		tasks: 'default',
	},
	md: {
		files: ['index.template.html', 'wiki/*.md', 'rocketeer/README.md'],
		tasks: 'md',
	},
	img: {
		files: '<%= paths.original.img %>/**/*',
		tasks: 'copy',
	},
	js: {
		files: '<%= paths.original.js %>/**/*',
		tasks: 'js',
	},
	css: {
		files: '<%= paths.original.sass %>/**/*',
		tasks: 'css',
	},
};