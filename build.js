({
  baseUrl: "assets/js",
  paths: {
		'jquery'          : "../../components/jquery/jquery.min",
		'marked'          : "../../components/marked/lib/marked",
		'rainbow'         : "../../components/rainbow/js/rainbow.min",
		'rainbow-generic' : "../../components/rainbow/js/language/generic",
		'rainbow-php'     : "../../components/rainbow/js/language/php",
  },
  optimize: "uglify2",
  name: "index",
  out: "assets/js/main.js"
})