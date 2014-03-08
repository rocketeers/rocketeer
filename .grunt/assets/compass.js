module.exports = {
	options: {
		appDir             : "<%= app %>/",
		cssDir             : "css",
		generatedImagesDir : "img/sprite/generated",
		imagesDir          : "img",
		outputStyle        : 'nested',
		noLineComments     : true,
		relativeAssets     : true,
		require            : ['susy', 'breakpoint', 'sass-globbing'],
	},

	/**
	 * Cleans the created files and rebuilds them
	 */
	clean: {
		options: {
			clean: true,
		}
	},

	/**
	 * Compile Sass files
	 */
	compile: {},
};