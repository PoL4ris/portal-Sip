
"use strict";


angular.module('SmartAdmin.Forms').directive('bootstrapMovieForm', function(){

  return {
    restrict: 'E',
    replace: true,
    templateUrl: 'app/_common/forms/directives/bootstrap-validation/bootstrap-movie-form.tpl.html',
    link: function(scope, form){
      form.bootstrapValidator({
        feedbackIcons : {
          valid : 'glyphicon glyphicon-ok',
          invalid : 'glyphicon glyphicon-remove',
          validating : 'glyphicon glyphicon-refresh'
        },
        fields : {
          title : {
            group : '.col-md-8',
            validators : {
              notEmpty : {
                message : 'The title is required'
              },
              stringLength : {
                max : 200,
                message : 'The title must be less than 200 characters long'
              }
            }
          },
          genre : {
            group : '.col-md-4',
            validators : {
              notEmpty : {
                message : 'The genre is required'
              }
            }
          },
          director : {
            group : '.col-md-4',
            validators : {
              notEmpty : {
                message : 'The director name is required'
              },
              stringLength : {
                max : 80,
                message : 'The director name must be less than 80 characters long'
              }
            }
          },
          writer : {
            group : '.col-md-4',
            validators : {
              notEmpty : {
                message : 'The writer name is required'
              },
              stringLength : {
                max : 80,
                message : 'The writer name must be less than 80 characters long'
              }
            }
          },
          producer : {
            group : '.col-md-4',
            validators : {
              notEmpty : {
                message : 'The producer name is required'
              },
              stringLength : {
                max : 80,
                message : 'The producer name must be less than 80 characters long'
              }
            }
          },
          website : {
            group : '.col-md-6',
            validators : {
              notEmpty : {
                message : 'The website address is required'
              },
              uri : {
                message : 'The website address is not valid'
              }
            }
          },
          trailer : {
            group : '.col-md-6',
            validators : {
              notEmpty : {
                message : 'The trailer link is required'
              },
              uri : {
                message : 'The trailer link is not valid'
              }
            }
          },
          review : {
            // The group will be set as default (.form-group)
            validators : {
              stringLength : {
                max : 500,
                message : 'The review must be less than 500 characters long'
              }
            }
          },
          rating : {
            // The group will be set as default (.form-group)
            validators : {
              notEmpty : {
                message : 'The rating is required'
              }
            }
          }
        }
      });

    }

  }

});
"use strict";


angular.module('SmartAdmin.Forms').directive('bootstrapProductForm', function(){

  return {
    restrict: 'E',
    replace: true,
    templateUrl: 'app/_common/forms/directives/bootstrap-validation/bootstrap-product-form.tpl.html',
    link: function(scope, form){
      form.bootstrapValidator({
        feedbackIcons : {
          valid : 'glyphicon glyphicon-ok',
          invalid : 'glyphicon glyphicon-remove',
          validating : 'glyphicon glyphicon-refresh'
        },
        fields : {
          price : {
            validators : {
              notEmpty : {
                message : 'The price is required'
              },
              numeric : {
                message : 'The price must be a number'
              }
            }
          },
          amount : {
            validators : {
              notEmpty : {
                message : 'The amount is required'
              },
              numeric : {
                message : 'The amount must be a number'
              }
            }
          },
          color : {
            validators : {
              notEmpty : {
                message : 'The color is required'
              }
            }
          },
          size : {
            validators : {
              notEmpty : {
                message : 'The size is required'
              }
            }
          }
        }
      });
    }

  }
});
"use strict";


angular.module('SmartAdmin.Forms').directive('bootstrapProfileForm', function(){

  return {
    restrict: 'E',
    replace: true,
    templateUrl: 'app/_common/forms/directives/bootstrap-validation/bootstrap-profile-form.tpl.html',
    link: function(scope, form){
      form.bootstrapValidator({
        feedbackIcons : {
          valid : 'glyphicon glyphicon-ok',
          invalid : 'glyphicon glyphicon-remove',
          validating : 'glyphicon glyphicon-refresh'
        },
        fields : {
          email : {
            validators : {
              notEmpty : {
                message : 'The email address is required'
              },
              emailAddress : {
                message : 'The email address is not valid'
              }
            }
          },
          password : {
            validators : {
              notEmpty : {
                message : 'The password is required'
              }
            }
          }
        }
      });
    }

  }

});
"use strict";


angular.module('SmartAdmin.Forms').directive('bootstrapTogglingForm', function(){

  return {
    restrict: 'E',
    replace: true,
    templateUrl: 'app/_common/forms/directives/bootstrap-validation/bootstrap-toggling-form.tpl.html',
    link: function(scope, form){
      form.bootstrapValidator({
        feedbackIcons : {
          valid : 'glyphicon glyphicon-ok',
          invalid : 'glyphicon glyphicon-remove',
          validating : 'glyphicon glyphicon-refresh'
        },
        fields : {
          firstName : {
            validators : {
              notEmpty : {
                message : 'The first name is required'
              }
            }
          },
          lastName : {
            validators : {
              notEmpty : {
                message : 'The last name is required'
              }
            }
          },
          company : {
            validators : {
              notEmpty : {
                message : 'The company name is required'
              }
            }
          },
          // These fields will be validated when being visible
          job : {
            validators : {
              notEmpty : {
                message : 'The job title is required'
              }
            }
          },
          department : {
            validators : {
              notEmpty : {
                message : 'The department name is required'
              }
            }
          },
          mobilePhone : {
            validators : {
              notEmpty : {
                message : 'The mobile phone number is required'
              },
              digits : {
                message : 'The mobile phone number is not valid'
              }
            }
          },
          // These fields will be validated when being visible
          homePhone : {
            validators : {
              digits : {
                message : 'The home phone number is not valid'
              }
            }
          },
          officePhone : {
            validators : {
              digits : {
                message : 'The office phone number is not valid'
              }
            }
          }
        }
      }).find('button[data-toggle]').on('click', function() {
        var $target = $($(this).attr('data-toggle'));
        // Show or hide the additional fields
        // They will or will not be validated based on their visibilities
        $target.toggle();
        if (!$target.is(':visible')) {
          // Enable the submit buttons in case additional fields are not valid
          form.data('bootstrapValidator').disableSubmitButtons(false);
        }
      });
    }

  }



});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartCkEditor', function () {
  return {
    restrict: 'A',
    compile: function ( tElement) {
      tElement.removeAttr('smart-ck-editor data-smart-ck-editor');
      //CKEDITOR.basePath = 'bower_components/ckeditor/';

      CKEDITOR.replace( tElement.attr('name'), { height: '380px', startupFocus : true} );
    }
  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartDestroySummernote', function () {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-destroy-summernote data-smart-destroy-summernote')
      tElement.on('click', function() {
        angular.element(tAttributes.smartDestroySummernote).destroy();
      })
    }
  }
});

'use strict';

angular.module('SmartAdmin.Forms').directive('smartEditSummernote', function () {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-edit-summernote data-smart-edit-summernote');
      tElement.on('click', function(){
        angular.element(tAttributes.smartEditSummernote).summernote({
          focus : true
        });
      });
    }
  }
});

'use strict';

angular.module('SmartAdmin.Forms').directive('smartMarkdownEditor', function () {
  return {
    restrict: 'A',
    compile: function (element, attributes) {
      element.removeAttr('smart-markdown-editor data-smart-markdown-editor')

      var options = {
        autofocus:false,
        savable:true,
        fullscreen: {
          enable: false
        }
      };

      if(attributes.height){
        options.height = parseInt(attributes.height);
      }

      element.markdown(options);
    }
  }
});

'use strict';

angular.module('SmartAdmin.Forms').directive('smartSummernoteEditor', function (lazyScript) {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-summernote-editor data-smart-summernote-editor');

      var options = {
        focus : true,
        tabsize : 2
      };

      if(tAttributes.height){
        options.height = tAttributes.height;
      }

      lazyScript.register('/js/smart/build/vendor.ui.js').then(function(){
        tElement.summernote(options);
      });
    }
  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartCheckoutForm', function (formsCommon, lazyScript) {
  return {
    restrict: 'A',
    link: function (scope, form) {
      lazyScript.register('/js/smart/build/vendor.ui.js').then(function(){

        scope.countries = formsCommon.countries;

        form.validate(angular.extend({
          // Rules for form validation
          rules : {
            fname : {
              required : true
            },
            lname : {
              required : true
            },
            email : {
              required : true,
              email : true
            },
            phone : {
              required : true
            },
            country : {
              required : true
            },
            city : {
              required : true
            },
            code : {
              required : true,
              digits : true
            },
            address : {
              required : true
            },
            name : {
              required : true
            },
            card : {
              required : true,
              creditcard : true
            },
            cvv : {
              required : true,
              digits : true
            },
            month : {
              required : true
            },
            year : {
              required : true,
              digits : true
            }
          },

          // Messages for form validation
          messages : {
            fname : {
              required : 'Please enter your first name'
            },
            lname : {
              required : 'Please enter your last name'
            },
            email : {
              required : 'Please enter your email address',
              email : 'Please enter a VALID email address'
            },
            phone : {
              required : 'Please enter your phone number'
            },
            country : {
              required : 'Please select your country'
            },
            city : {
              required : 'Please enter your city'
            },
            code : {
              required : 'Please enter code',
              digits : 'Digits only please'
            },
            address : {
              required : 'Please enter your full address'
            },
            name : {
              required : 'Please enter name on your card'
            },
            card : {
              required : 'Please enter your card number'
            },
            cvv : {
              required : 'Enter CVV2',
              digits : 'Digits only'
            },
            month : {
              required : 'Select month'
            },
            year : {
              required : 'Enter year',
              digits : 'Digits only please'
            }
          }
        }, formsCommon.validateOptions));
      });
    }
  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartCommentForm', function (formsCommon, lazyScript) {
  return {
    restrict: 'A',
    link: function (scope, form) {
      lazyScript.register('/js/smart/build/vendor.ui.js').then(function(){
        form.validate(angular.extend({
          // Rules for form validation
          rules : {
            name : {
              required : true
            },
            email : {
              required : true,
              email : true
            },
            url : {
              url : true
            },
            comment : {
              required : true
            }
          },

          // Messages for form validation
          messages : {
            name : {
              required : 'Enter your name',
            },
            email : {
              required : 'Enter your email address',
              email : 'Enter a VALID email'
            },
            url : {
              email : 'Enter a VALID url'
            },
            comment : {
              required : 'Please enter your comment'
            }
          },

          // Ajax form submition
          submitHandler : function() {
            form.ajaxSubmit({
              success : function() {
                form.addClass('submited');
              }
            });
          }

        }, formsCommon.validateOptions));
      });

    }
  }
});

'use strict';

angular.module('SmartAdmin.Forms').directive('smartContactsForm', function (formsCommon, lazyScript) {
  return {
    restrict: 'A',
    link: function (scope, form) {
      lazyScript.register('/js/smart/build/vendor.ui.js').then(function(){
        form.validate(angular.extend({
          // Rules for form validation
          rules : {
            name : {
              required : true
            },
            email : {
              required : true,
              email : true
            },
            message : {
              required : true,
              minlength : 10
            }
          },

          // Messages for form validation
          messages : {
            name : {
              required : 'Please enter your name'
            },
            email : {
              required : 'Please enter your email address',
              email : 'Please enter a VALID email address'
            },
            message : {
              required : 'Please enter your message'
            }
          },

          // Ajax form submition
          submitHandler : function() {
            form.ajaxSubmit({
              success : function() {
                form.addClass('submited');
              }
            });
          }
        }, formsCommon.validateOptions));
      });
    }
  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartOrderForm', function (formsCommon, lazyScript) {
  return {
    restrict: 'E',
    link: function (scope, form) {
      lazyScript.register('/js/smart/build/vendor.ui.js').then(function(){
        form.validate(angular.extend({
          // Rules for form validation
          rules : {
            name : {
              required : true
            },
            email : {
              required : true,
              email : true
            },
            phone : {
              required : true
            },
            interested : {
              required : true
            },
            budget : {
              required : true
            }
          },

          // Messages for form validation
          messages : {
            name : {
              required : 'Please enter your name'
            },
            email : {
              required : 'Please enter your email address',
              email : 'Please enter a VALID email address'
            },
            phone : {
              required : 'Please enter your phone number'
            },
            interested : {
              required : 'Please select interested service'
            },
            budget : {
              required : 'Please select your budget'
            }
          },

        }, formsCommon.validateOptions));
      });

    }
  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartRegistrationForm', function (formsCommon, lazyScript) {
  return {
    restrict: 'A',
    link: function (scope, form, attributes) {
      lazyScript.register('/js/smart/build/vendor.ui.js').then(function(){
        form.validate(angular.extend({

          // Rules for form validation
          rules: {
            username: {
              required: true
            },
            email: {
              required: true,
              email: true
            },
            password: {
              required: true,
              minlength: 3,
              maxlength: 20
            },
            passwordConfirm: {
              required: true,
              minlength: 3,
              maxlength: 20,
              equalTo: '#password'
            },
            firstname: {
              required: true
            },
            lastname: {
              required: true
            },
            gender: {
              required: true
            },
            terms: {
              required: true
            }
          },

          // Messages for form validation
          messages: {
            login: {
              required: 'Please enter your login'
            },
            email: {
              required: 'Please enter your email address',
              email: 'Please enter a VALID email address'
            },
            password: {
              required: 'Please enter your password'
            },
            passwordConfirm: {
              required: 'Please enter your password one more time',
              equalTo: 'Please enter the same password as above'
            },
            firstname: {
              required: 'Please select your first name'
            },
            lastname: {
              required: 'Please select your last name'
            },
            gender: {
              required: 'Please select your gender'
            },
            terms: {
              required: 'You must agree with Terms and Conditions'
            }
          }

        }, formsCommon.validateOptions));
      });
    }
  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartReviewForm', function (formsCommon, lazyScript) {
  return {
    restrict: 'E',
    link: function (scope, form) {
      lazyScript.register('/js/smart/build/vendor.ui.js').then(function(){

        form.validate(angular.extend({
          // Rules for form validation
          rules : {
            name : {
              required : true
            },
            email : {
              required : true,
              email : true
            },
            review : {
              required : true,
              minlength : 20
            },
            quality : {
              required : true
            },
            reliability : {
              required : true
            },
            overall : {
              required : true
            }
          },

          // Messages for form validation
          messages : {
            name : {
              required : 'Please enter your name'
            },
            email : {
              required : 'Please enter your email address',
              email : '<i class="fa fa-warning"></i><strong>Please enter a VALID email addres</strong>'
            },
            review : {
              required : 'Please enter your review'
            },
            quality : {
              required : 'Please rate quality of the product'
            },
            reliability : {
              required : 'Please rate reliability of the product'
            },
            overall : {
              required : 'Please rate the product'
            }
          }

        }, formsCommon.validateOptions));
      });
    }
  }
});
'use strict';

angular.module('SmartAdmin.Forms').directive('smartJcrop', function ($q) {
  return {
    restrict: 'A',
    scope: {
      coords: '=',
      options: '=',
      selection: '='
    },
    link: function (scope, element, attributes) {
      var jcropApi, imageWidth, imageHeight, imageLoaded = $q.defer();

      var listeners = {
        onSelectHandlers: [],
        onChangeHandlers: [],
        onSelect: function (c) {
          angular.forEach(listeners.onSelectHandlers, function (handler) {
            handler.call(jcropApi, c)
          })
        },
        onChange: function (c) {
          angular.forEach(listeners.onChangeHandlers, function (handler) {
            handler.call(jcropApi, c)
          })
        }
      };

      if (attributes.coords) {
        var coordsUpdate = function (c) {
          scope.$apply(function () {
            scope.coords = c;
          });
        };
        listeners.onSelectHandlers.push(coordsUpdate);
        listeners.onChangeHandlers.push(coordsUpdate);
      }

      var $previewPane = $(attributes.smartJcropPreview),
        $previewContainer = $previewPane.find('.preview-container'),
        $previewImg = $previewPane.find('img');

      if ($previewPane.length && $previewImg.length) {
        var previewUpdate = function (coords) {
          if (parseInt(coords.w) > 0) {
            var rx = $previewContainer.width() / coords.w;
            var ry = $previewContainer.height() / coords.h;

            $previewImg.css({
              width: Math.round(rx * imageWidth) + 'px',
              height: Math.round(ry * imageHeight) + 'px',
              marginLeft: '-' + Math.round(rx * coords.x) + 'px',
              marginTop: '-' + Math.round(ry * coords.y) + 'px'
            });
          }
        };
        listeners.onSelectHandlers.push(previewUpdate);
        listeners.onChangeHandlers.push(previewUpdate);
      }


      var options = {
        onSelect: listeners.onSelect,
        onChange: listeners.onChange
      };

      if ($previewContainer.length) {
        options.aspectRatio = $previewContainer.width() / $previewContainer.height()
      }

      if (attributes.selection) {
        scope.$watch('selection', function (newVal, oldVal) {
          if (newVal != oldVal) {
            var rectangle = newVal == 'release' ? [imageWidth / 2, imageHeight / 2, imageWidth / 2, imageHeight / 2] : newVal;

            var callback = newVal == 'release' ? function () {
              jcropApi.release();
            } : angular.noop;

            imageLoaded.promise.then(function () {
              if (scope.options && scope.options.animate) {
                jcropApi.animateTo(rectangle, callback);
              } else {
                jcropApi.setSelect(rectangle);
              }
            });
          }
        });
      }

      if (attributes.options) {

        var optionNames = [
          'bgOpacity', 'bgColor', 'bgFade', 'shade', 'outerImage',
          'allowSelect', 'allowMove', 'allowResize',
          'aspectRatio'
        ];

        angular.forEach(optionNames, function (name) {
          if (scope.options[name])
            options[name] = scope.options[name]

          scope.$watch('options.' + name, function (newVal, oldVal) {
            if (newVal != oldVal) {
              imageLoaded.promise.then(function () {
                var update = {};
                update[name] = newVal;
                jcropApi.setOptions(update);
              });
            }
          });

        });


        scope.$watch('options.disabled', function (newVal, oldVal) {
          if (newVal != oldVal) {
            if (newVal) {
              jcropApi.disable();
            } else {
              jcropApi.enable();
            }
          }
        });

        scope.$watch('options.destroyed', function (newVal, oldVal) {
          if (newVal != oldVal) {
            if (newVal) {
              jcropApi.destroy();
            } else {
              _init();
            }
          }
        });

        scope.$watch('options.src', function (newVal, oldVal) {
          imageLoaded = $q.defer();
          if (newVal != oldVal) {
            jcropApi.setImage(scope.options.src, function () {
              imageLoaded.resolve();
            });
          }
        });

        var updateSize = function(){
          jcropApi.setOptions({
            minSize: [scope.options.minSizeWidth, scope.options.minSizeHeight],
            maxSize: [scope.options.maxSizeWidth, scope.options.maxSizeHeight]
          });
        };

        scope.$watch('options.minSizeWidth', function (newVal, oldVal) {
          if (newVal != oldVal) updateSize();
        });
        scope.$watch('options.minSizeHeight', function (newVal, oldVal) {
          if (newVal != oldVal) updateSize();
        });
        scope.$watch('options.maxSizeWidth', function (newVal, oldVal) {
          if (newVal != oldVal) updateSize();
        });
        scope.$watch('options.maxSizeHeight', function (newVal, oldVal) {
          if (newVal != oldVal) updateSize();
        });
      }

      var _init = function () {
        element.Jcrop(options, function () {
          jcropApi = this;
          // Use the API to get the real image size
          var bounds = this.getBounds();
          imageWidth = bounds[0];
          imageHeight = bounds[1];

          if (attributes.selection && angular.isArray(scope.selection)) {
            if (scope.options && scope.options.animate) {
              jcropApi.animateTo(scope.selection);
            } else {
              jcropApi.setSelect(scope.selection);
            }
          }
          imageLoaded.resolve();
        });
      };

      _init()


    }
  }
});