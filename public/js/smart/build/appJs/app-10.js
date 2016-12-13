
'use strict';

angular.module('SmartAdmin.Forms').directive('smartClockpicker', function () {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-clockpicker data-smart-clockpicker');

      var options = {
        placement: 'top',
        donetext: 'Done'
      }

      tElement.clockpicker(options);
    }
  }
});

'use strict';

angular.module('SmartAdmin.Forms').directive('smartColorpicker', function () {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-colorpicker data-smart-colorpicker');


      var aOptions = _.pick(tAttributes, ['']);

      var options = _.extend(aOptions, {});

      tElement.colorpicker(options);
    }
  }
});
"use strict";

angular.module('SmartAdmin.Forms').directive('smartDatepicker', function () {
  return {
    restrict: 'A',
    scope: {
      options: '='
    },
    link: function (scope, element, attributes) {

      var onSelectCallbacks = [];
      if (attributes.minRestrict) {
        onSelectCallbacks.push(function (selectedDate) {
          $(attributes.minRestrict).datepicker('option', 'minDate', selectedDate);
        });
      }
      if (attributes.maxRestrict) {
        onSelectCallbacks.push(function (selectedDate) {
          $(attributes.maxRestrict).datepicker('option', 'maxDate', selectedDate);
        });
      }

      //Let others know about changes to the data field
      onSelectCallbacks.push(function (selectedDate) {
        //CVB - 07/14/2015 - Update the scope with the selected value
        element.triggerHandler("change");

        //CVB - 07/17/2015 - Update Bootstrap Validator
        var form = element.closest('form');

        if(typeof form.bootstrapValidator == 'function')
          form.bootstrapValidator('revalidateField', element.attr('name'));
      });

      var options = _.extend({
        prevText: '<i class="fa fa-chevron-left"></i>',
        nextText: '<i class="fa fa-chevron-right"></i>',
        onSelect: function (selectedDate) {
          angular.forEach(onSelectCallbacks, function (callback) {
            callback.call(this, selectedDate)
          })
        }
      }, scope.options || {});


      if (attributes.numberOfMonths) options.numberOfMonths = parseInt(attributes.numberOfMonths);

      if (attributes.dateFormat) options.dateFormat = attributes.dateFormat;

      if (attributes.defaultDate) options.defaultDate = attributes.defaultDate;

      if (attributes.changeMonth) options.changeMonth = attributes.changeMonth == "true";


      element.datepicker(options)
    }
  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartDuallistbox', function () {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-duallistbox data-smart-duallistbox');


      var aOptions = _.pick(tAttributes, ['nonSelectedFilter']);

      var options = _.extend(aOptions, {
        nonSelectedListLabel: 'Non-selected',
        selectedListLabel: 'Selected',
        preserveSelectionOnMove: 'moved',
        moveOnSelect: false
      });

      tElement.bootstrapDualListbox(options);
    }
  }
});

'use strict';

angular.module('SmartAdmin.Forms').directive('smartIonslider', function (lazyScript) {
  return {
    restrict: 'A',
    compile: function (element, attributes) {
      element.removeAttr('smart-ionslider data-smart-ionslider');

      lazyScript.register('/js/smart/build/vendor.ui.js').then(function(){
        element.ionRangeSlider();
      });
    }
  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartKnob', function () {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-knob data-smart-knob');

      tElement.knob();
    }
  }
});
"use strict";

angular.module('SmartAdmin.Forms').directive('smartMaskedInput', function(lazyScript){
  return {
    restrict: 'A',
    compile: function(tElement, tAttributes){
      tElement.removeAttr('smart-masked-input data-smart-masked-input');

      lazyScript.register('/js/smart/build/vendor.ui.js').then(function(){

        var options = {};
        if(tAttributes.maskPlaceholder) options.placeholder =  tAttributes.maskPlaceholder;
        tElement.mask(tAttributes.smartMaskedInput, options);
      })
    }
  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartNouislider', function ($parse, lazyScript) {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      lazyScript.register('/js/smart/build/vendor.ui.js').then(function(){
        tElement.removeAttr('smart-nouislider data-smart-nouislider');

        tElement.addClass('noUiSlider');

        var options = {
          range: {
            min: tAttributes.rangeMin ? parseInt(tAttributes.rangeMin) : 0,
            max: tAttributes.rangeMax ? parseInt(tAttributes.rangeMax) : 1000
          },
          start: $parse(tAttributes.start)()
        };

        if (tAttributes.step) options.step =  parseInt(tAttributes.step);

        if(tAttributes.connect) options.connect = tAttributes.connect == 'true' ? true : tAttributes.connect;

        tElement.noUiSlider(options);

        if(tAttributes.update) tElement.on('slide', function(){
          $(tAttributes.update).text(JSON.stringify(tElement.val()));
        });
      })
    }
  }
});
'use strict'

angular.module('SmartAdmin.Forms').directive('smartSelect2', function (lazyScript) {
  return {
    restrict: 'A',
    compile: function (element, attributes) {
      element.hide().removeAttr('smart-select2 data-smart-select2');
      lazyScript.register('/js/smart/build/vendor.ui.js').then(function(){
        element.show().select2();
      })
    }
  }
});
'use strict'

angular.module('SmartAdmin.Forms').directive('smartSpinner', function () {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-spinner');

      var options = {};
      if(tAttributes.smartSpinner == 'deicimal'){
        options = {
          step: 0.01,
          numberFormat: "n"
        };
      }else if(tAttributes.smartSpinner == 'currency'){
        options = {
          min: 5,
          max: 2500,
          step: 25,
          start: 1000,
          numberFormat: "C"
        };
      }

      tElement.spinner(options);
    }
  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartTagsinput', function () {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-tagsinput data-smart-tagsinput');
      tElement.tagsinput();
    }
  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartTimepicker', function () {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-timepicker data-smart-timepicker');
      tElement.timepicker();
    }
  }
});

'use strict';

angular.module('SmartAdmin.Forms').directive('smartUislider', function ($parse, lazyScript) {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {

      tElement.removeAttr('smart-uislider data-smart-uislider');

      lazyScript.register('/js/smart/build/vendor.ui.js').then(function(){
        tElement.bootstrapSlider();

        $(tElement.data('bootstrapSlider').sliderElem).prepend(tElement);
      })

    }
  }
});
"use strict";

angular.module('SmartAdmin.Forms').directive('smartXeditable', function($timeout, $log){

  function link (scope, element, attrs, ngModel) {

    var defaults = {
      validate: function (value){
//         console.log(attrs);
        gToolsxEdit(value, attrs.recordField, attrs.recordId, attrs.recordIdcontainer, attrs.recordTable);
      }
//       ,display: function(value, srcData) {
//           ngModel.$setViewValue(value);
//           scope.$apply();
//       }
    };

    var inited = false;

    var initXeditable = function() {
      var options = scope.options || {};
      var initOptions = angular.extend(defaults, options);

      // $log.log(initOptions);
      element.editable('destroy');
      element.editable(initOptions);
    }

    scope.$watch("options", function(newValue) {

      if(!newValue) {
        return false;
      }

      initXeditable();

      // $log.log("Options changed...");

    }, true);

  }

  return {
    restrict: 'A',
    require: "ngModel",
    scope: {
      options: "="
    },
    link: link

  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartDropzone', function () {
  return function (scope, element, attrs) {
    var config, dropzone;

    config = scope[attrs.smartDropzone];

    // create a Dropzone for the element with the given options
    dropzone = new Dropzone(element[0], config.options);

    // bind the given event handlers
    angular.forEach(config.eventHandlers, function (handler, event) {
      dropzone.on(event, handler);
    });
  };
});

'use strict';

angular.module('SmartAdmin.Forms').directive('smartValidateForm', function (formsCommon) {
  return {
    restrict: 'A',
    link: function (scope, form, attributes) {

      var validateOptions = {
        rules: {},
        messages: {},
        highlight: function (element) {
          $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
        },
        unhighlight: function (element) {
          $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
        },
        errorElement: 'span',
        errorClass: 'help-block',
        errorPlacement: function (error, element) {
          if (element.parent('.input-group').length) {
            error.insertAfter(element.parent());
          } else {
            error.insertAfter(element);
          }
        }
      };
      form.find('[data-smart-validate-input], [smart-validate-input]').each(function () {
        var $input = $(this), fieldName = $input.attr('name');

        validateOptions.rules[fieldName] = {};

        if ($input.data('required') != undefined) {
          validateOptions.rules[fieldName].required = true;
        }
        if ($input.data('email') != undefined) {
          validateOptions.rules[fieldName].email = true;
        }

        if ($input.data('maxlength') != undefined) {
          validateOptions.rules[fieldName].maxlength = $input.data('maxlength');
        }

        if ($input.data('minlength') != undefined) {
          validateOptions.rules[fieldName].minlength = $input.data('minlength');
        }

        if($input.data('message')){
          validateOptions.messages[fieldName] = $input.data('message');
        } else {
          angular.forEach($input.data(), function(value, key){
            if(key.search(/message/)== 0){
              if(!validateOptions.messages[fieldName])
                validateOptions.messages[fieldName] = {};

              var messageKey = key.toLowerCase().replace(/^message/,'')
              validateOptions.messages[fieldName][messageKey] = value;
            }
          });
        }
      });


      form.validate(validateOptions);

    }
  }
});

'use strict';

angular.module('SmartAdmin.Forms').directive('smartFueluxWizard', function () {
  return {
    restrict: 'A',
    scope: {
      smartWizardCallback: '&'
    },
    link: function (scope, element, attributes) {

      var wizard = element.wizard();

      var $form = element.find('form');

      wizard.on('actionclicked.fu.wizard', function(e, data){
        if ($form.data('validator')) {
          if (!$form.valid()) {
            $form.data('validator').focusInvalid();
            e.preventDefault();
          }
        }
      });

      wizard.on('finished.fu.wizard', function (e, data) {
        var formData = {};
        _.each($form.serializeArray(), function(field){
          formData[field.name] = field.value
        });
        if(typeof scope.smartWizardCallback() === 'function'){
          scope.smartWizardCallback()(formData)
        }
      });
    }
  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartWizard', function () {
  return {
    restrict: 'A',
    scope: {
      'smartWizardCallback': '&'
    },
    link: function (scope, element, attributes) {

      var stepsCount = $('[data-smart-wizard-tab]').length;

      var currentStep = 1;

      var validSteps = [];

      var $form = element.closest('form');

      var $prev = $('[data-smart-wizard-prev]', element);

      var $next = $('[data-smart-wizard-next]', element);

      function setStep(step) {
        currentStep = step;
        $('[data-smart-wizard-pane=' + step + ']', element).addClass('active').siblings('[data-smart-wizard-pane]').removeClass('active');
        $('[data-smart-wizard-tab=' + step + ']', element).addClass('active').siblings('[data-smart-wizard-tab]').removeClass('active');

        $prev.toggleClass('disabled', step == 1)
      }


      element.on('click', '[data-smart-wizard-tab]', function (e) {
        setStep(parseInt($(this).data('smartWizardTab')));
        e.preventDefault();
      });

      $next.on('click', function (e) {
        if ($form.data('validator')) {
          if (!$form.valid()) {
            validSteps = _.without(validSteps, currentStep);
            $form.data('validator').focusInvalid();
            return false;
          } else {
            validSteps = _.without(validSteps, currentStep);
            validSteps.push(currentStep);
            element.find('[data-smart-wizard-tab=' + currentStep + ']')
              .addClass('complete')
              .find('.step')
              .html('<i class="fa fa-check"></i>');
          }
        }
        if (currentStep < stepsCount) {
          setStep(currentStep + 1);
        } else {
          if (validSteps.length < stepsCount) {
            var steps = _.range(1, stepsCount + 1)

            _(steps).forEach(function (num) {
              if (validSteps.indexOf(num) == -1) {
                console.log(num);
                setStep(num);
                return false;
              }
            })
          } else {
            var data = {};
            _.each($form.serializeArray(), function(field){
              data[field.name] = field.value
            });
            if(typeof  scope.smartWizardCallback() === 'function'){
              scope.smartWizardCallback()(data)
            }
          }
        }

        e.preventDefault();
      });

      $prev.on('click', function (e) {
        if (!$prev.hasClass('disabled') && currentStep > 0) {
          setStep(currentStep - 1);
        }
        e.preventDefault();
      });


      setStep(currentStep);

    }
  }
});
'use strict';

angular.module('SmartAdmin.Layout').directive('demoStates', function ($rootScope) {
  return {
    restrict: 'EA',
    replace: true,
    templateUrl: 'app/_common/layout/directives/demo/demo-states.tpl.html',
    scope: true,
    link: function (scope, element, attributes) {
      element.parent().css({
        position: 'relative'
      });

      element.on('click', '#demo-setting', function () {
        element.toggleClass('activate')
      })
    },
    controller: function ($scope) {
      var $root = $('body');

      $scope.$watch('fixedHeader', function (fixedHeader) {
        localStorage.setItem('sm-fixed-header', fixedHeader);
        $root.toggleClass('fixed-header', fixedHeader);
        if (fixedHeader == false) {
          $scope.fixedRibbon = false;
          $scope.fixedNavigation = false;
        }
      });


      $scope.$watch('fixedNavigation', function (fixedNavigation) {
        localStorage.setItem('sm-fixed-navigation', fixedNavigation);
        $root.toggleClass('fixed-navigation', fixedNavigation);
        if (fixedNavigation) {
          $scope.insideContainer = false;
          $scope.fixedHeader = true;
        } else {
          $scope.fixedRibbon = false;
        }
      });


      $scope.$watch('fixedRibbon', function (fixedRibbon) {
        localStorage.setItem('sm-fixed-ribbon', fixedRibbon);
        $root.toggleClass('fixed-ribbon', fixedRibbon);
        if (fixedRibbon) {
          $scope.fixedHeader = true;
          $scope.fixedNavigation = true;
          $scope.insideContainer = false;
        }
      });

      $scope.$watch('fixedPageFooter', function (fixedPageFooter) {
        localStorage.setItem('sm-fixed-page-footer', fixedPageFooter);
        $root.toggleClass('fixed-page-footer', fixedPageFooter);
      });

      $scope.$watch('insideContainer', function (insideContainer) {
        localStorage.setItem('sm-inside-container', insideContainer);
        $root.toggleClass('container', insideContainer);
        if (insideContainer) {
          $scope.fixedRibbon = false;
          $scope.fixedNavigation = false;
        }
      });

      $scope.$watch('rtl', function (rtl) {
        localStorage.setItem('sm-rtl', rtl);
        $root.toggleClass('smart-rtl', rtl);
      });

      $scope.$watch('menuOnTop', function (menuOnTop) {
        $rootScope.$broadcast('$smartLayoutMenuOnTop', menuOnTop);
        localStorage.setItem('sm-menu-on-top', menuOnTop);
        $root.toggleClass('menu-on-top', menuOnTop);

        if(menuOnTop)$root.removeClass('minified');
      });

      $scope.$watch('colorblindFriendly', function (colorblindFriendly) {
        localStorage.setItem('sm-colorblind-friendly', colorblindFriendly);
        $root.toggleClass('colorblind-friendly', colorblindFriendly);
      });


      $scope.fixedHeader = localStorage.getItem('sm-fixed-header') == 'true';
      $scope.fixedNavigation = localStorage.getItem('sm-fixed-navigation') == 'true';
      $scope.fixedRibbon = localStorage.getItem('sm-fixed-ribbon') == 'true';
      $scope.fixedPageFooter = localStorage.getItem('sm-fixed-page-footer') == 'true';
      $scope.insideContainer = localStorage.getItem('sm-inside-container') == 'true';
      $scope.rtl = localStorage.getItem('sm-rtl') == 'true';
      $scope.menuOnTop = localStorage.getItem('sm-menu-on-top') == 'true' || $root.hasClass('menu-on-top');
      $scope.colorblindFriendly = localStorage.getItem('sm-colorblind-friendly') == 'true';


      $scope.skins = appConfig.skins;


      $scope.smartSkin = localStorage.getItem('sm-skin') ? localStorage.getItem('sm-skin') : appConfig.smartSkin;

      $scope.setSkin = function (skin) {
        $scope.smartSkin = skin.name;
        $root.removeClass(_.pluck($scope.skins, 'name').join(' '));
        $root.addClass(skin.name);
        localStorage.setItem('sm-skin', skin.name);
        $("#logo img").attr('src', skin.logo);
      };


      if($scope.smartSkin != "smart-style-0"){
        $scope.setSkin(_.find($scope.skins, {name: $scope.smartSkin}))
      }


      $scope.factoryReset = function () {
        $.SmartMessageBox({
          title: "<i class='fa fa-refresh' style='color:green'></i> Clear Local Storage",
          content: "Would you like to RESET all your saved widgets and clear LocalStorage?1",
          buttons: '[No][Yes]'
        }, function (ButtonPressed) {
          if (ButtonPressed == "Yes" && localStorage) {
            localStorage.clear();
            location.reload()
          }
        });
      }
    }
  }
});
"use strict";

(function ($) {

  $.fn.smartCollapseToggle = function () {

    return this.each(function () {

      var $body = $('body');
      var $this = $(this);

      // only if not  'menu-on-top'
      if ($body.hasClass('menu-on-top')) {


      } else {

        $body.hasClass('mobile-view-activated')

        // toggle open
        $this.toggleClass('open');

        // for minified menu collapse only second level
        if ($body.hasClass('minified')) {
          if ($this.closest('nav ul ul').length) {
            $this.find('>a .collapse-sign .fa').toggleClass('fa-minus-square-o fa-plus-square-o');
            $this.find('ul:first').slideToggle(appConfig.menu_speed || 200);
          }
        } else {
          // toggle expand item
          $this.find('>a .collapse-sign .fa').toggleClass('fa-minus-square-o fa-plus-square-o');
          $this.find('ul:first').slideToggle(appConfig.menu_speed || 200);
        }
      }
    });
  };
})(jQuery);

angular.module('SmartAdmin.Layout').directive('smartMenu', function ($state, $rootScope) {
  return {
    restrict: 'A',
    link: function (scope, element, attrs) {
      var $body = $('body');

      var $collapsible = element.find('li[data-menu-collapse]');

      var bindEvents = function(){
        $collapsible.each(function (idx, li) {
          var $li = $(li);
          $li
            .on('click', '>a', function (e) {

              // collapse all open siblings
              $li.siblings('.open').smartCollapseToggle();

              // toggle element
              $li.smartCollapseToggle();

              // add active marker to collapsed element if it has active childs
              if (!$li.hasClass('open') && $li.find('li.active').length > 0) {
                $li.addClass('active')
              }

              e.preventDefault();
            })
            .find('>a').append('<b class="collapse-sign"><em class="fa fa-plus-square-o"></em></b>');

          // initialization toggle
          if ($li.find('li.active').length) {
            $li.smartCollapseToggle();
            $li.find('li.active').parents('li').addClass('active');
          }
        });
      }
      bindEvents();


      // click on route link
      element.on('click', 'a[data-ui-sref]', function (e) {
        // collapse all siblings to element parents and remove active markers
        $(this)
          .parents('li').addClass('active')
          .each(function () {
            $(this).siblings('li.open').smartCollapseToggle();
            $(this).siblings('li').removeClass('active')
          });

        if ($body.hasClass('mobile-view-activated')) {
          $rootScope.$broadcast('requestToggleMenu');
        }
      });


      scope.$on('$smartLayoutMenuOnTop', function (event, menuOnTop) {
        if (menuOnTop) {
          $collapsible.filter('.open').smartCollapseToggle();
        }
      });
    }
  }
});
(function(){
  "use strict";

  angular.module('SmartAdmin.Layout').directive('smartMenuItems', function ($http, $rootScope, $compile) {
    return {
      restrict: 'A',
      compile: function (element, attrs) {


        function createItem(item, parent, level){
          var li = $('<li />' ,{'ui-sref-active': "active"})
          var a = $('<a />');
          var i = $('<i />');

          li.append(a);

          if(item.sref)
            a.attr('ui-sref', item.sref);
          if(item.href)
            a.attr('href', item.href);
          if(item.icon){
            i.attr('class', 'fa fa-lg fa-fw fa-'+item.icon);
            a.append(i);
          }
          if(item.title){
            a.attr('title', item.title);
            if(level == 1){
              a.append(' <span class="menu-item-parent">' + item.title + '</span>');
            } else {
              a.append(' ' + item.title);

            }
          }

          if(item.items){
            var ul = $('<ul />');
            li.append(ul);
            li.attr('data-menu-collapse', '');
            _.forEach(item.items, function(child) {
              createItem(child, ul, level+1);
            })
          }

          parent.append(li);
        }


        $http.get(attrs.smartMenuItems).then(function(res){
          var ul = $('<ul />', {
            'smart-menu': ''
          })
          _.forEach(res.data.items, function(item) {
            createItem(item, ul, 1);
          })

          var $scope = $rootScope.$new();
          var html = $('<div>').append(ul).html();
          var linkingFunction = $compile(html);

          var _element = linkingFunction($scope);

          element.replaceWith(_element);
        })
      }
    }
  });
})();
/**
 * Jarvis Widget Directive
 *
 *    colorbutton="false"
 *    editbutton="false"
 togglebutton="false"
 deletebutton="false"
 fullscreenbutton="false"
 custombutton="false"
 collapsed="true"
 sortable="false"
 *
 *
 */
"use strict";

angular.module('SmartAdmin.Layout').directive('jarvisWidget', function($rootScope){
  
  return {
    restrict: "A",
    compile: function(element, attributes){
      if(element.data('widget-color'))
        element.addClass('jarviswidget-color-' + element.data('widget-color'));


      element.find('.widget-body').prepend('<div class="jarviswidget-editbox"><input class="form-control" type="text"></div>');

      element.addClass('jarviswidget');
      $rootScope.$emit('jarvisWidgetAdded', element )

    }
  }
});
"use strict";

angular.module('SmartAdmin.Layout').directive('widgetGrid', function ($rootScope, $compile, $q, $state, $timeout) {

  var jarvisWidgetsDefaults = {
    grid: 'article',
    widgets: '.jarviswidget',
    localStorage: true,
    deleteSettingsKey: '#deletesettingskey-options',
    settingsKeyLabel: 'Reset settings?',
    deletePositionKey: '#deletepositionkey-options',
    positionKeyLabel: 'Reset position?',
    sortable: true,
    buttonsHidden: false,
    // toggle button
    toggleButton: true,
    toggleClass: 'fa fa-minus | fa fa-plus',
    toggleSpeed: 200,
    onToggle: function () {
    },
    // delete btn
    deleteButton: true,
    deleteMsg: 'Warning: This action cannot be undone!',
    deleteClass: 'fa fa-times',
    deleteSpeed: 200,
    onDelete: function () {
    },
    // edit btn
    editButton: true,
    editPlaceholder: '.jarviswidget-editbox',
    editClass: 'fa fa-cog | fa fa-save',
    editSpeed: 200,
    onEdit: function () {
    },
    // color button
    colorButton: true,
    // full screen
    fullscreenButton: true,
    fullscreenClass: 'fa fa-expand | fa fa-compress',
    fullscreenDiff: 3,
    onFullscreen: function () {
    },
    // custom btn
    customButton: false,
    customClass: 'folder-10 | next-10',
    customStart: function () {
      alert('Hello you, this is a custom button...');
    },
    customEnd: function () {
      alert('bye, till next time...');
    },
    // order
    buttonOrder: '%refresh% %custom% %edit% %toggle% %fullscreen% %delete%',
    opacity: 1.0,
    dragHandle: '> header',
    placeholderClass: 'jarviswidget-placeholder',
    indicator: true,
    indicatorTime: 600,
    ajax: true,
    timestampPlaceholder: '.jarviswidget-timestamp',
    timestampFormat: 'Last update: %m%/%d%/%y% %h%:%i%:%s%',
    refreshButton: true,
    refreshButtonClass: 'fa fa-refresh',
    labelError: 'Sorry but there was a error:',
    labelUpdated: 'Last Update:',
    labelRefresh: 'Refresh',
    labelDelete: 'Delete widget:',
    afterLoad: function () {
    },
    rtl: false, // best not to toggle this!
    onChange: function () {

    },
    onSave: function () {

    },
    ajaxnav: true

  }

  var dispatchedWidgetIds = [];
  var setupWaiting = false;

  var debug = 1;

  var setupWidgets = function (element, widgetIds) {

    if (!setupWaiting) {

      if(_.intersection(widgetIds, dispatchedWidgetIds).length != widgetIds.length){

        dispatchedWidgetIds = _.union(widgetIds, dispatchedWidgetIds);

//                    console.log('setupWidgets', debug++);

        element.data('jarvisWidgets') && element.data('jarvisWidgets').destroy();
        element.jarvisWidgets(jarvisWidgetsDefaults);
        initDropdowns(widgetIds);
      }

    } else {
      if (!setupWaiting) {
        setupWaiting = true;
        $timeout(function () {
          setupWaiting = false;
          setupWidgets(element, widgetIds)
        }, 200);
      }
    }

  };

  var destroyWidgets = function(element, widgetIds){
    element.data('jarvisWidgets') && element.data('jarvisWidgets').destroy();
    dispatchedWidgetIds = _.xor(dispatchedWidgetIds, widgetIds);
  };

  var initDropdowns = function (widgetIds) {
    angular.forEach(widgetIds, function (wid) {
      $('#' + wid + ' [data-toggle="dropdown"]').each(function () {
        var $parent = $(this).parent();
        // $(this).removeAttr('data-toggle');
        if (!$parent.attr('dropdown')) {
          $(this).removeAttr('href');
          $parent.attr('dropdown', '');
          var compiled = $compile($parent)($parent.scope())
          $parent.replaceWith(compiled);
        }
      })
    });
  };

  var jarvisWidgetAddedOff,
    $viewContentLoadedOff,
    $stateChangeStartOff;

  return {
    restrict: 'A',
    compile: function(element){

      element.removeAttr('widget-grid data-widget-grid');

      var widgetIds = [];

      $viewContentLoadedOff = $rootScope.$on('$viewContentLoaded', function (event, data) {
        $timeout(function () {
          setupWidgets(element, widgetIds)
        }, 100);
      });


      $stateChangeStartOff = $rootScope.$on('$stateChangeStart',
        function(event, toState, toParams, fromState, fromParams){
          jarvisWidgetAddedOff();
          $viewContentLoadedOff();
          $stateChangeStartOff();
          destroyWidgets(element, widgetIds)
        });

      jarvisWidgetAddedOff = $rootScope.$on('jarvisWidgetAdded', function (event, widget) {
        if (widgetIds.indexOf(widget.attr('id')) == -1) {
          widgetIds.push(widget.attr('id'));
          $timeout(function () {
            setupWidgets(element, widgetIds)
          }, 100);
        }
//                    console.log('jarvisWidgetAdded', widget.attr('id'));
      });

    }
  }
});
