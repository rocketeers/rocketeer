module.exports = function(grunt) {

	// Load modules
	grunt.loadNpmTasks('grunt-bower-task');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-compass');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-csslint');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-markdown');
	grunt.loadNpmTasks('grunt-prettify');
	grunt.loadNpmTasks('grunt-shell');

	// Project configuration.
	grunt.initConfig({

		//////////////////////////////////////////////////////////////////
		/////////////////////////////// PATHS ////////////////////////////
		//////////////////////////////////////////////////////////////////

		app        : 'assets',
		builds     : 'assets/compiled',
		components : 'bower_components',

		paths: {
			original: {
				css  : '<%= app %>/css',
				js   : '<%= app %>/js',
				sass : '<%= app %>/sass',
				img  : '<%= app %>/img',
			},
			compiled: {
				css : '<%= builds %>/css',
				js  : '<%= builds %>/js',
				img : '<%= builds %>/img',
			},
		},

		//////////////////////////////////////////////////////////////////
		/////////////////////////////// TASKS ////////////////////////////
		//////////////////////////////////////////////////////////////////

		// Development
		//////////////////////////////////////////////////////////////////

		watch: {
			options: {
				livereload : true,
				interrupt  : true,
			},

			grunt: {
				files: 'Gruntfile.js',
				tasks: 'default',
			},
			markdown: {
				files: ['wiki/*.md', 'rocketeer/README.md'],
				tasks: 'md',
			},
			scripts: {
				files: '<%= paths.original.js %>/**/*',
				tasks: 'js',
			},
			stylesheets: {
				files: '<%= paths.original.sass %>/**/*',
				tasks: 'css',
			},
		},

		shell: {
			deploy: {
				command: [
					'git push',
					'php versions/rocketeer deploy'
				].join('&&')
			},

			phar: {
				command: [
					'cd rocketeer',
					'composer install',
					'php bin/compile',
					'mv bin/rocketeer.phar ../versions/rocketeer',
				].join('&&'),
			}
		},

		clean: ['<%= builds %>'],

		// Assets
		//////////////////////////////////////////////////////////////////

		bower: {
			install: {
				options: {
					targetDir: '<%= components %>'
				}
			}
		},

		concat: {
			markdown: {
				files: {
					'contents.md': [
						'rocketeer/README.md', "wiki/Whats-Rocketeer.md", 'wiki/Getting-started.md', 'wiki/Tasks.md', 'wiki/Events.md', 'wiki/Plugins.md',
					],
				},
				options: {
					separator: "\n\n[/section]\n[section]\n\n"
				},
			},
			stylesheets: {
				files: {
					'<%= paths.compiled.css %>/styles.css': [
						'<%= components %>/bootstrap/dist/css/bootstrap.min.css',
						'<%= components %>/rainbow/themes/obsidian.css',
						'<%= paths.original.css %>/*'
					],
				},
			},
			javascript: {
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
		},

		copy: {
			dist: {
				files: [
					{
						expand : true,
						src    : ['**'],
						cwd    : '<%= components %>/bootstrap/fonts',
						dest   : '<%= builds %>/fonts/'
					},
					{
						expand : true,
						src    : ['**'],
						cwd    : '<%= paths.original.img %>',
						dest   : '<%= paths.compiled.img %>'
					}
				]
			}
		},

		cssmin: {
			minify: {
				expand : true,
				cwd    : '<%= paths.compiled.css %>',
				src    : '*.css',
				dest   : '<%= paths.compiled.css %>',
				ext    : '.min.css'
			}
		},

		uglify: {
			dest: {
				files: [{
					expand : true,
					cwd    : '<%= paths.compiled.js %>',
					src    : ['*.js'],
					dest   : '<%= paths.compiled.js %>',
					ext    : '.min.js',
				}],
			}
		},

		// Linting
		//////////////////////////////////////////////////////////////////

		csslint: {
			dist: {
				options: {
					'adjoining-classes'          : false,
					'unique-headings'            : false,
					'qualified-headings'         : false,
					'star-property-hack'         : false,
					'floats'                     : false,
					'display-property-grouping'  : false,
					'duplicate-properties'       : false,
					'text-indent'                : false,
					'known-properties'           : false,
					'font-sizes'                 : false,
					'box-model'                  : false,
					'gradients'                  : false,
					'box-sizing'                 : false,
					'compatible-vendor-prefixes' : false,
				},
				src: ['<%= paths.original.css %>/*']
			},
		},

		jshint: {
			options: {
				boss    : true,
				browser : true,
				bitwise : true,
				curly   : true,
				devel   : true,
				eqeqeq  : true,
				eqnull  : true,
				immed   : true,
				indent  : 2,
				latedef : true,
				newcap  : true,
				noarg   : true,
				noempty : true,
				sub     : true,
				undef   : true,
				unused  : true,
				predef  : [
					'marked', 'Rainbow',
				],
				globals : {
					$ : false,
				}
			},
			all: ['<%= paths.original.js %>/*']
		},

		// Preprocessors
		//////////////////////////////////////////////////////////////////

		markdown: {
			dist: {
				files: {
					'index.html': ['contents.md'],
				},
				options: {
					template: 'index.template',
					postCompile: function(src, context) {
						src = src
							.replace(/\[section\](<\/p>)?/g, '<section>')
							.replace(/(<p>)?\[\/section\]/g, '</section>');

						return '<section>'+src+'</section>';
					},
				},
			}
		},

		prettify: {
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
		},

		compass: {
			options: {
				appDir             : "assets/",
				cssDir             : "css",
				generatedImagesDir : "img/sprite/generated",
				imagesDir          : "img",
				outputStyle        : 'nested',
				noLineComments     : true,
				relativeAssets     : true,
				require            : ['susy'],
			},

			clean: {
				options: {
					clean: true,
				}
			},
			compile: {},
		}

	});

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// COMMANDS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	grunt.registerTask('default', 'Build assets for local', [
		'css', 'js', 'md',
		'copy',
	]);

	grunt.registerTask('production', 'Build assets for production', [
		'md',
		'copy',
		'concat', 'minify',
		'shell:phar'
	]);

	grunt.registerTask('rebuild', 'Build assets from scratch', [
		'compass',
		'clean',
		'default',
	]);

	// Flow
	////////////////////////////////////////////////////////////////////

	grunt.registerTask('minify', 'Minify assets', [
		'cssmin',
		'uglify',
	]);

	// By filetype
	////////////////////////////////////////////////////////////////////

	grunt.registerTask('md', 'Build contents', [
		'concat:markdown',
		'markdown',
		'prettify',
	]);

	grunt.registerTask('js', 'Build scripts', [
		'jshint',
		'concat:javascript',
	]);

	grunt.registerTask('css', 'Build stylesheets', [
		'compass:compile',
		'csslint',
		'concat:stylesheets'
	]);
};