module.exports = function(grunt) {
    // Project Configuration
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        watch: {
            sass: {
                files: ['lib/craftcms-sass/_mixins.scss', 'src/web/assets/feedme/dist/css/*.scss'],
                tasks: 'css'
            },
            js: {
                files: ['src/web/assets/feedme/src/js/*.js'],
                tasks: ['concat', 'uglify:js']
            }
        },
        sass: {
            options: {
                style: 'compact',
                unixNewlines: true
            },
            dist: {
                expand: true,
                cwd: 'src/web/assets',
                src: [
                    '**/*.scss'
                ],
                dest: 'src/web/assets',
                rename: function(dest, src) {
                    // Keep them where they came from
                    return dest + '/' + src;
                },
                ext: '.css'
            }
        },
        postcss: {
            options: {
                map: true,
                processors: [
                    require('autoprefixer')({browsers: 'last 2 versions'})
                ]
            },
            dist: {
                expand: true,
                cwd: 'src/web/assets',
                src: [
                    '**/*.css',
                ],
                dest: 'src/web/assets'
            }
        },
        concat: {
            js: {
                options: {
                    banner: '/*! <%= pkg.name %> <%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %> */\n' +
                        '(function($){\n\n',
                    footer: '\n})(jQuery);\n'
                },
                src: [
                    'src/web/assets/feedme/src/js/_help.js',
                    'src/web/assets/feedme/src/js/_selectize.js',
                    'src/web/assets/feedme/src/js/feed-me.js',
                ],
                dest: 'src/web/assets/feedme/dist/js/feed-me.js'
            }
        },
        uglify: {
            options: {
                sourceMap: true,
                preserveComments: 'some',
                screwIE8: true
            },
            js: {
                src: 'src/web/assets/feedme/dist/js/feed-me.js',
                dest: 'src/web/assets/feedme/dist/js/feed-me.min.js'
            },
        },
        jshint: {
            options: {
                expr: true,
                laxbreak: true,
                loopfunc: true, // Supresses "Don't make functions within a loop." errors
                shadow: true,
                strict: false,
                '-W041': true,
                '-W061': true
            },
            beforeconcat: [
                'gruntfile.js',
                'src/web/assets/**/*.js',
                '!src/web/assets/**/*.min.js',
                '!src/web/assets/feedme/dist/js/feed-me.js',
            ],
            afterconcat: [
                'src/web/assets/feedme/dist/js/feed-me.js'
            ]
        }
    });

    //Load NPM tasks
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-postcss');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-jshint');

    // Default task(s).
    grunt.registerTask('css', ['sass', 'postcss']);
    grunt.registerTask('js', ['jshint:beforeconcat', 'concat', 'jshint:afterconcat', 'uglify']);
    grunt.registerTask('default', ['css', 'js']);
};
