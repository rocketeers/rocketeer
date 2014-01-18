module.exports = {
	options: {
		compass            : true,
		ignoreSassMixins   : true,
		minMatch           : 5,
		failWhenDuplicates : true,
		verbose            : true,
	},

	dist: {
		src: '<%= paths.original.css %>/*.css'
	}
};