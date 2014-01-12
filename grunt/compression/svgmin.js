module.exports = {
	dist: {
		expand: true,
		cwd: '<%= paths.original.img %>',
		src: ['*.svg'],
		dest: '<%= paths.original.img %>',
		ext: '.svg'
	}
};