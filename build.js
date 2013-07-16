({
  baseUrl: "assets/js",
  paths: {
		'jquery'          : "../../bower_components/jquery/jquery.min",
		'marked'          : "../../bower_components/marked/lib/marked",
		'rainbow'         : "../../bower_components/rainbow/js/rainbow.min",
		'rainbow-generic' : "../../bower_components/rainbow/js/language/generic",
		'rainbow-php'     : "../../bower_components/rainbow/js/language/php",
  },
  optimize: "uglify2",
  name: "index",
  out: "assets/js/main.js"
})