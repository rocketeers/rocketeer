module.exports = {
	options: {
		apiKey       : 'Dwy-_pMBk4LwxsuPBVSZn1sU9cAIxcUP',
		checkSigs    : true,
		sigFile      : 'config/sigfile.json',
		summarize    : true,
		showProgress : true
	},

	dist: {
		expand : true,
		cwd    : '<%= paths.original.img %>',
		src    : ['**/*.png'],
		dest   : '<%= paths.original.img %>',
		ext    : '.png'
	},
};