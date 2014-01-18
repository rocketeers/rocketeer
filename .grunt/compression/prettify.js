module.exports = {
	dist: {
		options: {
			indent           : 2,
			condense         : false,
			indent_char      : '	',
			wrap_line_length : 78,
			brace_style      : 'expand',
			unformatted      : ['strong', 'em', 'a', 'code', 'pre']
		},
		files: {
			'index.html': ['index.html']
		}
	}
};