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

// Get each Markdown file, parse it and add it to HTML
['rocketeer/README.md', 'wiki/Getting-started.md', 'wiki/Tasks.md'].forEach(function(file) {
	$.ajax({
		type: 'GET',
		async: false,
		url: file,
		success: function(file) {

			// Add file to page
			$('.layout-container').append('<section>' + parse(file) + '</section>');
		}
	});
});

// Highlight codeblocks
$('code').each(function(key, pre) {
	var content = $(pre).html();

	return Rainbow.color(content, 'php', function(highlighted) {
		return $(pre).html(highlighted);
	});
});