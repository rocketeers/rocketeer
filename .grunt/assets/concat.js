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
				'rocketeer/CHANGELOG.md',
				'wiki/III-Further/Troubleshooting.md',
			],
		},
		options: {
			separator: "\n\n[/section]\n[section]\n\n"
		},
	},
	css: {
		files: {
			'<%= paths.compiled.css %>/styles.css': [
				'<%= paths.components.bootstrap.css %>',
				'<%= components %>/rainbow/themes/tomorrow-night.css',
				'<%= paths.original.css %>/*'
			],
		},
	},
	js: {
		files: {
			'<%= paths.compiled.js %>/scripts.js': [
				'<%= paths.components.jquery %>',
				'<%= components %>/rainbow/js/rainbow.js',
				'<%= components %>/rainbow/js/language/generic.js',
				'<%= components %>/rainbow/js/language/php.js',
				'<%= components %>/toc/dist/jquery.toc.js',

				'<%= paths.original.js %>/**/*.js',
			],
		},
	}
};