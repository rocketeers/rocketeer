module.exports = {
	deploy: {
		command: [
			'git push',
			'php rocketer/bin/rocketeer deploy --verbose'
		].join('&&')
	},

	phar: {
		command: [
			'cd rocketeer',
			'composer install',
			'php bin/compile',
			'mv bin/rocketeer.phar ../versions/rocketeer.phar',
		].join('&&'),
	}
};