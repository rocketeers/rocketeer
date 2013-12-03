/**
 * Parses a Markdown file and returns HTML
 *
 * @param  {string} file
 *
 * @return {string}
 */
var parse = function(file) {
	file = marked.lexer(file);
	file = marked.parser(file);

	return file;
};

var appendFile = function(file) {
	file = '<section>' +parse(file)+ '</section>';

	$('.layout-container').append(file);
	Rainbow.color();

	// Replace download links
	$('a[href="http://rocketeer.autopergamene.eu/versions/rocketeer"]').attr('target', '_blank').attr('download', '');
};

// Get each Markdown file, parse it and add it to HTML
['rocketeer/README.md', "wiki/Whats-Rocketeer.md", 'wiki/Getting-started.md', 'wiki/Tasks.md'].forEach(function(file) {
	$.ajax({
		type    : 'GET',
		async   : false,
		url     : file,
		success : appendFile
	});
});