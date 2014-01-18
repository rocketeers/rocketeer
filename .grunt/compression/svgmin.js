module.exports = {
	dist: {
		expand : true,
		cwd    : '<%= paths.original.svg %>',
		src    : ['*.svg'],
		dest   : '<%= paths.original.svg %>',
		ext    : '.svg'
	}
};