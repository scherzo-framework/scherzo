module.exports = function(grunt) {

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    phpunit: {
      docs: {
        dir: 'test/unit',
        options: {
          logJunit:    'build/docs/<%= pkg.version %>/results/junit.xml',
          logTap:      'build/docs/<%= pkg.version %>/results/tap.txt',
          logJson:     'build/docs/<%= pkg.version %>/results/phpunit.json',
          testdoxHtml: 'build/docs/<%= pkg.version %>/results/testdox.html',
          testdoxText: 'build/docs/<%= pkg.version %>/results/testdox.txt',
        }
      }
    },

  phpdocumentor: {
    docs: {
      options: {
        directory: 'classes',
        target: 'build/docs/<%= pkg.version %>/api/'
      }
    }
  },

  clean: {
    // ensure nothing is left from a previous build
    beforeDocs: ['build/docs/<%= pkg.version %>/'],
    // remove phpdoc working files
    afterDocs: ['build/docs//<%= pkg.version %>/api/phpdoc-cache-*']
  }


  });

  grunt.loadNpmTasks('grunt-phpunit');
  grunt.loadNpmTasks('grunt-phpdocumentor');
  grunt.loadNpmTasks('grunt-contrib-clean');

  grunt.registerTask('unittest', ['phpunit']);

  grunt.registerTask('default', ['unittest']);

  // build test result output and api docs
  grunt.registerTask('docs', ['clean:beforeDocs', 'phpunit:docs', 'phpdocumentor:docs', 'clean:afterDocs']);

};
