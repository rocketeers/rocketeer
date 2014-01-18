module.exports = {
	md: {
		files: {
			'contents.md': [
				'rocketeer/README.md',
				'wiki/I-Introduction/Whats-Rocketeer.md',
				'wiki/I-Introduction/Getting-started.md',
				'wiki/II-Concepts/Tasks.md',
				'wiki/II-Concepts/Connections-Stages.md',
				'wiki/II-Concepts/Events.md',
				'wiki/II-Concepts/Plugins.md',
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