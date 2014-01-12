module.exports = {
	dist: {
		expand : true,
		cwd    : '<%= paths.compiled.js %>',
		src    : ['*.js'],
		dest   : '<%= paths.compiled.js %>',
		ext    : '.min.js',
	}
};