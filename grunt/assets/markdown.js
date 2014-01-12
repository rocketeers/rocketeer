module.exports = {
	dist: {
		files: {
			'index.html': ['contents.md'],
		},
		options: {
			template: 'index.template.html',
			postCompile: function(src, context) {
				src = src
					.replace(/\[section\](<\/p>)?/g, '<section>')
					.replace(/(<p>)?\[\/section\]/g, '</section>');

				return '<section>'+src+'</section>';
			},
		},
	}
};