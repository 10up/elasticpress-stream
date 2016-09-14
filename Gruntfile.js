module.exports = function (grunt) {
	'use strict';

	// Load all grunt tasks
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

	// Project configuration
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		// @see https://github.com/cedaro/grunt-wp-i18n/blob/develop/docs/makepot.md
		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true
					},
					type: 'wp-plugin',
					updateTimestamp: false
				}
			}
		}
	});

	// Default tasks
	grunt.registerTask('default', ['makepot']);

	grunt.util.linefeed = '\n';
};
