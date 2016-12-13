
"use strict";

angular.module('app.inbox').directive('messageLabels', function (InboxConfig) {
  return {
    replace: true,
    restrict: 'AE',
    link: function (scope, element) {

      if (scope.message.labels && scope.message.labels.length) {
        InboxConfig.success(function (config) {
          var html = _.map(scope.message.labels, function (label) {
            return '<span class="label bg-color-'+config.labels[label].color +'">' + config.labels[label].name + '</span>';
          }).join('');
          element.replaceWith(html);
        });

      } else {
        element.replaceWith('');
      }
    }
  }
});
"use strict";

angular.module('app.inbox').directive('unreadMessagesCount', function(InboxConfig){
  return {
    restrict: 'A',
    link: function(scope, element){
      InboxConfig.success(function(config){
        element.html(_.find(config.folders, {key: 'inbox'}).unread);
      })
    }
  }
});
"use strict";

angular.module('app.inbox').factory('InboxConfig', function($http, APP_CONFIG){
  return $http.get(APP_CONFIG.apiRootUrl + '/inbox.json');
})
"use strict";

angular.module('app.inbox').factory('InboxMessage', function($resource, APP_CONFIG){
  var InboxMessage = $resource(APP_CONFIG.apiRootUrl + '/messages.json/:id', {'id': '@_id'}, {
    get:{
      url: APP_CONFIG.apiRootUrl + '/message.json',
      isArray: false
    }
  });

  _.extend(InboxMessage.prototype, {
    selected: false,
    hasAttachments: function(){
      return (_.isArray(this.attachments) && this.attachments.length)
    },
    fullAttachmentsTootlip: function(){
      return 'FILES: ' + _.pluck(this.attachments, 'name').join(', ');
    },
    getBodyTeaser: function(){
      var clearBody  = this.body.replace(/<[^<>]+?>/gm, ' ').replace(/(\s{2}|\n)/gm, ' ');

      var teaserMaxLength = 55 - this.subject.length;

      return clearBody.length > teaserMaxLength ? clearBody.substring(0, teaserMaxLength) + '...' : clearBody;
    }
  });

  return InboxMessage;

});
"use strict";

angular.module('app').controller("LanguagesCtrl",  function LanguagesCtrl($scope, $rootScope, $log, Language){
  $rootScope.lang = {};
  Language.getLanguages(function(data){

    $rootScope.currentLanguage = data[0];

    $rootScope.languages = data;

    Language.getLang(data[0].key,function(data){

      $rootScope.lang = data;
    });

  });

  $scope.selectLanguage = function(language){
    $rootScope.currentLanguage = language;

    Language.getLang(language.key,function(data){

      $rootScope.lang = data;

    });
  }

  $rootScope.getWord = function(key){
    if(angular.isDefined($rootScope.lang[key])){
      return $rootScope.lang[key];
    }
    else {
      return key;
    }
  }

});
"use strict";

angular.module('app').factory('Language', function($http, APP_CONFIG){

  function getLanguage(key, callback) {

    $http.get(APP_CONFIG.apiRootUrl + '/langs/' + key + '.json').success(function(data){

      callback(data);

    }).error(function(){

      $log.log('Error');
      callback([]);

    });

  }

  function getLanguages(callback) {

    $http.get(APP_CONFIG.apiRootUrl + '/languages.json').success(function(data){

      callback(data);

    }).error(function(){

      $log.log('Error');
      callback([]);

    });

  }

  return {
    getLang: function(type, callback) {
      getLanguage(type, callback);
    },
    getLanguages:function(callback){
      getLanguages(callback);
    }
  }

});
"use strict";

angular.module('app').directive('languageSelector', function(Language){
  return {
    restrict: "EA",
    replace: true,
    templateUrl: "app/layout/language/language-selector.tpl.html",
    scope: true
  }
});
"use strict";

angular.module('app').directive('toggleShortcut', function($log,$timeout) {

  var initDomEvents = function($element){

    var shortcut_dropdown = $('#shortcut');

    $element.on('click',function(){

      if (shortcut_dropdown.is(":visible")) {
        shortcut_buttons_hide();
      } else {
        shortcut_buttons_show();
      }

    })

    shortcut_dropdown.find('a').click(function(e) {
      e.preventDefault();
      window.location = $(this).attr('href');
      setTimeout(shortcut_buttons_hide, 300);
    });



    // SHORTCUT buttons goes away if mouse is clicked outside of the area
    $(document).mouseup(function(e) {
      if (shortcut_dropdown && !shortcut_dropdown.is(e.target) && shortcut_dropdown.has(e.target).length === 0) {
        shortcut_buttons_hide();
      }
    });

    // SHORTCUT ANIMATE HIDE
    function shortcut_buttons_hide() {
      shortcut_dropdown.animate({
        height : "hide"
      }, 300, "easeOutCirc");
      $('body').removeClass('shortcut-on');

    }

    // SHORTCUT ANIMATE SHOW
    function shortcut_buttons_show() {
      shortcut_dropdown.animate({
        height : "show"
      }, 200, "easeOutCirc");
      $('body').addClass('shortcut-on');
    }
  }

  var link = function($scope,$element){
    $timeout(function(){
      initDomEvents($element);
    });
  }

  return{
    restrict:'EA',
    link:link
  }
})
'use strict';

angular.module('app.maps').controller('MapsDemoCtrl',
  function ($scope, $http, $q, SmartMapStyle, uiGmapGoogleMapApi) {


    $scope.styles = SmartMapStyle.styles;

    $scope.setType = function (key) {
      SmartMapStyle.getMapType(key).then(function (type) {
        $scope.map.control.getGMap().mapTypes.set(key, type);
        $scope.map.control.getGMap().setMapTypeId(key);
      });
      $scope.currentType = key;
    };


    $scope.map = {
      center: {latitude: 45, longitude: -73},
      zoom: 8,
      control: {}
    };


    uiGmapGoogleMapApi.then(function (maps) {

    })
      .then(function () {
        return SmartMapStyle.getMapType('colorful')
      }).then(function () {
      $scope.setType('colorful')
    });



  });
"use strict";


angular.module('app.maps').factory('SmartMapStyle', function ($q, $http, APP_CONFIG) {

  var styles = {
    'colorful': { name: 'Colorful', url: APP_CONFIG.apiRootUrl + '/maps/colorful.json'},
    'greyscale': { name: 'greyscale', url: APP_CONFIG.apiRootUrl + '/maps/greyscale.json'},
    'metro': { name: 'metro', url: APP_CONFIG.apiRootUrl + '/maps/metro.json'},
    'mono-color': { name: 'mono-color', url: APP_CONFIG.apiRootUrl + '/maps/mono-color.json'},
    'monochrome': { name: 'monochrome', url: APP_CONFIG.apiRootUrl + '/maps/monochrome.json'},
    'nightvision': { name: 'Nightvision', url: APP_CONFIG.apiRootUrl + '/maps/nightvision.json'},
    'nightvision-highlight': { name: 'nightvision-highlight', url: APP_CONFIG.apiRootUrl + '/maps/nightvision-highlight.json'},
    'old-paper': { name: 'Old Paper', url: APP_CONFIG.apiRootUrl + '/maps/old-paper.json'}
  };


  function getMapType(key){
    var keyData = styles[key];

    if(!keyData.cache){
      keyData.cache = createMapType(keyData)
    }

    return keyData.cache;
  }

  function createMapType(keyData){
    var dfd = $q.defer();
    $http.get(keyData.url).then(function(resp){
      var styleData = resp.data;
      var type = new google.maps.StyledMapType(styleData, {name: keyData.name})
      dfd.resolve(type);
    }, function(reason){
      console.error(reason);
      dfd.reject(reason);
    });

    return dfd.promise;
  }


  return {
    getMapType: getMapType,
    styles: styles
  }



});
/**
 * Created by griga on 2/9/16.
 */


angular.module('app.tables').controller('DatatablesCtrl', function(DTOptionsBuilder, DTColumnBuilder){
  console.log('DatatablesCtrl app.js-Controller');

  this.standardOptions = DTOptionsBuilder
    .fromSource('ttt/api/tables/datatables.standard.json')
    //Add Bootstrap compatibility
    .withDOM("<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>" +
      "t" +
      "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>")
    .withBootstrap();
  this.standardColumns = [
    DTColumnBuilder.newColumn('id').withClass('text-danger'),
    DTColumnBuilder.newColumn('name'),
    DTColumnBuilder.newColumn('phone'),
    DTColumnBuilder.newColumn('company'),
    DTColumnBuilder.newColumn('zip'),
    DTColumnBuilder.newColumn('city'),
    DTColumnBuilder.newColumn('date')
  ];


});
'use strict';

angular.module('app.tables').controller('JqGridCtrl', function ($scope) {
  $scope.gridData = {
    data: [
      {
        id: "1",
        date: "2007-10-01",
        name: "test",
        note: "note",
        amount: "200.00",
        tax: "10.00",
        total: "210.00"
      },
      {
        id: "2",
        date: "2007-10-02",
        name: "test2",
        note: "note2",
        amount: "300.00",
        tax: "20.00",
        total: "320.00"
      },
      {
        id: "3",
        date: "2007-09-01",
        name: "test3",
        note: "note3",
        amount: "400.00",
        tax: "30.00",
        total: "430.00"
      },
      {
        id: "4",
        date: "2007-10-04",
        name: "test",
        note: "note",
        amount: "200.00",
        tax: "10.00",
        total: "210.00"
      },
      {
        id: "5",
        date: "2007-10-05",
        name: "test2",
        note: "note2",
        amount: "300.00",
        tax: "20.00",
        total: "320.00"
      },
      {
        id: "6",
        date: "2007-09-06",
        name: "test3",
        note: "note3",
        amount: "400.00",
        tax: "30.00",
        total: "430.00"
      },
      {
        id: "7",
        date: "2007-10-04",
        name: "test",
        note: "note",
        amount: "200.00",
        tax: "10.00",
        total: "210.00"
      },
      {
        id: "8",
        date: "2007-10-03",
        name: "test2",
        note: "note2",
        amount: "300.00",
        tax: "20.00",
        total: "320.00"
      },
      {
        id: "9",
        date: "2007-09-01",
        name: "test3",
        note: "note3",
        amount: "400.00",
        tax: "30.00",
        total: "430.00"
      },
      {
        id: "10",
        date: "2007-10-01",
        name: "test",
        note: "note",
        amount: "200.00",
        tax: "10.00",
        total: "210.00"
      },
      {
        id: "11",
        date: "2007-10-02",
        name: "test2",
        note: "note2",
        amount: "300.00",
        tax: "20.00",
        total: "320.00"
      },
      {
        id: "12",
        date: "2007-09-01",
        name: "test3",
        note: "note3",
        amount: "400.00",
        tax: "30.00",
        total: "430.00"
      },
      {
        id: "13",
        date: "2007-10-04",
        name: "test",
        note: "note",
        amount: "200.00",
        tax: "10.00",
        total: "210.00"
      },
      {
        id: "14",
        date: "2007-10-05",
        name: "test2",
        note: "note2",
        amount: "300.00",
        tax: "20.00",
        total: "320.00"
      },
      {
        id: "15",
        date: "2007-09-06",
        name: "test3",
        note: "note3",
        amount: "400.00",
        tax: "30.00",
        total: "430.00"
      },
      {
        id: "16",
        date: "2007-10-04",
        name: "test",
        note: "note",
        amount: "200.00",
        tax: "10.00",
        total: "210.00"
      },
      {
        id: "17",
        date: "2007-10-03",
        name: "test2",
        note: "note2",
        amount: "300.00",
        tax: "20.00",
        total: "320.00"
      },
      {
        id: "18",
        date: "2007-09-01",
        name: "test3",
        note: "note3",
        amount: "400.00",
        tax: "30.00",
        total: "430.00"
      }
    ],
    colNames: ['Actions', 'Inv No', 'Date', 'Client', 'Amount', 'Tax', 'Total', 'Notes'],
    colModel: [
      {
        name: 'act',
        index: 'act',
        sortable: false
      },
      {
        name: 'id',
        index: 'id'
      },
      {
        name: 'date',
        index: 'date',
        editable: true
      },
      {
        name: 'name',
        index: 'name',
        editable: true
      },
      {
        name: 'amount',
        index: 'amount',
        align: "right",
        editable: true
      },
      {
        name: 'tax',
        index: 'tax',
        align: "right",
        editable: true
      },
      {
        name: 'total',
        index: 'total',
        align: "right",
        editable: true
      },
      {
        name: 'note',
        index: 'note',
        sortable: false,
        editable: true
      }
    ]
  }


  $scope.getSelection = function(){
    alert(jQuery('table').jqGrid('getGridParam', 'selarrrow'));
  };

  $scope.selectRow = function(row){
    jQuery('table').jqGrid('setSelection', row);

  }
});
"use strict";

angular.module('app.ui').controller('GeneralElementsCtrl', function ($scope, $sce) {
  /*
   * Smart Notifications
   */

  console.log('estamos en el appUI');
  $scope.eg1 = function () {

    $.bigBox({
      title: "Big Information box",
      content: "This message will dissapear in 6 seconds!",
      color: "#C46A69",
      //timeout: 6000,
      icon: "fa fa-warning shake animated",
      number: "1",
      timeout: 6000
    });
  };

  $scope.eg2 = function () {

    $.bigBox({
      title: "Big Information box",
      content: "Lorem ipsum dolor sit amet, test consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam",
      color: "#3276B1",
      //timeout: 8000,
      icon: "fa fa-bell swing animated",
      number: "2"
    });

  };

  $scope.eg3 = function () {

    $.bigBox({
      title: "Shield is up and running!",
      content: "Lorem ipsum dolor sit amet, test consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam",
      color: "#C79121",
      //timeout: 8000,
      icon: "fa fa-shield fadeInLeft animated",
      number: "3"
    });

  };

  $scope.eg4 = function () {

    $.bigBox({
      title: "Success Message Example",
      content: "Lorem ipsum dolor sit amet, test consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam",
      color: "#739E73",
      //timeout: 8000,
      icon: "fa fa-check",
      number: "4"
    }, function () {
      $scope.closedthis();
    });

  };


  $scope.eg5 = function() {

    $.smallBox({
      title: "Ding Dong!",
      content: "Someone's at the door...shall one get it sir? <p class='text-align-right'><a href-void class='btn btn-primary btn-sm'>Yes</a> <a href-void class='btn btn-danger btn-sm'>No</a></p>",
      color: "#296191",
      //timeout: 8000,
      icon: "fa fa-bell swing animated"
    });
  };


  $scope.eg6 = function() {

    $.smallBox({
      title: "Big Information box",
      content: "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam",
      color: "#5384AF",
      //timeout: 8000,
      icon: "fa fa-bell"
    });

  };

  $scope.eg7 = function() {

    $.smallBox({
      title: "James Simmons liked your comment",
      content: "<i class='fa fa-clock-o'></i> <i>2 seconds ago...</i>",
      color: "#296191",
      iconSmall: "fa fa-thumbs-up bounce animated",
      timeout: 4000
    });

  };

  $scope.closedthis = function() {
    $.smallBox({
      title: "Great! You just closed that last alert!",
      content: "This message will be gone in 5 seconds!",
      color: "#739E73",
      iconSmall: "fa fa-cloud",
      timeout: 5000
    });
  };

  /*
   * SmartAlerts
   */
  // With Callback
  $scope.smartModEg1 =  function () {
    $.SmartMessageBox({
      title: "Smart Alert!",
      content: "This is a confirmation box. Can be programmed for button callback",
      buttons: '[No][Yes]'
    }, function (ButtonPressed) {
      if (ButtonPressed === "Yes") {

        $.smallBox({
          title: "Callback function",
          content: "<i class='fa fa-clock-o'></i> <i>You pressed Yes...</i>",
          color: "#659265",
          iconSmall: "fa fa-check fa-2x fadeInRight animated",
          timeout: 4000
        });
      }
      if (ButtonPressed === "No") {
        $.smallBox({
          title: "Callback function",
          content: "<i class='fa fa-clock-o'></i> <i>You pressed No...</i>",
          color: "#C46A69",
          iconSmall: "fa fa-times fa-2x fadeInRight animated",
          timeout: 4000
        });
      }

    });
  };

  // With Input
  $scope.smartModEg2 =  function () {
    $.SmartMessageBox({
      title: "Smart Alert: Input",
      content: "Please enter your user name",
      buttons: "[Accept]",
      input: "text",
      placeholder: "Enter your user name"
    }, function (ButtonPress, Value) {
      alert(ButtonPress + " " + Value);
    });
  };

  // With Buttons
  $scope.smartModEg3 =  function () {
    $.SmartMessageBox({
      title: "Smart Notification: Buttons",
      content: "Lots of buttons to go...",
      buttons: '[Need?][You][Do][Buttons][Many][How]'
    });

  }
  // With Select
  $scope.smartModEg4 =  function () {
    $.SmartMessageBox({
      title: "Smart Alert: Select",
      content: "You can even create a group of options.",
      buttons: "[Done]",
      input: "select",
      options: "[Costa Rica][United States][Autralia][Spain]"
    }, function (ButtonPress, Value) {
      alert(ButtonPress + " " + Value);
    });

  };

  // With Login
  $scope.smartModEg5 =  function () {

    $.SmartMessageBox({
      title: "Login form",
      content: "Please enter your user name",
      buttons: "[Cancel][Accept]",
      input: "text",
      placeholder: "Enter your user name"
    }, function (ButtonPress, Value) {
      if (ButtonPress == "Cancel") {
        alert("Why did you cancel that? :(");
        return 0;
      }

      var Value1 = Value.toUpperCase();
      var ValueOriginal = Value;
      $.SmartMessageBox({
        title: "Hey! <strong>" + Value1 + ",</strong>",
        content: "And now please provide your password:",
        buttons: "[Login]",
        input: "password",
        placeholder: "Password"
      }, function (ButtonPress, Value) {
        alert("Username: " + ValueOriginal + " and your password is: " + Value);
      });
    });

  };

  $scope.tabsPopoverContent = $sce.trustAsHtml("<ul id='popup-tab' class='nav nav-tabs bordered'><li class='active'><a href='#pop-1' data-toggle='tab'>Active Tab </a></li><li><a href='#pop-2' data-toggle='tab'>Tab 2</a></li></ul><div id='popup-tab-content' class='tab-content padding-10'><div class='tab-pane fade in active' id='pop-1'><p>I have six locks on my door all in a row. When I go out, I lock every other one. I figure no matter how long somebody stands there picking the locks, they are always locking three.</p></div><div class='tab-pane fade' id='pop-2'><p>Food truck fixie locavore, accusamus mcsweeneys marfa nulla single-origin coffee squid. wes anderson artisan four loko farm-to-table craft beer twee.</p></div></div>")

  $scope.formPopoverContent = $sce.trustAsHtml("<form action='/api/plug' style='min-width:170px'><div class='checkbox'><label><input type='checkbox' class='checkbox style-0' checked='checked'><span>Read</span></label></div><div class='checkbox'><label><input type='checkbox' class='checkbox style-0'><span>Write</span></label></div><div class='checkbox'><label><input type='checkbox' class='checkbox style-0'><span>Execute</span></label></div><div class='form-actions'><div class='row'><div class='col-md-12'><button class='btn btn-primary btn-sm' type='submit'>SAVE</button></div></div></div></form>")

});

"use strict";


angular.module('app.ui').controller('JquiCtrl', function ($scope) {
  $scope.demoAutocompleteWords = [
    "ActionScript",
    "AppleScript",
    "Asp",
    "BASIC",
    "C",
    "C++",
    "Clojure",
    "COBOL",
    "ColdFusion",
    "Erlang",
    "Fortran",
    "Groovy",
    "Haskell",
    "Java",
    "JavaScript",
    "Lisp",
    "Perl",
    "PHP",
    "Python",
    "Ruby",
    "Scala",
    "Scheme"];


  $scope.demoAjaxAutocomplete = '';


  $scope.modalDemo1 = function(){
    console.log('modalDemo1');
  }

  $scope.modalDemo2 = function(){
    console.log('modalDemo2');
  }


});
"use strict";


angular.module('app.ui').controller('TreeviewCtrl', function ($scope) {
  $scope.demoTree1 = [
    {"content": "<span><i class=\"fa fa-lg fa-calendar\"></i> 2013, Week 2</span>", "expanded": true, "children": [
      {"content": "<span class=\"label label-success\"><i class=\"fa fa-lg fa-plus-circle\"></i> Monday, January 7: 8.00 hours</span>", "expanded": true, "children": [
        {"content": "<span><i class=\"fa fa-clock-o\"></i> 8.00</span> &ndash; <a> Changed CSS to accomodate...</a>"}
      ]},
      {"content": "<span><i class=\"fa fa-clock-o\"></i> 8.00</span> &ndash; <a> Changed CSS to accomodate...</a>"},
      {"content": "<span class=\"label label-success\"><i class=\"fa fa-lg fa-minus-circle\"></i> Tuesday, January 8: 8.00 hours</span>", "expanded": true, "children": [
        {"content": "<span><i class=\"fa fa-clock-o\"></i> 6.00</span> &ndash; <a> Altered code...</a>"},
        {"content": "<span><i class=\"fa fa-clock-o\"></i> 2.00</span> &ndash; <a> Simplified our approach to...</a>"}
      ]},
      {"content": "<span><i class=\"fa fa-clock-o\"></i> 6.00</span> &ndash; <a> Altered code...</a>"},
      {"content": "<span><i class=\"fa fa-clock-o\"></i> 2.00</span> &ndash; <a> Simplified our approach to...</a>"},
      {"content": "<span class=\"label label-warning\"><i class=\"fa fa-lg fa-minus-circle\"></i> Wednesday, January 9: 6.00 hours</span>", "children": [
        {"content": "<span><i class=\"fa fa-clock-o\"></i> 3.00</span> &ndash; <a> Fixed bug caused by...</a>"},
        {"content": "<span><i class=\"fa fa-clock-o\"></i> 3.00</span> &ndash; <a> Comitting latest code to Git...</a>"}
      ]},
      {"content": "<span><i class=\"fa fa-clock-o\"></i> 3.00</span> &ndash; <a> Fixed bug caused by...</a>"},
      {"content": "<span><i class=\"fa fa-clock-o\"></i> 3.00</span> &ndash; <a> Comitting latest code to Git...</a>"},
      {"content": "<span class=\"label label-danger\"><i class=\"fa fa-lg fa-minus-circle\"></i> Wednesday, January 9: 4.00 hours</span>", "children": [
        {"content": "<span><i class=\"fa fa-clock-o\"></i> 2.00</span> &ndash; <a> Create component that...</a>"}
      ]},
      {"content": "<span><i class=\"fa fa-clock-o\"></i> 2.00</span> &ndash; <a> Create component that...</a>"}
    ]},
    {"content": "<span><i class=\"fa fa-lg fa-calendar\"></i> 2013, Week 3</span>", "children": [
      {"content": "<span class=\"label label-success\"><i class=\"fa fa-lg fa-minus-circle\"></i> Monday, January 14: 8.00 hours</span>", "children": [
        {"content": "<span><i class=\"fa fa-clock-o\"></i> 7.75</span> &ndash; <a> Writing documentation...</a>"},
        {"content": "<span><i class=\"fa fa-clock-o\"></i> 0.25</span> &ndash; <a> Reverting code back to...</a>"}
      ]},
      {"content": "<span><i class=\"fa fa-clock-o\"></i> 7.75</span> &ndash; <a> Writing documentation...</a>"},
      {"content": "<span><i class=\"fa fa-clock-o\"></i> 0.25</span> &ndash; <a> Reverting code back to...</a>"}
    ]}
  ]

  $scope.demoTree2 = [
    {"content": "<span><i class=\"fa fa-lg fa-folder-open\"></i> Parent</span>", "expanded": true, "children": [
      {"content": "<span><i class=\"fa fa-lg fa-plus-circle\"></i> Administrators</span>", "expanded": true, "children": [
        {"content": "<span> <label class=\"checkbox inline-block\"><input type=\"checkbox\" name=\"checkbox-inline\"><i></i>Michael.Jackson</label> </span>"},
        {"content": "<span> <label class=\"checkbox inline-block\"><input type=\"checkbox\" checked=\"checked\" name=\"checkbox-inline\"><i></i>Sunny.Ahmed</label> </span>"},
        {"content": "<span> <label class=\"checkbox inline-block\"><input type=\"checkbox\" checked=\"checked\" name=\"checkbox-inline\"><i></i>Jackie.Chan</label> </span>"}
      ]},
      {"content": "<span> <label class=\"checkbox inline-block\"><input type=\"checkbox\" name=\"checkbox-inline\"><i></i>Michael.Jackson</label> </span>"},
      {"content": "<span> <label class=\"checkbox inline-block\"><input type=\"checkbox\" checked=\"checked\" name=\"checkbox-inline\"><i></i>Sunny.Ahmed</label> </span>"},
      {"content": "<span> <label class=\"checkbox inline-block\"><input type=\"checkbox\" checked=\"checked\" name=\"checkbox-inline\"><i></i>Jackie.Chan</label> </span>"},
      {"content": "<span><i class=\"fa fa-lg fa-minus-circle\"></i> Child</span>", "expanded": true, "children": [
        {"content": "<span><i class=\"icon-leaf\"></i> Grand Child</span>"},
        {"content": "<span><i class=\"icon-leaf\"></i> Grand Child</span>"},
        {"content": "<span><i class=\"fa fa-lg fa-plus-circle\"></i> Grand Child</span>",  "children": [
          {"content": "<span><i class=\"fa fa-lg fa-plus-circle\"></i> Great Grand Child</span>", "children": [
            {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"},
            {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"}
          ]},
          {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"},
          {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"},
          {"content": "<span><i class=\"icon-leaf\"></i> Great Grand Child</span>"},
          {"content": "<span><i class=\"icon-leaf\"></i> Great Grand Child</span>"}
        ]},
        {"content": "<span><i class=\"fa fa-lg fa-plus-circle\"></i> Great Grand Child</span>", "children": [
          {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"},
          {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"}
        ]},
        {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"},
        {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"},
        {"content": "<span><i class=\"icon-leaf\"></i> Great Grand Child</span>"},
        {"content": "<span><i class=\"icon-leaf\"></i> Great Grand Child</span>"}
      ]},
      {"content": "<span><i class=\"icon-leaf\"></i> Grand Child</span>"},
      {"content": "<span><i class=\"icon-leaf\"></i> Grand Child</span>"},
      {"content": "<span><i class=\"fa fa-lg fa-plus-circle\"></i> Grand Child</span>", "children": [
        {"content": "<span><i class=\"fa fa-lg fa-plus-circle\"></i> Great Grand Child</span>", "children": [
          {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"},
          {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"}
        ]},
        {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"},
        {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"},
        {"content": "<span><i class=\"icon-leaf\"></i> Great Grand Child</span>"},
        {"content": "<span><i class=\"icon-leaf\"></i> Great Grand Child</span>"}
      ]},
      {"content": "<span><i class=\"fa fa-lg fa-plus-circle\"></i> Great Grand Child</span>", "children": [
        {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"},
        {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"}
      ]},
      {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"},
      {"content": "<span><i class=\"icon-leaf\"></i> Great great Grand Child</span>"},
      {"content": "<span><i class=\"icon-leaf\"></i> Great Grand Child</span>"},
      {"content": "<span><i class=\"icon-leaf\"></i> Great Grand Child</span>"}
    ]},
    {"content": "<span><i class=\"fa fa-lg fa-folder-open\"></i> Parent2</span>", "children": [
      {"content": "<span><i class=\"icon-leaf\"></i> Child</span>"}
    ]}
  ]
});
'use strict';

angular.module('app.ui').directive('smartClassFilter', function () {
  return {
    restrict: 'A',
    scope: {
      model: '=',
      displayElements: '@',
      filterElements: '@'
    },
    link: function (scope, element) {
      scope.$watch('model', function (model) {
        if (angular.isString(model)) {
          var search = model.trim();
          if (search) {
            angular.element(scope.displayElements, element).hide();

            angular.element(scope.filterElements, element)
              .filter(function () {
                var r = new RegExp(search, 'i');
                return r.test($(this).attr('class') + $(this).attr('alt'))
              })
              .closest(scope.displayElements).show();
          } else {
            angular.element(scope.displayElements, element).show();
          }
        }
      })
    }
  }
});
'use strict';

angular.module('app.ui').directive('smartJquiAccordion', function () {
  return {
    restrict: 'A',
    link: function (scope, element, attributes) {

      element.accordion({
        autoHeight : false,
        heightStyle : "content",
        collapsible : true,
        animate : 300,
        icons: {
          header: "fa fa-plus",    // custom icon class
          activeHeader: "fa fa-minus" // custom icon class
        },
        header : "h4"
      })
    }
  }
});

'use strict';

angular.module('app.ui').directive('smartJquiAjaxAutocomplete', function () {
  return {
    restrict: 'A',
    scope: {
      ngModel: '='
    },
    link: function (scope, element, attributes) {
      function split(val) {
        return val.split(/,\s*/);
      }

      function extractLast(term) {
        return split(term).pop();
      }

      function extractFirst(term) {
        return split(term)[0];
      }


      element.autocomplete({
        source: function (request, response) {
          jQuery.getJSON(
            "http://gd.geobytes.com/AutoCompleteCity?callback=?&q=" + extractLast(request.term),
            function (data) {
              response(data);
            }
          );
        },
        minLength: 3,
        select: function (event, ui) {
          var selectedObj = ui.item,
            placeName = selectedObj.value;
          if (typeof placeName == "undefined") placeName = element.val();

          if (placeName) {
            var terms = split(element.val());
            // remove the current input
            terms.pop();
            // add the selected item (city only)
            terms.push(extractFirst(placeName));
            // add placeholder to get the comma-and-space at the end
            terms.push("");

            scope.$apply(function(){
              scope.ngModel = terms.join(", ")
            });
          }

          return false;
        },
        focus: function() {
          // prevent value inserted on focus
          return false;
        },
        delay: 100
      });
    }
  }
});