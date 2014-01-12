module.exports = {
	deploy: {
		command: [
			'git push',
			'php versions/rocketeer.phar deploy --verbose'
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