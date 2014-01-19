module.exports = {
	dist: {
		src: 'index.template.html',
		dest: './',
		replacements: [{
			from: /\.(css|js)/g,
			to: '.min.$1'
		}]
	}
};