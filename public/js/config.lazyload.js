// lazyload config

angular.module('app')
    /**
   * jQuery plugin config use ui-jq directive , config the js and css files that required
   * key: function name of the jQuery plugin
   * value: array of the css js file located
   */
  .constant('JQ_CONFIG', {
      easyPieChart:   [   '/js/jquery.easypiechart.fill.js'],
      sparkline:      [   '/js/jquery.sparkline.retina.js'],
      plot:           [   '/js/jquery.flot.js',
                          '/js/jquery.flot.pie.js',
                          '/js/jquery.flot.resize.js',
                          '/js/jquery.flot.tooltip.min.js',
                          '/js/jquery.flot.orderBars.js',
                          '/js/jquery.flot.spline.min.js'],
      moment:         [   '/js/moment.js'],
      screenfull:     [   '/js/screenfull.min.js'],
      slimScroll:     [   '/js/jquery.slimscroll.min.js'],
      sortable:       [   '/js/jquery.sortable.js'],
      nestable:       [   '/js/jquery.nestable.js',
                          '/js/jquery.nestable.css'],
      filestyle:      [   '/js/bootstrap-filestyle.js'],
      slider:         [   '/js/bootstrap-slider.js',
                          '/js/bootstrap-slider.css'],
      chosen:         [   '/js/chosen.jquery.min.js',
                          '/js/bootstrap-chosen.css'],
      TouchSpin:      [   '/js/jquery.bootstrap-touchspin.min.js',
                          '/js/jquery.bootstrap-touchspin.min.css'],
      wysiwyg:        [   '/js/bootstrap-wysiwyg.js',
                          '/js/jquery.hotkeys.js'],
      dataTable:      [   '/js/jquery.dataTables.min.js',
                          '/js/dataTables.bootstrap.js',
                          '/js/dataTables.bootstrap.css'],
      vectorMap:      [   '/js/jquery-jvectormap-1.2.2.min.js',
                          '/js/jquery-jvectormap-world-mill-en.js',
                          '/js/jquery-jvectormap-us-aea-en.js',
                          '/js/jquery-jvectormap.css'],
      footable:       [   '/js/footable.min.js',
                          '/js/footable.bootstrap.min.css'],
      fullcalendar:   [   '/js/moment.js',
                          '/js/fullcalendar.min.js',
                          '/js/fullcalendar.css',
                          '/js/fullcalendar.theme.css'],
      daterangepicker:[   '/js/moment.js',
                          '/js/daterangepicker.js',
                          '/js/daterangepicker-bs3.css'],
      tagsinput:      [   '/js/bootstrap-tagsinput.js',
                          '/js/bootstrap-tagsinput.css']

    }
  )
;
