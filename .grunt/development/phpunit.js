module.exports = {
	options: {
		followOutput: true,
	},

	core: {
		options: {
			excludeGroup: 'Routes',
		}
	},

	coverage: {
		options: {
			excludeGroup: 'Routes',
			coverageHtml: '<%= tests %>/.coverage'
		}
	},

	all: {
	},
};