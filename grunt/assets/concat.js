module.exports = {
	md: {
		files: {
			'contents.md': [
				'rocketeer/README.md', "wiki/Whats-Rocketeer.md", 'wiki/Getting-started.md', 'wiki/Tasks.md', 'wiki/Events.md', 'wiki/Plugins.md',
			],
		},
		options: {
			separator: "\n\n[/section]\n[section]\n\n"
		},
	},
	css: {
		files: {
			'<%= paths.compiled.css %>/styles.css': [
				'<%= components %>/bootstrap/dist/css/bootstrap.min.css',
				'<%= components %>/rainbow/themes/obsidian.css',
				'<%= paths.original.css %>/*'
			],
		},
	},
	js: {
		files: {
			'<%= paths.compiled.js %>/scripts.js': [
				'<%= components %>/jquery/jquery.js',
				'<%= components %>/marked/lib/marked.js',
				'<%= components %>/rainbow/js/rainbow.js',
				'<%= components %>/rainbow/js/language/generic.js',
				'<%= components %>/rainbow/js/language/php.js',

				'<%= paths.original.js %>/*',
			],
		},
	}
};