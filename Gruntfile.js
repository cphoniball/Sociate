module.exports = function(grunt) {

  grunt.initConfig({
    jshint: {
      all: [
        'js/sociate-admin.jquery.js', 
        'js/sociate-charts.jquery.js', 
        'js/sociate.jquery.js'
      ]
    },
    uglify: {
      all: {
        files: {
          'js/sociate.jquery.min.js': 'js/sociate.jquery.js'
        }
      }
    },
    cssmin: {
      all: {
        files: {
          'css/sociate.min.css': ['css/sociate.css']
        }
      }
    }
  });
  
  
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-concat');

  grunt.registerTask('default', ['uglify', 'cssmin']); 
};
