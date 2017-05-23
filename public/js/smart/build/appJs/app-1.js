'use strict';



var appConfig = window.appConfig || {};

appConfig.menu_speed = 200;

// appConfig.smartSkin = "fixed-navigation fixed-header fixed-page-footer";
appConfig.smartSkin = "smart-style-7";

appConfig.skins = [
  {name: "smart-style-0",
    logo: "/css/smart/styles/img/losgo.png",
    class: "btn btn-block btn-xs txt-color-white margin-right-5",
    style: "background-color:#4E463F;",
    label: "Smart Default"},

  {name: "smart-style-1",
    logo: "styles/img/logo-white.png",
    class: "btn btn-block btn-xs txt-color-white",
    style: "background:#3A4558;",
    label: "Dark Elegance"},

  {name: "smart-style-2",
    logo: "styles/img/logo-blue.png",
    class: "btn btn-xs btn-block txt-color-darken margin-top-5",
    style: "background:#fff;",
    label: "Ultra Light"},

  {name: "smart-style-3",
    logo: "styles/img/logo-pale.png",
    class: "btn btn-xs btn-block txt-color-white margin-top-5",
    style: "background:#f78c40",
    label: "Google Skin"},

  {name: "smart-style-4",
    logo: "styles/img/logo-pale.png",
    class: "btn btn-xs btn-block txt-color-white margin-top-5",
    style: "background: #bbc0cf; border: 1px solid #59779E; color: #17273D !important;",
    label: "PixelSmash"},

  {name: "smart-style-5",
    logo: "styles/img/logo-pale.png",
    class: "btn btn-xs btn-block txt-color-white margin-top-5",
    style: "background: rgba(153, 179, 204, 0.2); border: 1px solid rgba(121, 161, 221, 0.8); color: #17273D !important;",
    label: "Glass"},

  {name: "smart-style-6",
    logo: "styles/img/logo-pale.png",
    class: "btn btn-xs btn-block txt-color-white margin-top-5",
    style: "background: #2196F3; border: 1px solid rgba(121, 161, 221, 0.8); color: #FFF !important;",
    beta: true,
    label: "MaterialDesign"
  },

  {name: "smart-style-7",
//     logo: "styles/img/logo-white.png",
    logo: "styles/img/logo-white-ltrs.png",
    class: "btn btn-xs btn-block txt-color-white margin-top-5",
    style: "background:#3A4558;",
    label: "SilverIP"
  }


];
appConfig.skins = [
  {name: "smart-style-0",
    logo: "/css/smart/styles/img/losgo.png",
    class: "btn btn-block btn-xs txt-color-white margin-right-5",
    style: "background-color:#4E463F;",
    label: "Smart Default"},

  {name: "smart-style-1",
    logo: "styles/img/logo-white.png",
    class: "btn btn-block btn-xs txt-color-white",
    style: "background:#3A4558;",
    label: "Dark Elegance"},

  {name: "smart-style-2",
    logo: "styles/img/logo-blue.png",
    class: "btn btn-xs btn-block txt-color-darken margin-top-5",
    style: "background:#fff;",
    label: "Ultra Light"},

  {name: "smart-style-3",
    logo: "styles/img/logo-pale.png",
    class: "btn btn-xs btn-block txt-color-white margin-top-5",
    style: "background:#f78c40",
    label: "Google Skin"},

  {name: "smart-style-4",
    logo: "styles/img/logo-pale.png",
    class: "btn btn-xs btn-block txt-color-white margin-top-5",
    style: "background: #bbc0cf; border: 1px solid #59779E; color: #17273D !important;",
    label: "PixelSmash"},

  {name: "smart-style-5",
    logo: "styles/img/logo-pale.png",
    class: "btn btn-xs btn-block txt-color-white margin-top-5",
    style: "background: rgba(153, 179, 204, 0.2); border: 1px solid rgba(121, 161, 221, 0.8); color: #17273D !important;",
    label: "Glass"},

  {name: "smart-style-6",
    logo: "styles/img/logo-pale.png",
    class: "btn btn-xs btn-block txt-color-white margin-top-5",
    style: "background: #2196F3; border: 1px solid rgba(121, 161, 221, 0.8); color: #FFF !important;",
    beta: true,
    label: "MaterialDesign"
  },

  {name: "smart-style-7",
//     logo: "styles/img/logo-white.png",
    logo: "styles/img/logo-white-ltrs.png",
    class: "btn btn-xs btn-block txt-color-white margin-top-5",
    style: "background:#3A4558;",
    label: "SilverIP"
  }


];





appConfig.apiRootUrl = 'api';

window.appConfig = appConfig;

/*
 * END APP.appConfig
 */
'use strict';



$(function () {

  // moment.js default language
  moment.locale('en')

  angular.bootstrap(document, ['app']);

});

'use strict';

/**
 * @ngdoc overview
 * @name app [smartadminApp]
 * @description
 * # app [smartadminApp]
 *
 * Main module of the application.
 */

var app = angular.module('app', [
  'ngSanitize',
  'ngAnimate',
  'restangular',
  'ui.router',
  'ui.bootstrap',

  // Smartadmin Angular Common Module
  'SmartAdmin',

  // App
  'app.auth',
  'app.layout',
  'app.chat',
  'app.dashboard',
  'app.calendar',
  'app.inbox',
  'app.graphs',
  'app.tables',
  'app.forms',
  'app.ui',
  'app.widgets',
  'app.maps',
  'app.appViews',
  'app.misc',
  'app.smartAdmin',
  'app.eCommerce',
  'app.buildings',
  'app.customers',
  'app.network',
  'app.support',
  'app.admin',
  'app.customershome',
  'app.warpol',
  'app.tech-schedule',
  'app.tech-appointments',
  'app.dummyapp',
  'app.reports'
])
  .config(function ($provide, $httpProvider, RestangularProvider) {


    // Intercept http calls.


  })
  .constant('APP_CONFIG', window.appConfig)

  .run(function ($rootScope
    , $state, $stateParams
  ) {
    $rootScope.$state = $state;
    $rootScope.$stateParams = $stateParams;
    // editableOptions.theme = 'bs3';

  });



"use strict";


angular.module('app.appViews', ['ui.router'])
  .config(function ($stateProvider) {

    $stateProvider
      .state('app.appViews', {
        abstract: true,
        data: {
          title: 'App views'
        }
      })

      .state('app.appViews.projects', {
        url: '/projects',
        data: {
          title: 'Projects'
        },
        views: {
          "content@app": {
            templateUrl: 'app/app-views/views/project-list.html',
            controller: 'ProjectsDemoCtrl',
            resolve: {
              projects: function($http, APP_CONFIG){
                return $http.get(APP_CONFIG.apiRootUrl + '/project-list.json')
              }
            }
          }
        },
        resolve: {
          scripts: function(lazyScript){
            return lazyScript.register([
              'build/vendor.datatables.js'
            ]);
          }
        }
      })

      .state('app.appViews.blogDemo', {
        url: '/blog',
        data: {
          title: 'Blog'
        },
        views: {
          "content@app": {
            templateUrl: 'app/app-views/views/blog-demo.html'
          }
        }
      })

      .state('app.appViews.galleryDemo', {
        url: '/gallery',
        data: {
          title: 'Gallery'
        },
        views: {
          "content@app": {
            templateUrl: 'app/app-views/views/gallery-demo.html'
          }
        },
        resolve: {
          scripts: function(lazyScript){
            return lazyScript.register([
              'smartadmin-plugin/legacy/superbox/superbox.min.js'
            ]);
          }
        }
      })

      .state('app.appViews.forumDemo', {
        url: '/forum',
        data: {
          title: 'Forum'
        },
        views: {
          "content@app": {
            templateUrl: 'app/app-views/views/forum-demo.html'
          }
        }
      })

      .state('app.appViews.forumTopicDemo', {
        url: '/forum-topic',
        data: {
          title: 'Forum Topic'
        },
        views: {
          "content@app": {
            templateUrl: 'app/app-views/views/forum-topic-demo.html'
          }
        }
      })

      .state('app.appViews.forumPostDemo', {
        url: '/forum-post',
        data: {
          title: 'Forum Post'
        },
        views: {
          "content@app": {
            templateUrl: 'app/app-views/views/forum-post-demo.html'
          }
        },
        resolve: {
          scripts: function(lazyScript){
            return lazyScript.register([
              '/js/smart/build/vendor.ui.js'
            ]);
          }
        }
      })


      .state('app.appViews.profileDemo', {
        url: '/profile',
        data: {
          title: 'Profile'
        },
        views: {
          "content@app": {
            templateUrl: '/views/userprofile.html',
            controller: 'userProfileController'
          }
        }
      })


      .state('app.appViews.timelineDemo', {
        url: '/timeline',
        data: {
          title: 'Timeline'
        },
        views: {
          "content@app": {
            templateUrl: 'app/app-views/views/timeline-demo.html'
          }
        }
      })
  });

"use strict";

angular.module('app.auth', [
  'ui.router'
//        ,
//        'ezfb',
//        'googleplus'
]);



"use strict";


// angular
//   .module('app.calendar', ['ngResource','ui.router'])
//   .config(function ($stateProvider) {
//
//     $stateProvider
//       .state('app.calendar', {
//         url: '/calendar',
//         views: {
//           content: {
//             templateUrl: 'app/calendar/views/calendar.tpl.html'
//           }
//         },
//         data:{
//           title: 'Calendar'
//         }
//       });
//   });



'use strict';

angular.module('app.dashboard', [
  'ui.router',
  'ngResource'
])

  .config(function ($stateProvider) {
    $stateProvider
      .state('app.dashboard', {
        url: '/dashboard',
        views: {
          "content@app": {
            controller: 'DashboardCtrl',
            templateUrl: 'app/dashboard/dashboard.html'
          }
        },
        data:{
          title: 'Dashboard'
        }
      })
      .state('app.dashboard-social', {
        url: '/dashboard-social',
        views: {
          "content@app": {
            templateUrl: 'app/dashboard/social-wall.html'
          }
        },
        data:{
          title: 'Dashboard Social'
        }
      });
  });

"use strict";


angular.module('app.eCommerce', ['ui.router'])
  .config(function ($stateProvider) {

    $stateProvider
      .state('app.eCommerce', {
        abstract: true,
        data: {
          title: 'E-Commerce'
        }
      })

      .state('app.eCommerce.orders', {
        url: '/e-commerce/orders',
        data: {
          title: 'Orders'
        },
        views: {
          "content@app": {
            templateUrl: 'app/e-commerce/views/orders.html',
            controller: 'OrdersDemoCtrl',
            resolve: {
              orders: function($http, APP_CONFIG){
                return $http.get(APP_CONFIG.apiRootUrl + '/e-commerce/orders.json')
              }
            }
          }
        },
        resolve: {
          scripts: function(lazyScript){
            return lazyScript.register([
              'build/vendor.datatables.js'
            ]);
          }
        }
      })

      .state('app.eCommerce.products', {
        url: '/e-commerce/products-view',
        data: {
          title: 'Products View'
        },
        views: {
          "content@app": {
            templateUrl: 'app/e-commerce/views/products.html'
          }
        }
      })

      .state('app.eCommerce.detail', {
        url: '/e-commerce/products-detail',
        data: {
          title: 'Products Detail'
        },
        views: {
          "content@app": {
            templateUrl: 'app/e-commerce/views/detail.html'
          }
        }
      })
  });

"use strict";


angular.module('app.forms', ['ui.router'])


angular.module('app.forms').config(function ($stateProvider) {

  $stateProvider
    .state('app.form', {
      abstract: true,
      data: {
        title: 'Forms'
      }
    })

    .state('app.form.elements', {
      url: '/form/elements',
      data: {
        title: 'Form Elements'
      },
      views: {
        "content@app": {
          templateUrl: 'app/forms/views/form-elements.html'
        }
      }
    })

    .state('app.form.layouts', {
      url: '/form/layouts',
      data: {
        title: 'Form Layouts'
      },
      views: {
        "content@app": {
          controller: 'FormLayoutsCtrl',
          templateUrl: 'app/forms/views/form-layouts/form-layouts-demo.html'
        }
      }
    })

    .state('app.form.validation', {
      url: '/form/validation',
      data: {
        title: 'Form Validation'
      },
      views: {
        "content@app": {
          templateUrl: 'app/forms/views/form-validation.html'
        }
      }
    })

    .state('app.form.bootstrapForms', {
      url: '/form/bootstrap-forms',
      data: {
        title: 'Bootstrap Forms'
      },
      views: {
        "content@app": {
          templateUrl: 'app/forms/views/bootstrap-forms.html'
        }
      }
    })

    .state('app.form.bootstrapValidation', {
      url: '/form/bootstrap-validation',
      data: {
        title: 'Bootstrap Validation'
      },
      views: {
        "content@app": {
          templateUrl: 'app/forms/views/bootstrap-validation.html'
        }
      },
      resolve: {
        srcipts: function(lazyScript){
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ])

        }
      }
    })

    .state('app.form.plugins', {
      url: '/form/plugins',
      data: {
        title: 'Form Plugins'
      },
      views: {
        "content@app": {
          templateUrl: 'app/forms/views/form-plugins.html',
          controller: 'FormPluginsCtrl'
        }
      },
      resolve: {
        srcipts: function(lazyScript){
          return lazyScript.register([
            "/js/smart/build/vendor.ui.js"
          ])

        }
      }
    })
    .state('app.form.wizards', {
      url: '/form/wizards',
      data: {
        title: 'Wizards'
      },
      views: {
        "content@app": {
          templateUrl: 'app/forms/views/form-wizards.html',
          controller: 'FormWizardCtrl'
        }
      },
      resolve: {
        srcipts: function(lazyScript){
          return lazyScript.register([
            "/js/smart/build/vendor.ui.js"
          ])

        }
      }
    })
    .state('app.form.editors', {
      url: '/form/editors',
      data: {
        title: 'Editors'
      },
      views: {
        "content@app": {
          templateUrl: 'app/forms/views/form-editors.html'
        }
      },
      resolve: {
        scripts: function(lazyScript){
          return lazyScript.register([
            "/js/smart/build/vendor.ui.js"
          ])
        }
      }
    })
    .state('app.form.dropzone', {
      url: '/form/dropzone',
      data: {
        title: 'Dropzone'
      },
      views: {
        "content@app": {
          templateUrl: 'app/forms/views/dropzone.html',
          controller: function($scope){
            $scope.dropzoneConfig = {
              'options': { // passed into the Dropzone constructor
                'url': '/api/plug'
              },
              'eventHandlers': {
                'sending': function (file, xhr, formData) {
                },
                'success': function (file, response) {
                }
              }
            };
          }
        }
      },
      resolve: {
        scripts: function(lazyScript){
          return lazyScript.register('/js/smart/build/vendor.ui.js')
        }
      }
    })
    .state('app.form.imageEditor', {
      url: '/form/image-editor',
      data: {
        title: 'Image Editor'
      },
      views: {
        "content@app": {
          templateUrl: 'app/forms/views/image-editor.html',
          controller: 'ImageEditorCtrl'
        }
      },
      resolve: {
        scripts: function(lazyScript){
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ])
        }
      }
    })


});
"use strict";

angular.module('app.graphs', [
  'ui.router'
]).config(function ($stateProvider) {
  $stateProvider
    .state('app.graphs', {
      abstract: true,
      data: {
        title: 'Graphs'
      }
    })

    .state('app.graphs.flot', {
      url: '/graphs/flot',
      data: {
        title: 'Flot Charts'
      },
      views: {
        "content@app": {
          controller: 'FlotCtrl',
          templateUrl: "app/graphs/views/flot-charts.html"
        }
      }
    })

    .state('app.graphs.morris', {
      url: '/graphs/morris',
      data: {
        title: 'Morris Charts'
      },
      views: {
        "content@app": {
          templateUrl: "app/graphs/views/morris-charts.html"
        }
      },
      resolve: {
        scripts: function(lazyScript){
          return lazyScript.register([
            'build/vendor.graphs.js'
          ]);
        }
      }
    })

    .state('app.graphs.sparkline', {
      url: '/graphs/sparkline',
      data: {
        title: 'Sparklines'
      },
      views: {
        "content@app": {
          templateUrl: "app/graphs/views/sparkline.html"
        }
      }
    })
    .state('app.graphs.easyPieCharts', {
      url: '/graphs/easy-pie',
      data: {
        title: 'Easy Pie Charts'
      },
      views: {
        "content@app": {
          templateUrl: "app/graphs/views/easy-pie-charts.html"
        }
      }
    })

    .state('app.graphs.dygraphs', {
      url: '/graphs/dygraphs',
      data: {
        title: 'Dygraphs Charts'
      },
      views: {
        "content@app": {
          templateUrl: "app/graphs/views/dygraphs-charts.html"
        }
      },
      resolve: {
        scripts: function(lazyScript){
          return lazyScript.register([
            'build/vendor.graphs.js'
          ]);
        }
      }
    })

    .state('app.graphs.chartjs', {
      url: '/graphs/chartjs',
      data: {
        title: 'Chart.js'
      },
      views: {
        "content@app": {
          templateUrl: "app/graphs/views/chartjs-charts.html"
        }
      },
      resolve: {
        scripts: function(lazyScript){
          return lazyScript.register([
            'build/vendor.graphs.js'
          ]);
        }
      }
    })


    .state('app.graphs.highchartTables', {
      url: '/graphs/highchart-tables',
      data: {
        title: 'Highchart Tables'
      },
      views: {
        "content@app": {
          templateUrl: "app/graphs/views/highchart-tables.html"
        }
      }
    })
});
'use strict';

angular.module('app.inbox', [
  'ui.router',
  'ngResource'
])
  .config(function ($stateProvider) {

    $stateProvider
      .state('app.inbox', {
        url: '/inbox',
        data: {
          title: 'Inbox'
        },
        views: {
          content: {
            templateUrl: 'app/inbox/views/inbox-layout.html',
            controller: function ($scope, config) {
              $scope.config = config.data;
              $scope.deleteSelected = function(){
                $scope.$broadcast('$inboxDeleteMessages')
              }
            },
            controllerAs: 'inboxCtrl',
            resolve: {
              config: function (InboxConfig) {
                return InboxConfig;
              }
            }
          }
        }

      })
      .state('app.inbox.folder', {
        url: '/:folder',
        views: {
          inbox: {
            templateUrl: 'app/inbox/views/inbox-folder.html',
            controller: function ($scope, messages, $stateParams) {
              $scope.$parent.selectedFolder = _.find($scope.$parent.config.folders, {key: $stateParams.folder});
              $scope.messages = messages;

              $scope.$on('$inboxDeleteMessages', function(event){
                angular.forEach($scope.messages, function(message, idx){
                  if(message.selected){
                    message.$delete(function(){
                      $scope.messages.splice(idx, 1);
                    })
                  }
                });
              });

            },
            resolve: {
              messages: function (InboxMessage, $stateParams) {
                return InboxMessage.query({folder: $stateParams.folder});
              }
            }
          }
        }
      })
      .state('app.inbox.folder.detail', {
        url: '/detail/:message',
        views: {
          "inbox@app.inbox": {
            templateUrl: 'app/inbox/views/inbox-detail.html',
            controller: function ($scope, message) {
              $scope.message = message;
            },
            resolve: {
              message: function (InboxMessage, $stateParams) {
                return InboxMessage.get({id: $stateParams.message})
              }
            }
          }
        }
      })
      .state('app.inbox.folder.replay', {
        url: '/replay/:message',
        views: {
          "inbox@app.inbox": {
            templateUrl: 'app/inbox/views/inbox-replay.html',
            controller: function ($scope, $timeout, $state, replayTo) {
              $scope.replayTo = replayTo;
              $scope.sending = false;
              $scope.send = function(){
                $scope.sending = true;
                $timeout(function(){
                  $state.go('app.inbox.folder')
                }, 1000);
              }
            },
            controllerAs: 'replayCtrl',
            resolve: {
              replayTo: function (InboxMessage, $stateParams) {
                return InboxMessage.get({id: $stateParams.message})
              }
            }
          }
        }
      })
      .state('app.inbox.folder.compose', {
        url: '/compose',
        views: {
          "inbox@app.inbox": {
            templateUrl: 'app/inbox/views/inbox-compose.html',
            controller: function ($scope, $timeout, $state) {
              $scope.sending = false;
              $scope.send = function(){
                $scope.sending = true;
                $timeout(function(){
                  $state.go('app.inbox.folder')
                }, 1000);
              }
            },
            controllerAs: 'composeCtrl'
          }
        }
      });
  });
"use strict";


angular.module('app.layout', ['ui.router'])

  .config(function ($stateProvider, $urlRouterProvider) {


    $stateProvider
      .state('app', {
        abstract: true,
        views: {
          root: {
            templateUrl: 'app/layout/layout.tpl.html'
          }
        }
      });
    $urlRouterProvider.otherwise('/dashboard');

  })

