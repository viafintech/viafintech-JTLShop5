module.exports = function (grunt) {
    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        potomo: {
            dist: {
                files: [
                    {
                        'locale/de-DE/base.mo': 'locale/de-DE/base.po',
                        'locale/en-GB/base.mo': 'locale/en-GB/base.po'
                    }
                ]
            }
        },
        watch: {
            pomo: {
                files: ['locale/de-DE/base.po', 'locale/en-GB/base.po'],
                tasks: ['potomo'],
                options: {
                    atBegin: true
                }
            }
        }
    });

    /* SCSS compilation */
    //grunt.loadNpmTasks('grunt-contrib-sass');
    //grunt.loadNpmTasks('grunt-autoprefixer');

    /* JS minify */
    //grunt.loadNpmTasks('grunt-contrib-uglify');

    /* admin localisation */
    grunt.loadNpmTasks('grunt-potomo');

    // Default task(s).
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.registerTask('default', ['watch']);

};
