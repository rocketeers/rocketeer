module.exports = {
	minify: {
		expand : true,
		cwd    : '<%= paths.compiled.css %>',
		src    : '*.css',
		dest   : '<%= paths.compiled.css %>',
		ext    : '.min.css'
	}
};