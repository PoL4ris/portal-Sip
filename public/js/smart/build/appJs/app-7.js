
"use strict";

angular.module('app.graphs').directive('morrisTimeGraph', function(){
  return {
    restrict: 'E',
    replace: true,
    template: '<div class="chart no-padding"></div>',
    link: function(scope, element){

      var week_data = [{
        "period" : "2011 W27",
        "licensed" : 3407,
        "sorned" : 660
      }, {
        "period" : "2011 W26",
        "licensed" : 3351,
        "sorned" : 629
      }, {
        "period" : "2011 W25",
        "licensed" : 3269,
        "sorned" : 618
      }, {
        "period" : "2011 W24",
        "licensed" : 3246,
        "sorned" : 661
      }, {
        "period" : "2011 W23",
        "licensed" : 3257,
        "sorned" : 667
      }, {
        "period" : "2011 W22",
        "licensed" : 3248,
        "sorned" : 627
      }, {
        "period" : "2011 W21",
        "licensed" : 3171,
        "sorned" : 660
      }, {
        "period" : "2011 W20",
        "licensed" : 3171,
        "sorned" : 676
      }, {
        "period" : "2011 W19",
        "licensed" : 3201,
        "sorned" : 656
      }, {
        "period" : "2011 W18",
        "licensed" : 3215,
        "sorned" : 622
      }, {
        "period" : "2011 W17",
        "licensed" : 3148,
        "sorned" : 632
      }, {
        "period" : "2011 W16",
        "licensed" : 3155,
        "sorned" : 681
      }, {
        "period" : "2011 W15",
        "licensed" : 3190,
        "sorned" : 667
      }, {
        "period" : "2011 W14",
        "licensed" : 3226,
        "sorned" : 620
      }, {
        "period" : "2011 W13",
        "licensed" : 3245,
        "sorned" : null
      }, {
        "period" : "2011 W12",
        "licensed" : 3289,
        "sorned" : null
      }, {
        "period" : "2011 W11",
        "licensed" : 3263,
        "sorned" : null
      }, {
        "period" : "2011 W10",
        "licensed" : 3189,
        "sorned" : null
      }, {
        "period" : "2011 W09",
        "licensed" : 3079,
        "sorned" : null
      }, {
        "period" : "2011 W08",
        "licensed" : 3085,
        "sorned" : null
      }, {
        "period" : "2011 W07",
        "licensed" : 3055,
        "sorned" : null
      }, {
        "period" : "2011 W06",
        "licensed" : 3063,
        "sorned" : null
      }, {
        "period" : "2011 W05",
        "licensed" : 2943,
        "sorned" : null
      }, {
        "period" : "2011 W04",
        "licensed" : 2806,
        "sorned" : null
      }, {
        "period" : "2011 W03",
        "licensed" : 2674,
        "sorned" : null
      }, {
        "period" : "2011 W02",
        "licensed" : 1702,
        "sorned" : null
      }, {
        "period" : "2011 W01",
        "licensed" : 1732,
        "sorned" : null
      }];
      Morris.Line({
        element : element,
        data : week_data,
        xkey : 'period',
        ykeys : ['licensed', 'sorned'],
        labels : ['Licensed', 'SORN'],
        events : ['2011-04', '2011-08']
      });

    }
  }
});
"use strict";

angular.module('app.graphs').directive('morrisYearGraph', function(){
  return {
    restrict: 'E',
    replace: true,
    template: '<div class="chart no-padding"></div>',
    link: function(scope, element){

      var day_data = [{
        "period" : "2012-10-01",
        "licensed" : 3407,
        "sorned" : 660
      }, {
        "period" : "2012-09-30",
        "licensed" : 3351,
        "sorned" : 629
      }, {
        "period" : "2012-09-29",
        "licensed" : 3269,
        "sorned" : 618
      }, {
        "period" : "2012-09-20",
        "licensed" : 3246,
        "sorned" : 661
      }, {
        "period" : "2012-09-19",
        "licensed" : 3257,
        "sorned" : 667
      }, {
        "period" : "2012-09-18",
        "licensed" : 3248,
        "sorned" : 627
      }, {
        "period" : "2012-09-17",
        "licensed" : 3171,
        "sorned" : 660
      }, {
        "period" : "2012-09-16",
        "licensed" : 3171,
        "sorned" : 676
      }, {
        "period" : "2012-09-15",
        "licensed" : 3201,
        "sorned" : 656
      }, {
        "period" : "2012-09-10",
        "licensed" : 3215,
        "sorned" : 622
      }];
      Morris.Line({
        element : element,
        data : day_data,
        xkey : 'period',
        ykeys : ['licensed', 'sorned'],
        labels : ['Licensed', 'SORN']
      })

    }
  }
});
'use strict';

angular.module('app.graphs').directive('vectorMap', function () {
  return {
    restrict: 'EA',
    scope: {
      mapData: '='
    },
    link: function (scope, element, attributes) {
      var data = scope.mapData;

      element.vectorMap({
        map: 'world_mill_en',
        backgroundColor: '#fff',
        regionStyle: {
          initial: {
            fill: '#c4c4c4'
          },
          hover: {
            "fill-opacity": 1
          }
        },
        series: {
          regions: [
            {
              values: data,
              scale: ['#85a8b6', '#4d7686'],
              normalizeFunction: 'polynomial'
            }
          ]
        },
        onRegionLabelShow: function (e, el, code) {
          if (typeof data[code] == 'undefined') {
            e.preventDefault();
          } else {
            var countrylbl = data[code];
            el.html(el.html() + ': ' + countrylbl + ' visits');
          }
        }
      });

      element.on('$destroy', function(){
        element.children('.jvectormap-container').data('mapObject').remove();
      })
    }
  }
});
'use strict';

angular.module('app.tables').directive('datatableBasic', function ($compile) {
  return {
    restrict: 'A',
    scope: {
      tableOptions: '='
    },
    link: function (scope, element, attributes) {
      /* // DOM Position key index //

       l - Length changing (dropdown)
       f - Filtering input (search)
       t - The Table! (datatable)
       i - Information (records)
       p - Pagination (paging)
       r - pRocessing
       < and > - div elements
       <"#id" and > - div with an id
       <"class" and > - div with a class
       <"#id.class" and > - div with an id and class

       Also see: http://legacy.datatables.net/usage/features
       */

      var options = {
        "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>" +
        "t" +
        "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
        oLanguage:{
          "sSearch": "<span class='input-group-addon input-sm'><i class='glyphicon glyphicon-search'></i></span> ",
          "sLengthMenu": "_MENU_"
        },
        "autoWidth": false,
        "smartResponsiveHelper": null,
        "preDrawCallback": function () {
          // Initialize the responsive datatables helper once.
          if (!this.smartResponsiveHelper) {
            this.smartResponsiveHelper = new ResponsiveDatatablesHelper(element, {
              tablet: 1024,
              phone: 480
            });
          }
        },
        "rowCallback": function (nRow) {
          this.smartResponsiveHelper.createExpandIcon(nRow);
        },
        "drawCallback": function (oSettings) {
          this.smartResponsiveHelper.respond();
        }
      };

      if(attributes.tableOptions){
        options = angular.extend(options, scope.tableOptions)
      }

      var _dataTable;

      var childFormat = element.find('.smart-datatable-child-format');
      if(childFormat.length){
        var childFormatTemplate = childFormat.remove().html();
        element.on('click', childFormat.data('childControl'), function () {
          var tr = $(this).closest('tr');

          var row = _dataTable.row( tr );
          if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
          }
          else {
            // Open this row
            var childScope = scope.$new();
            childScope.d = row.data();
            var html = $compile(childFormatTemplate)(childScope);
            row.child( html ).show();
            tr.addClass('shown');
          }
        })
      }



      _dataTable =  element.DataTable(options);

      if(attributes.bindFilters){
        element.parent().find("div.toolbar").html('<div class="text-right"><img src="/css/smart/styles/img/logo.png" alt="SmartAdmin" style="width: 111px; margin-top: 3px; margin-right: 10px;"></div>');

        element.on( 'keyup change', 'thead th input[type=text]', function () {

          _dataTable
            .column( $(this).parent().index()+':visible' )
            .search( this.value )
            .draw();

        } );
      }
    }
  }
});
'use strict';

angular.module('app.tables').directive('datatableColumnFilter', function () {
  return {
    restrict: 'A',
    link: function (scope, element, attributes) {
      /* // DOM Position key index //

       l - Length changing (dropdown)
       f - Filtering input (search)
       t - The Table! (datatable)
       i - Information (records)
       p - Pagination (paging)
       r - pRocessing
       < and > - div elements
       <"#id" and > - div with an id
       <"class" and > - div with a class
       <"#id.class" and > - div with an id and class

       Also see: http://legacy.datatables.net/usage/features
       */

      var responsiveHelper = undefined;

      var breakpointDefinition = {
        tablet: 1024,
        phone: 480
      };

      var otable = element.DataTable({
        //"bFilter": false,
        //"bInfo": false,
        //"bLengthChange": false
        //"bAutoWidth": false,
        //"bPaginate": false,
        //"bStateSave": true // saves sort state using localStorage
        "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
        "t"+
        "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
        oLanguage:{
          "sSearch": "<span class='input-group-addon input-sm'><i class='glyphicon glyphicon-search'></i></span> "
        },
        "autoWidth" : false,
        "preDrawCallback" : function() {
          // Initialize the responsive datatables helper once.
          if (!responsiveHelper) {
            responsiveHelper = new ResponsiveDatatablesHelper(element, breakpointDefinition);
          }
        },
        "rowCallback" : function(nRow) {
          responsiveHelper.createExpandIcon(nRow);
        },
        "drawCallback" : function(oSettings) {
          responsiveHelper.respond();
        }

      });

      // custom toolbar
      element.parent().find("div.toolbar").html('<div class="text-right"><img src="/css/smart/styles/img/logo.png" alt="SmartAdmin" style="width: 111px; margin-top: 3px; margin-right: 10px;"></div>');

      // Apply the filter
      element.on( 'keyup change', 'thead th input[type=text]', function () {

        otable
          .column( $(this).parent().index()+':visible' )
          .search( this.value )
          .draw();

      } );
    }
  }
});
'use strict';

angular.module('app.tables').directive('datatableColumnReorder', function () {
  return {
    restrict: 'A',
    link: function (scope, element) {
      /* // DOM Position key index //

       l - Length changing (dropdown)
       f - Filtering input (search)
       t - The Table! (datatable)
       i - Information (records)
       p - Pagination (paging)
       r - pRocessing
       < and > - div elements
       <"#id" and > - div with an id
       <"class" and > - div with a class
       <"#id.class" and > - div with an id and class

       Also see: http://legacy.datatables.net/usage/features
       */

      var responsiveHelper = undefined;

      var breakpointDefinition = {
        tablet: 1024,
        phone: 480
      };

      element.dataTable({
        "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 hidden-xs'C>r>" +
        "t" +
        "<'dt-toolbar-footer'<'col-sm-6 hidden-xs'i><'col-sm-6 col-xs-12'p>>",
        oLanguage: {
          "sSearch": "<span class='input-group-addon input-sm'><i class='glyphicon glyphicon-search'></i></span> "
        },
        "autoWidth": false,
        "preDrawCallback": function () {
          // Initialize the responsive datatables helper once.
          if (!responsiveHelper) {
            responsiveHelper = new ResponsiveDatatablesHelper(element, breakpointDefinition);
          }
        },
        "rowCallback": function (nRow) {
          responsiveHelper.createExpandIcon(nRow);
        },
        "drawCallback": function (oSettings) {
          responsiveHelper.respond();
        }
      });
    }
  }
});
'use strict';

angular.module('app.tables').directive('datatableTableTools', function () {
  return {
    restrict: 'A',
    link: function (scope, element, attributes) {
      /* // DOM Position key index //

       l - Length changing (dropdown)
       f - Filtering input (search)
       t - The Table! (datatable)
       i - Information (records)
       p - Pagination (paging)
       r - pRocessing
       < and > - div elements
       <"#id" and > - div with an id
       <"class" and > - div with a class
       <"#id.class" and > - div with an id and class

       Also see: http://legacy.datatables.net/usage/features
       */
      var responsiveHelper = undefined;

      var breakpointDefinition = {
        tablet: 1024,
        phone: 480
      };

      element.dataTable({
        // Tabletools options:
        //   https://datatables.net/extensions/tabletools/button_options
        "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 hidden-xs'T>r>" +
        "t" +
        "<'dt-toolbar-footer'<'col-sm-6 hidden-xs'i><'col-sm-6 col-xs-12'p>>",
        oLanguage:{
          "sSearch": "<span class='input-group-addon input-sm'><i class='glyphicon glyphicon-search'></i></span> "
        },

        sFilterInput:  "form-control",
        "oTableTools": {
          "aButtons": [
            "copy",
            "csv",
            "xls",
            {
              "sExtends": "pdf",
              "sTitle": "SmartAdmin_PDF",
              "sPdfMessage": "SmartAdmin PDF Export",
              "sPdfSize": "letter"
            },
            {
              "sExtends": "print",
              "sMessage": "Generated by SmartAdmin <i>(press Esc to close)</i>"
            }
          ],
          "sSwfPath": "bower_components/datatables-tabletools/swf/copy_csv_xls_pdf.swf"
        },
        "autoWidth": false,
        preDrawCallback: function () {
          // Initialize the responsive datatables helper once.
          if (!responsiveHelper) {
            responsiveHelper = new ResponsiveDatatablesHelper(element, breakpointDefinition);
          }
        },
        rowCallback: function (nRow) {
          responsiveHelper.createExpandIcon(nRow);
        },
        drawCallback: function (oSettings) {
          responsiveHelper.respond();
        }
      });
    }
  }
});
'use strict';

angular.module('app.tables').directive('jqGrid', function ($compile) {
  var jqGridCounter = 0;

  return {
    replace: true,
    restrict: 'E',
    scope: {
      gridData: '='
    },
    template: '<div>' +
    '<table></table>' +
    '<div class="jqgrid-pagination"></div>' +
    '</div>',
    controller: function($scope, $element){
      $scope.editRow  = function(row){
        $element.find('table').editRow(row);
      };
      $scope.saveRow  = function(row){
        $element.find('table').saveRow(row);
      };
      $scope.restoreRow  = function(row){
        $element.find('table').restoreRow(row);
      };
    },
    link: function (scope, element) {
      var gridNumber = jqGridCounter++;
      var wrapperId = 'jqgrid-' + gridNumber;
      element.attr('id', wrapperId);

      var tableId = 'jqgrid-table-' + gridNumber;
      var table = element.find('table');
      table.attr('id', tableId);

      var pagerId = 'jqgrid-pager-' + gridNumber;
      element.find('.jqgrid-pagination').attr('id', pagerId);


      table.jqGrid({
        data : scope.gridData.data,
        datatype : "local",
        height : 'auto',
        colNames : scope.gridData.colNames || [],
        colModel : scope.gridData.colModel || [],
        rowNum : 10,
        rowList : [10, 20, 30],
        pager : '#' + pagerId,
        sortname : 'id',
        toolbarfilter : true,
        viewrecords : true,
        sortorder : "asc",
        gridComplete : function() {
          var ids = table.jqGrid('getDataIDs');
          for (var i = 0; i < ids.length; i++) {
            var cl = ids[i];
            var be = "<button class='btn btn-xs btn-default' uib-tooltip='Edit Row' tooltip-append-to-body='true' ng-click='editRow("+ cl +")'><i class='fa fa-pencil'></i></button>";

            var se = "<button class='btn btn-xs btn-default' uib-tooltip='Save Row' tooltip-append-to-body='true' ng-click='saveRow("+ cl +")'><i class='fa fa-save'></i></button>";

            var ca = "<button class='btn btn-xs btn-default' uib-tooltip='Cancel' tooltip-append-to-body='true' ng-click='restoreRow("+ cl +")'><i class='fa fa-times'></i></button>";

            table.jqGrid('setRowData', ids[i], {
              act : be + se + ca
            });
          }
        },
        editurl : "dummy.html",
        caption : "SmartAdmin jQgrid Skin",
        multiselect : true,
        autowidth : true

      });
      table.jqGrid('navGrid', '#' + pagerId, {
        edit : false,
        add : false,
        del : true
      });
      table.jqGrid('inlineNav', '#' + pagerId);


      element.find(".ui-jqgrid").removeClass("ui-widget ui-widget-content");
      element.find(".ui-jqgrid-view").children().removeClass("ui-widget-header ui-state-default");
      element.find(".ui-jqgrid-labels, .ui-search-toolbar").children().removeClass("ui-state-default ui-th-column ui-th-ltr");
      element.find(".ui-jqgrid-pager").removeClass("ui-state-default");
      element.find(".ui-jqgrid").removeClass("ui-widget-content");

      // add classes
      element.find(".ui-jqgrid-htable").addClass("table table-bordered table-hover");
      element.find(".ui-jqgrid-btable").addClass("table table-bordered table-striped");

      element.find(".ui-pg-div").removeClass().addClass("btn btn-sm btn-primary");
      element.find(".ui-icon.ui-icon-plus").removeClass().addClass("fa fa-plus");
      element.find(".ui-icon.ui-icon-pencil").removeClass().addClass("fa fa-pencil");
      element.find(".ui-icon.ui-icon-trash").removeClass().addClass("fa fa-trash-o");
      element.find(".ui-icon.ui-icon-search").removeClass().addClass("fa fa-search");
      element.find(".ui-icon.ui-icon-refresh").removeClass().addClass("fa fa-refresh");
      element.find(".ui-icon.ui-icon-disk").removeClass().addClass("fa fa-save").parent(".btn-primary").removeClass("btn-primary").addClass("btn-success");
      element.find(".ui-icon.ui-icon-cancel").removeClass().addClass("fa fa-times").parent(".btn-primary").removeClass("btn-primary").addClass("btn-danger");

      element.find(".ui-icon.ui-icon-seek-prev").wrap("<div class='btn btn-sm btn-default'></div>");
      element.find(".ui-icon.ui-icon-seek-prev").removeClass().addClass("fa fa-backward");

      element.find(".ui-icon.ui-icon-seek-first").wrap("<div class='btn btn-sm btn-default'></div>");
      element.find(".ui-icon.ui-icon-seek-first").removeClass().addClass("fa fa-fast-backward");

      element.find(".ui-icon.ui-icon-seek-next").wrap("<div class='btn btn-sm btn-default'></div>");
      element.find(".ui-icon.ui-icon-seek-next").removeClass().addClass("fa fa-forward");

      element.find(".ui-icon.ui-icon-seek-end").wrap("<div class='btn btn-sm btn-default'></div>");
      element.find(".ui-icon.ui-icon-seek-end").removeClass().addClass("fa fa-fast-forward");

      $(window).on('resize.jqGrid', function() {
        table.jqGrid('setGridWidth', $("#content").width());
      });


      $compile(element.contents())(scope);
    }
  }
});
"use strict";

angular.module('SmartAdmin.Layout').directive('fullScreen', function(){
  return {
    restrict: 'A',
    link: function(scope, element){
      var $body = $('body');
      var toggleFullSceen = function(e){
        if (!$body.hasClass("full-screen")) {
          $body.addClass("full-screen");
          if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen();
          } else if (document.documentElement.mozRequestFullScreen) {
            document.documentElement.mozRequestFullScreen();
          } else if (document.documentElement.webkitRequestFullscreen) {
            document.documentElement.webkitRequestFullscreen();
          } else if (document.documentElement.msRequestFullscreen) {
            document.documentElement.msRequestFullscreen();
          }
        } else {
          $body.removeClass("full-screen");
          if (document.exitFullscreen) {
            document.exitFullscreen();
          } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
          } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
          }
        }
      };

      element.on('click', toggleFullSceen);

    }
  }
});
"use strict";

angular.module('SmartAdmin.Layout').directive('minifyMenu', function(){
  return {
    restrict: 'A',
    link: function(scope, element){
      var $body = $('body');
      var minifyMenu = function() {
        if (!$body.hasClass("menu-on-top")) {
          $body.toggleClass("minified");
          $body.removeClass("hidden-menu");
          $('html').removeClass("hidden-menu-mobile-lock");
        }
      };

      element.on('click', minifyMenu);
    }
  }
})
'use strict';

angular.module('SmartAdmin.Layout').directive('reloadState', function ($rootScope) {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('reload-state data-reload-state');
      tElement.on('click', function (e) {
        $rootScope.$state.transitionTo($rootScope.$state.current, $rootScope.$stateParams, {
          reload: true,
          inherit: false,
          notify: true
        });
        e.preventDefault();
      })
    }
  }
});

"use strict";

angular.module('SmartAdmin.Layout').directive('resetWidgets', function($state){

  return {
    restrict: 'A',
    link: function(scope, element){
      element.on('click', function(){
        $.SmartMessageBox({
          title : "<i class='fa fa-refresh' style='color:green'></i> Clear Local Storage",
          content : "Would you like to RESET all your saved widgets and clear LocalStorage?1",
          buttons : '[No][Yes]'
        }, function(ButtonPressed) {
          if (ButtonPressed == "Yes" && localStorage) {
            localStorage.clear();
            location.reload()
          }
        });

      });
    }
  }

});

'use strict';

angular.module('SmartAdmin.Layout').directive('searchMobile', function () {
  return {
    restrict: 'A',
    compile: function (element, attributes) {
      element.removeAttr('search-mobile data-search-mobile');

      element.on('click', function (e) {
        $('body').addClass('search-mobile');
        e.preventDefault();
      });

      $('#cancel-search-js').on('click', function (e) {
        $('body').removeClass('search-mobile');
        e.preventDefault();
      });
    }
  }
});
"use strict";

angular.module('SmartAdmin.Layout').directive('toggleMenu', function(){
  return {
    restrict: 'A',
    link: function(scope, element){
      var $body = $('body');

      var toggleMenu = function(){
        if (!$body.hasClass("menu-on-top")){
          $('html').toggleClass("hidden-menu-mobile-lock");
          $body.toggleClass("hidden-menu");
          $body.removeClass("minified");
        } else if ( $body.hasClass("menu-on-top") && $body.hasClass("mobile-view-activated") ) {
          $('html').toggleClass("hidden-menu-mobile-lock");
          $body.toggleClass("hidden-menu");
          $body.removeClass("minified");
        }
      };

      element.on('click', toggleMenu);

      scope.$on('requestToggleMenu', function(){
        toggleMenu();
      });
    }
  }
});
'use strict';

angular.module('SmartAdmin.Layout').directive('bigBreadcrumbs', function () {
  return {
    restrict: 'EA',
    replace: true,
    template: '<div><h1 class="page-title txt-color-blueDark"></h1></div>',
    scope: {
      items: '=',
      icon: '@'
    },
    link: function (scope, element) {
      var first = _.first(scope.items);

      var icon = scope.icon || 'home';
      element.find('h1').append('<i class="fa-fw fa fa-' + icon + '"></i> ' + first);
      _.rest(scope.items).forEach(function (item) {
        element.find('h1').append(' <span>> ' + item + '</span>')
      })
    }
  }
});

'use strict';

angular.module('SmartAdmin.Layout').directive('dismisser', function () {
  return {
    restrict: 'A',
    compile: function (element) {
      element.removeAttr('dismisser data-dissmiser')
      var closer = '<button class="close">&times;</button>';
      element.prepend(closer);
      element.on('click', '>button.close', function(){
        element.fadeOut('fast',function(){ $(this).remove(); });

      })
    }
  }
});
'use strict';

angular.module('SmartAdmin.Layout').directive('hrefVoid', function () {
  return {
    restrict: 'A',
    link: function (scope, element, attributes) {
      element.attr('href','#');
      element.on('click', function(e){
        e.preventDefault();
        e.stopPropagation();
      })
    }
  }
});
'use strict';

/*
 * Directive for toggling a ng-model with a button
 * Source: https://gist.github.com/aeife/9374784
 */

angular.module('SmartAdmin.Layout').directive('radioToggle', function ($log) {
  return {
    scope: {
      model: "=ngModel",
      value: "@value"
    },
    link: function(scope, element, attrs) {

      element.parent().on('click', function() {
        scope.model = scope.value;
        scope.$apply();
      });
    }
  }
});
/**
 * DETECT MOBILE DEVICES
 * Description: Detects mobile device - if any of the listed device is
 *
 * detected class is inserted to <tElement>.
 *
 *  (so far this is covering most hand held devices)
 */
'use strict';

angular.module('SmartAdmin.Layout').directive('smartDeviceDetect', function () {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-device-detect data-smart-device-detect');

      var isMobile = (/iphone|ipad|ipod|android|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()));

      tElement.toggleClass('desktop-detected', !isMobile);
      tElement.toggleClass('mobile-detected', isMobile);


    }
  }
});
/**
 *
 * Description: Directive utilizes FastClick library.
 *
 *
 * FastClick is a simple, easy-to-use library for eliminating the
 * 300ms delay between a physical tap and the firing of a click event on mobile browsers.
 * FastClick doesn't attach any listeners on desktop browsers.
 * @link: https://github.com/ftlabs/fastclick
 *
 * On mobile devices 'needsclick' class is attached to <tElement>
 *
 */


'use strict';

angular.module('SmartAdmin.Layout').directive('smartFastClick', function () {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-fast-click data-smart-fast-click');

      FastClick.attach(tElement);

      if(!FastClick.notNeeded())
        tElement.addClass('needsclick')
    }
  }
});

'use strict';

angular.module('SmartAdmin.Layout').directive('smartFitAppView', function ($rootScope, SmartCss) {
  return {
    restrict: 'A',
    compile: function (element, attributes) {
      element.removeAttr('smart-fit-app-view data-smart-fit-app-view leading-y data-leading-y');

      var leadingY = attributes.leadingY ? parseInt(attributes.leadingY) : 0;

      var selector = attributes.smartFitAppView;

      if(SmartCss.appViewSize && SmartCss.appViewSize.height){
        var height =  SmartCss.appViewSize.height - leadingY < 252 ? 252 :  SmartCss.appViewSize.height - leadingY;
        SmartCss.add(selector, 'height', height+'px');
      }

      var listenerDestroy = $rootScope.$on('$smartContentResize', function (event, data) {
        var height = data.height - leadingY < 252 ? 252 : data.height - leadingY;
        SmartCss.add(selector, 'height', height+'px');
      });

      element.on('$destroy', function () {
        listenerDestroy();
        SmartCss.remove(selector, 'height');
      });


    }
  }
});

"use strict";

angular.module('SmartAdmin.Layout').directive('smartInclude', function () {
    return {
      replace: true,
      restrict: 'A',
      templateUrl: function (element, attr) {
        return attr.smartInclude;
      },
      compile: function(element){
        element[0].className = element[0].className.replace(/placeholder[^\s]+/g, '');
      }
    };
  }
);


'use strict';

angular.module('SmartAdmin.Layout').directive('smartLayout', function ($rootScope, $timeout, $interval, $q, SmartCss, APP_CONFIG) {

  var _debug = 0;

  function getDocHeight() {
    var D = document;
    return Math.max(
      D.body.scrollHeight, D.documentElement.scrollHeight,
      D.body.offsetHeight, D.documentElement.offsetHeight,
      D.body.clientHeight, D.documentElement.clientHeight
    );
  }

  var initialized = false,
    initializedResolver = $q.defer();
  initializedResolver.promise.then(function () {
    initialized = true;
  });

  var $window = $(window),
    $document = $(document),
    $html = $('html'),
    $body = $('body'),
    $navigation ,
    $menu,
    $ribbon,
    $footer,
    $contentAnimContainer;


  (function cacheElements() {
    $navigation = $('#header');
    $menu = $('#left-panel');
    $ribbon = $('#ribbon');
    $footer = $('.page-footer');
    if (_.every([$navigation, $menu, $ribbon, $footer], function ($it) {
        return angular.isNumber($it.height())
      })) {
      initializedResolver.resolve();
    } else {
      $timeout(cacheElements, 100);
    }
  })();

  (function applyConfigSkin(){
    if(APP_CONFIG.smartSkin){
      $body.removeClass(_.pluck(APP_CONFIG.skins, 'name').join(' '));
      $body.addClass(APP_CONFIG.smartSkin);
    }
  })();


  return {
    priority: 2014,
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-layout data-smart-layout');

      var appViewHeight = 0 ,
        appViewWidth = 0,
        calcWidth,
        calcHeight,
        deltaX,
        deltaY;

      var forceResizeTrigger = false;

      function resizeListener() {

//                    full window height appHeight = Math.max($menu.outerHeight() - 10, getDocHeight() - 10);

        var menuHeight = $body.hasClass('menu-on-top') && $menu.is(':visible') ? $menu.height() : 0;
        var menuWidth = !$body.hasClass('menu-on-top') && $menu.is(':visible') ? $menu.width() + $menu.offset().left : 0;

        var $content = $('#content');
        var contentXPad = $content.outerWidth(true) - $content.width();
        var contentYPad = $content.outerHeight(true) - $content.height();


        calcWidth = $window.width() - menuWidth - contentXPad;
        calcHeight = $window.height() - menuHeight - contentYPad - $navigation.height() - $ribbon.height() - $footer.height();

        deltaX = appViewWidth - calcWidth;
        deltaY = appViewHeight - calcHeight;
        if (Math.abs(deltaX) || Math.abs(deltaY) || forceResizeTrigger) {

          //console.log('exec', calcWidth, calcHeight);
          $rootScope.$broadcast('$smartContentResize', {
            width: calcWidth,
            height: calcHeight,
            deltaX: deltaX,
            deltaY: deltaY
          });
          appViewWidth = calcWidth;
          appViewHeight = calcHeight;
          forceResizeTrigger = false;
        }
      }


      var looping = false;
      $interval(function () {
        if (looping) loop();
      }, 300);

      var debouncedRun = _.debounce(function () {
        run(300)
      }, 300);

      function run(delay) {
        initializedResolver.promise.then(function () {
          attachOnResize(delay);
        });
      }

      run(10);

      function detachOnResize() {
        looping = false;
      }

      function attachOnResize(delay) {
        $timeout(function () {
          looping = true;
        }, delay);
      }

      function loop() {
        $body.toggleClass('mobile-view-activated', $window.width() < 979);

        if ($window.width() < 979)
          $body.removeClass('minified');

        resizeListener();
      }

      function handleHtmlId(toState) {
        if (toState.data && toState.data.htmlId) $html.attr('id', toState.data.htmlId);
        else $html.removeAttr('id');
      }

      $rootScope.$on('$stateChangeStart', function (event, toState, toParams, fromState, fromParams) {
        //console.log(1, '$stateChangeStart', event, toState, toParams, fromState, fromParams);

        handleHtmlId(toState);
        detachOnResize();
      });

      // initialized with 1 cause we came here with one $viewContentLoading request
      var viewContentLoading = 1;
      $rootScope.$on('$viewContentLoading', function (event, viewConfig) {
        //console.log(2, '$viewContentLoading', event, viewConfig);
        viewContentLoading++;
      });

      $rootScope.$on('$stateChangeSuccess', function (event, toState, toParams, fromState, fromParams) {
        //console.log(3, '$stateChangeSuccess', event, toState, toParams, fromState, fromParams);
        forceResizeTrigger = true;
      });

      $rootScope.$on('$viewContentLoaded', function (event) {
        //console.log(4, '$viewContentLoaded', event);
        viewContentLoading--;

        if (viewContentLoading == 0 && initialized) {
          debouncedRun();
        }
      });
    }
  }
});