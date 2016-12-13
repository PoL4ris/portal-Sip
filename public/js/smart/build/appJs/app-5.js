
'use strict';

angular.module('app.ui').directive('smartJquiAutocomplete', function () {
  return {
    restrict: 'A',
    scope: {
      'source': '='
    },
    link: function (scope, element, attributes) {

      element.autocomplete({
        source: scope.source
      });
    }
  }
});
'use strict';

/*
 * CONVERT DIALOG TITLE TO HTML
 * REF: http://stackoverflow.com/questions/14488774/using-html-in-a-dialogs-title-in-jquery-ui-1-10
 */
$.widget("ui.dialog", $.extend({}, $.ui.dialog.prototype, {
  _title: function (title) {
    if (!this.options.title) {
      title.html("&#160;");
    } else {
      title.html(this.options.title);
    }
  }
}));


angular.module('app.ui').directive('smartJquiDialog', function () {

  var optionAttributes = ['autoOpen', 'modal', 'width', 'resizable'];

  var defaults = {
    width: Math.min($(window).width() * .7, 600),
    autoOpen: false,
    resizable: false
  };


  return {
    restrict: 'A',
    link: function (scope, element, attributes) {

      var title = element.find('[data-dialog-title]').remove().html();

      var options = _.clone(defaults);

      optionAttributes.forEach(function (option) {
        if (element.data(option)) {
          options[option] = element.data(option);
        }
      });

      var buttons = element.find('[data-dialog-buttons]').remove()
        .find('button').map(function (idx, button) {
          return {
            class: button.className,
            html: button.innerHTML,
            click: function () {
              if ($(button).data('action'))
                scope.$eval($(button).data('action'));
              element.dialog("close");
            }
          }
        });

      element.dialog(_.extend({
        title: title,
        buttons: buttons
      }, options));

    }
  }
});
'use strict';

//    $.widget("ui.dialog", $.extend({}, $.ui.dialog.prototype, {
//        _title: function (title) {
//            if (!this.options.title) {
//                title.html("&#160;");
//            } else {
//                title.html(this.options.title);
//            }
//        }
//    }));


angular.module('app.ui').directive('smartJquiDialogLauncher', function () {
  return {
    restrict: 'A',
    compile: function (element, attributes) {
      element.removeAttr('smart-jqui-dialog-launcher data-smart-jqui-dialog-launcher');
      element.on('click', function (e) {
        $(attributes.smartJquiDialogLauncher).dialog('open');
        e.preventDefault();
      })
    }
  }
});
'use strict';

angular.module('app.ui').directive('smartJquiDynamicTabs', function ($timeout) {


  function addDomEvents(element){

    $('#tabs2').tabs();

    var tabTitle = $("#tab_title"), tabContent = $("#tab_content"), tabTemplate = "<li style='position:relative;'> <span class='air air-top-left delete-tab' style='top:7px; left:7px;'><button class='btn btn-xs font-xs btn-default hover-transparent'><i class='fa fa-times'></i></button></span></span><a href='#{href}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; #{label}</a></li>", tabCounter = 2;

    var tabs = $('#tabs2').tabs();

    // modal dialog init: custom buttons and a "close" callback reseting the form inside
    var dialog = $("#addtab").dialog({
      autoOpen : false,
      width : 600,
      resizable : false,
      modal : true,
      buttons : [{
        html : "<i class='fa fa-times'></i>&nbsp; Cancel",
        "class" : "btn btn-default",
        click : function() {
          $(this).dialog("close");

        }
      }, {

        html : "<i class='fa fa-plus'></i>&nbsp; Add",
        "class" : "btn btn-danger",
        click : function() {
          addTab();
          $(this).dialog("close");
        }
      }]
    });

    // addTab form: calls addTab function on submit and closes the dialog
    var form = dialog.find("form").submit(function(event) {
      addTab();
      dialog.dialog("close");
      event.preventDefault();
    });

    // actual addTab function: adds new tab using the input from the form above
    function addTab() {
      var label = tabTitle.val() || "Tab " + tabCounter, id = "tabs-" + tabCounter, li = $(tabTemplate.replace(/#\{href\}/g, "#" + id).replace(/#\{label\}/g, label)), tabContentHtml = tabContent.val() || "Tab " + tabCounter + " content.";

      tabs.find(".ui-tabs-nav").append(li);
      tabs.append("<div id='" + id + "'><p>" + tabContentHtml + "</p></div>");
      tabs.tabs("refresh");
      tabCounter++;

      // clear fields
      $("#tab_title").val("");
      $("#tab_content").val("");
    }

    // addTab button: just opens the dialog
    $("#add_tab").button().click(function() {
      dialog.dialog("open");
    });

    // close icon: removing the tab on click
    $("#tabs2").on("click", 'span.delete-tab', function() {

      var panelId = $(this).closest("li").remove().attr("aria-controls");
      $("#" + panelId).remove();
      tabs.tabs("refresh");

    });

  }

  function link(element){

    $timeout(function(){
      addDomEvents(element);
    });

  }


  return {
    restrict: 'A',
    link: link
  }
});

'use strict';

angular.module('app.ui').directive('smartJquiMenu', function () {
  return {
    restrict: 'A',
    link: function (scope, element, attributes) {

      element.menu();
    }
  }
});
'use strict';

angular.module('app.ui').directive('smartJquiTabs', function () {
  return {
    restrict: 'A',
    link: function (scope, element, attributes) {

      element.tabs();
    }
  }
});
'use strict';

angular.module('app.ui').directive('smartNestable', function () {
  return {
    restrict: 'A',
    scope: {
      group: '@',
      output: '='
    },
    link: function (scope, element, attributes) {
      var options = {};
      if(scope.group){
        options.group = scope.group;
      }
      element.nestable(options);
      if(attributes.output){
        element.on('change', function(){
          scope.$apply(function(){
            scope.output = element.nestable('serialize');
          });
        });
        scope.output = element.nestable('serialize');
      }

    }
  }
});
'use strict';

angular.module('app.ui').directive('smartProgressbar', function (lazyScript) {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      lazyScript.register('/js/smart/build/vendor.ui.js').then(function(){
        tElement.removeAttr('smart-progressbar data-smart-progressbar');
        tElement.progressbar({
          display_text : 'fill'
        })
      })

    }
  }
});
'use strict';

angular.module('app.ui').directive('smartRideCarousel', function () {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {
      tElement.removeAttr('smart-ride-carousel data-smart-ride-carousel');
      tElement.carousel(tElement.data());
    }
  }
});
'use strict';

angular.module('app.ui').directive('smartSuperBox', function () {
  return {
    restrict: 'A',
    compile: function (tElement, tAttributes) {

      tElement.removeAttr('smart-super-box data-smart-super-box');

      tElement.SuperBox();
    }
  }
});
'use strict';

angular.module('app.ui').directive('smartTreeviewContent', function ($compile) {
  return {
    restrict: 'E',
    link: function (scope, element) {
      var $content = $(scope.item.content);

      function handleExpanded(){
        $content.find('>i')
          .toggleClass('fa-plus-circle', !scope.item.expanded)
          .toggleClass('fa-minus-circle', !!scope.item.expanded)

      }


      if (scope.item.children && scope.item.children.length) {
        $content.on('click', function(){
          scope.$apply(function(){
            scope.item.expanded = !scope.item.expanded;
            handleExpanded();
          });


        });
        handleExpanded();
      }

      element.replaceWith($content);


    }
  }
});

angular.module('app.ui').directive('smartTreeview', function ($compile, $sce) {
  return {
    restrict: 'A',
    scope: {
      'items': '='
    },
    template: '<li ng-class="{parent_li: item.children.length}" ng-repeat="item in items" role="treeitem">' +
    '<smart-treeview-content></smart-treeview-content>' +
    '<ul ng-if="item.children.length" smart-treeview ng-show="item.expanded"  items="item.children" role="group" class="smart-treeview-group" ></ul>' +
    '</li>',
    compile: function (element) {
      // Break the recursion loop by removing the contents
      var contents = element.contents().remove();
      var compiledContents;
      return {
        post: function (scope, element) {
          // Compile the contents
          if (!compiledContents) {
            compiledContents = $compile(contents);
          }
          // Re-add the compiled contents to the element
          compiledContents(scope, function (clone) {
            element.append(clone);
          });
        }
      };
    }
  };
});
"use strict";

angular.module('app.auth').directive('facebookSignin', function ($rootScope, ezfb) {
  return {
    replace: true,
    restrict: 'E',
    template: '<a class="btn btn-block btn-social btn-facebook"><i class="fa fa-facebook"></i> Sign in with Facebook</a>',
    link: function(scope, element){
      element.on('click', function(){
        ezfb.login(function (res) {
          if (res.authResponse) {
            $rootScope.$broadcast('event:facebook-signin-success', res.authResponse);
          }
        }, {scope: 'public_profile'});
      })

    }
  }
});
"use strict";

angular.module('app.auth').directive('googleSignin', function ($rootScope, GooglePlus) {
  return {
    restrict: 'E',
    template: '<a class="g-signin btn btn-block btn-social btn-google-plus"><i class="fa fa-google-plus"></i> Sign in with Google</a>',
    replace: true,
    link: function (scope, element) {
      element.on('click', function(){
        GooglePlus.login().then(function (authResult) {
          $rootScope.$broadcast('event:google-plus-signin-success', authResult);

        }, function (err) {
          $rootScope.$broadcast('event:google-plus-signin-failure', err);

        });
      })
    }
  };
});

'use strict';

angular.module('app.chat').factory('ChatApi', function ($q, $rootScope, User, $http, APP_CONFIG) {
  var dfd = $q.defer();
  var _user;
  var ChatSrv = {
    initialized: dfd.promise,
    users: [],
    messages: [],
    statuses: ['Online', 'Busy', 'Away', 'Log Off'],
    status: 'Online',
    setUser: function (user) {
      if (ChatSrv.users.indexOf(_user) != -1)
        ChatSrv.users.splice(ChatSrv.users.indexOf(_user), 1);
      _user = user;
      ChatSrv.users.push(_user);
    },
    sendMessage: function (text) {
      var message = {
        user: _user,
        body: text,
        date: new Date()
      };
      this.messages.push(message);
    }
  };


  $http.get(APP_CONFIG.apiRootUrl + '/chat.json').then(function(res){
    ChatSrv.messages = res.data.messages;
    ChatSrv.users = res.data.users;
    dfd.resolve();
  });

  ChatSrv.initialized.then(function () {

    User.initialized.then(function () {
      ChatSrv.setUser({
        username: User.username,
        picture: User.picture,
        status: ChatSrv.status
      });
    });

    $rootScope.$watch(function () {
      return User.username
    }, function (name, oldName) {
      if (name != oldName) {
        ChatSrv.setUser({
          username: User.username,
          picture: User.picture,
          status: ChatSrv.status
        });
      }
    });
  });


  return ChatSrv;

});
(function() {

  'use strict';

  /*
   * SMARTCHAT PLUGIN ARRAYS & CONFIG
   * Dependency: js/plugin/moment/moment.min.js
   *             js/plugin/cssemotions/jquery.cssemoticons.min.js
   *             js/smart-chat-ui/smart.chat.ui.js
   * (DO NOT CHANGE)
   */
  var boxList = [],
    showList = [],
    nameList = [],
    idList = [];
  /*
   * Width of the chat boxes, and the gap inbetween in pixel (minus padding)
   */
  var chatbox_config = {
    width: 200,
    gap: 35,
    offset: 0
  };



  /*
   * SMART CHAT ENGINE
   * Copyright (c) 2013 Wen Pu
   * Modified by MyOrange
   * All modifications made are hereby copyright (c) 2014-2015 MyOrange
   */

  // TODO: implement destroy()
  (function($) {
    $.widget("ui.chatbox", {
      options: {
        id: null, //id for the DOM element
        title: null, // title of the chatbox
        user: null, // can be anything associated with this chatbox
        hidden: false,
        offset: 0, // relative to right edge of the browser window
        width: 300, // width of the chatbox
        status: 'online', //
        alertmsg: null,
        alertshow: null,
        messageSent: function(id, user, msg) {
          // override this
          this.boxManager.addMsg(user.first_name, msg);
        },
        boxClosed: function(id) {
        }, // called when the close icon is clicked
        boxManager: {
          // thanks to the widget factory facility
          // similar to http://alexsexton.com/?p=51
          init: function(elem) {
            this.elem = elem;
          },
          addMsg: function(peer, msg) {
            var self = this;
            var box = self.elem.uiChatboxLog;
            var e = document.createElement('div');
            box.append(e);
            $(e).hide();

            var systemMessage = false;

            if (peer) {
              var peerName = document.createElement("b");
              $(peerName).text(peer + ": ");
              e.appendChild(peerName);
            } else {
              systemMessage = true;
            }

            var msgElement = document.createElement(
              systemMessage ? "i" : "span");
            $(msgElement).text(msg);
            e.appendChild(msgElement);
            $(e).addClass("ui-chatbox-msg");
            $(e).css("maxWidth", $(box).width());
            $(e).fadeIn();
            //$(e).prop( 'title', moment().calendar() ); // add dep: moment.js
            $(e).find("span").emoticonize(); // add dep: jquery.cssemoticons.js
            self._scrollToBottom();

            if (!self.elem.uiChatboxTitlebar.hasClass("ui-state-focus")
              && !self.highlightLock) {
              self.highlightLock = true;
              self.highlightBox();
            }
          },
          highlightBox: function() {
            var self = this;
            self.elem.uiChatboxTitlebar.effect("highlight", {}, 300);
            self.elem.uiChatbox.effect("bounce", {times: 2}, 300, function() {
              self.highlightLock = false;
              self._scrollToBottom();
            });
          },
          toggleBox: function() {
            this.elem.uiChatbox.toggle();
          },
          _scrollToBottom: function() {
            var box = this.elem.uiChatboxLog;
            box.scrollTop(box.get(0).scrollHeight);
          }
        }
      },
      toggleContent: function(event) {
        this.uiChatboxContent.toggle();
        if (this.uiChatboxContent.is(":visible")) {
          this.uiChatboxInputBox.focus();
        }
      },
      widget: function() {
        return this.uiChatbox
      },
      _create: function() {
        var self = this,
          options = self.options,
          title = options.title || "No Title",
          // chatbox
          uiChatbox = (self.uiChatbox = $('<div></div>'))
            .appendTo(document.body)
            .addClass('ui-widget ' +
              //'ui-corner-top ' +
              'ui-chatbox'
            )
            .attr('outline', 0)
            .focusin(function() {
              // ui-state-highlight is not really helpful here
              //self.uiChatbox.removeClass('ui-state-highlight');
              self.uiChatboxTitlebar.addClass('ui-state-focus');
            })
            .focusout(function() {
              self.uiChatboxTitlebar.removeClass('ui-state-focus');
            }),
          // titlebar
          uiChatboxTitlebar = (self.uiChatboxTitlebar = $('<div></div>'))
            .addClass('ui-widget-header ' +
              //'ui-corner-top ' +
              'ui-chatbox-titlebar ' +
              self.options.status +
              ' ui-dialog-header' // take advantage of dialog header style
            )
            .click(function(event) {
              self.toggleContent(event);
            })
            .appendTo(uiChatbox),
          uiChatboxTitle = (self.uiChatboxTitle = $('<span></span>'))
            .html(title)
            .appendTo(uiChatboxTitlebar),
          uiChatboxTitlebarClose = (self.uiChatboxTitlebarClose = $('<a href="#" rel="tooltip" data-placement="top" data-original-title="Hide"></a>'))
            .addClass(//'ui-corner-all ' +
              'ui-chatbox-icon '
            )
            .attr('role', 'button')
            .hover(function() { uiChatboxTitlebarClose.addClass('ui-state-hover'); },
              function() { uiChatboxTitlebarClose.removeClass('ui-state-hover'); })
            .click(function(event) {
              uiChatbox.hide();
              self.options.boxClosed(self.options.id);
              return false;
            })
            .appendTo(uiChatboxTitlebar),
          uiChatboxTitlebarCloseText = $('<i></i>')
            .addClass('fa ' +
              'fa-times')
            .appendTo(uiChatboxTitlebarClose),
          uiChatboxTitlebarMinimize = (self.uiChatboxTitlebarMinimize = $('<a href="#" rel="tooltip" data-placement="top" data-original-title="Minimize"></a>'))
            .addClass(//'ui-corner-all ' +
              'ui-chatbox-icon'
            )
            .attr('role', 'button')
            .hover(function() { uiChatboxTitlebarMinimize.addClass('ui-state-hover'); },
              function() { uiChatboxTitlebarMinimize.removeClass('ui-state-hover'); })
            .click(function(event) {
              self.toggleContent(event);
              return false;
            })
            .appendTo(uiChatboxTitlebar),
          uiChatboxTitlebarMinimizeText = $('<i></i>')
            .addClass('fa ' +
              'fa-minus')
            .appendTo(uiChatboxTitlebarMinimize),
          // content
          uiChatboxContent = (self.uiChatboxContent = $('<div class="'+ self.options.alertshow +'"><span class="alert-msg">'+ self.options.alertmsg + '</span></div>'))
            .addClass('ui-widget-content ' +
              'ui-chatbox-content '
            )
            .appendTo(uiChatbox),
          uiChatboxLog = (self.uiChatboxLog = self.element)
            .addClass('ui-widget-content ' +
              'ui-chatbox-log ' +
              'custom-scroll'
            )
            .appendTo(uiChatboxContent),
          uiChatboxInput = (self.uiChatboxInput = $('<div></div>'))
            .addClass('ui-widget-content ' +
              'ui-chatbox-input'
            )
            .click(function(event) {
              // anything?
            })
            .appendTo(uiChatboxContent),
          uiChatboxInputBox = (self.uiChatboxInputBox = $('<textarea></textarea>'))
            .addClass('ui-widget-content ' +
              'ui-chatbox-input-box '
            )
            .appendTo(uiChatboxInput)
            .keydown(function(event) {
              if (event.keyCode && event.keyCode == $.ui.keyCode.ENTER) {
                var msg = $.trim($(this).val());
                if (msg.length > 0) {
                  self.options.messageSent(self.options.id, self.options.user, msg);
                }
                $(this).val('');
                return false;
              }
            })
            .focusin(function() {
              uiChatboxInputBox.addClass('ui-chatbox-input-focus');
              var box = $(this).parent().prev();
              box.scrollTop(box.get(0).scrollHeight);
            })
            .focusout(function() {
              uiChatboxInputBox.removeClass('ui-chatbox-input-focus');
            });

        // disable selection
        uiChatboxTitlebar.find('*').add(uiChatboxTitlebar).disableSelection();

        // switch focus to input box when whatever clicked
        uiChatboxContent.children().click(function() {
          // click on any children, set focus on input box
          self.uiChatboxInputBox.focus();
        });

        self._setWidth(self.options.width);
        self._position(self.options.offset);

        self.options.boxManager.init(self);

        if (!self.options.hidden) {
          uiChatbox.show();
        }

        $(".ui-chatbox [rel=tooltip]").tooltip();
        //console.log("tooltip created");
      },
      _setOption: function(option, value) {
        if (value != null) {
          switch (option) {
            case "hidden":
              if (value)
                this.uiChatbox.hide();
              else
                this.uiChatbox.show();
              break;
            case "offset":
              this._position(value);
              break;
            case "width":
              this._setWidth(value);
              break;
          }
        }
        $.Widget.prototype._setOption.apply(this, arguments);
      },
      _setWidth: function(width) {
        this.uiChatbox.width((width + 28) + "px");
        //this.uiChatboxTitlebar.width((width + 28) + "px");
        //this.uiChatboxLog.width(width + "px");
        // this.uiChatboxInput.css("maxWidth", width + "px");
        // padding:2, boarder:2, margin:5
        this.uiChatboxInputBox.css("width", (width + 18) + "px");
      },
      _position: function(offset) {
        this.uiChatbox.css("right", offset);
      }
    });
  }(jQuery));


  /*
   * jQuery CSSEmoticons plugin 0.2.9
   *
   * Copyright (c) 2010 Steve Schwartz (JangoSteve)
   *
   * Dual licensed under the MIT and GPL licenses:
   *   http://www.opensource.org/licenses/mit-license.php
   *   http://www.gnu.org/licenses/gpl.html
   *
   * Date: Sun Oct 22 1:00:00 2010 -0500
   */
  (function($) {
    $.fn.emoticonize = function(options) {

      var opts = $.extend({}, $.fn.emoticonize.defaults, options);

      var escapeCharacters = [ ")", "(", "*", "[", "]", "{", "}", "|", "^", "<", ">", "\\", "?", "+", "=", "." ];

      var threeCharacterEmoticons = [
        // really weird bug if you have :{ and then have :{) in the same container anywhere *after* :{ then :{ doesn't get matched, e.g. :] :{ :) :{) :) :-) will match everything except :{
        //  But if you take out the :{) or even just move :{ to the right of :{) then everything works fine. This has something to do with the preMatch string below I think, because
        //  it'll work again if you set preMatch equal to '()'
        //  So for now, we'll just remove :{) from the emoticons, because who actually uses this mustache man anyway?
        // ":{)",
        ":-)", ":o)", ":c)", ":^)", ":-D", ":-(", ":-9", ";-)", ":-P", ":-p", ":-Þ", ":-b", ":-O", ":-/", ":-X", ":-#", ":'(", "B-)", "8-)", ";*(", ":-*", ":-\\",
        "?-)", // <== This is my own invention, it's a smiling pirate (with an eye-patch)!
        // and the twoCharacterEmoticons from below, but with a space inserted
        ": )", ": ]", "= ]", "= )", "8 )", ": }", ": D", "8 D", "X D", "x D", "= D", ": (", ": [", ": {", "= (", "; )", "; ]", "; D", ": P", ": p", "= P", "= p", ": b", ": Þ", ": O", "8 O", ": /", "= /", ": S", ": #", ": X", "B )", ": |", ": \\", "= \\", ": *", ": &gt;", ": &lt;"//, "* )"
      ];

      var twoCharacterEmoticons = [ // separate these out so that we can add a letter-spacing between the characters for better proportions
        ":)", ":]", "=]", "=)", "8)", ":}", ":D", ":(", ":[", ":{", "=(", ";)", ";]", ";D", ":P", ":p", "=P", "=p", ":b", ":Þ", ":O", ":/", "=/", ":S", ":#", ":X", "B)", ":|", ":\\", "=\\", ":*", ":&gt;", ":&lt;"//, "*)"
      ];

      var specialEmoticons = { // emoticons to be treated with a special class, hash specifies the additional class to add, along with standard css-emoticon class
        "&gt;:)": { cssClass: "red-emoticon small-emoticon spaced-emoticon" },
        "&gt;;)": { cssClass: "red-emoticon small-emoticon spaced-emoticon"},
        "&gt;:(": { cssClass: "red-emoticon small-emoticon spaced-emoticon" },
        "&gt;: )": { cssClass: "red-emoticon small-emoticon" },
        "&gt;; )": { cssClass: "red-emoticon small-emoticon"},
        "&gt;: (": { cssClass: "red-emoticon small-emoticon" },
        ";(":     { cssClass: "red-emoticon spaced-emoticon" },
        "&lt;3":  { cssClass: "pink-emoticon counter-rotated" },
        "O_O":    { cssClass: "no-rotate" },
        "o_o":    { cssClass: "no-rotate" },
        "0_o":    { cssClass: "no-rotate" },
        "O_o":    { cssClass: "no-rotate" },
        "T_T":    { cssClass: "no-rotate" },
        "^_^":    { cssClass: "no-rotate" },
        "O:)":    { cssClass: "small-emoticon spaced-emoticon" },
        "O: )":   { cssClass: "small-emoticon" },
        "8D":     { cssClass: "small-emoticon spaced-emoticon" },
        "XD":     { cssClass: "small-emoticon spaced-emoticon" },
        "xD":     { cssClass: "small-emoticon spaced-emoticon" },
        "=D":     { cssClass: "small-emoticon spaced-emoticon" },
        "8O":     { cssClass: "small-emoticon spaced-emoticon" },
        "[+=..]":  { cssClass: "no-rotate nintendo-controller" }
        //"OwO":  { cssClass: "no-rotate" }, // these emoticons overflow and look weird even if they're made even smaller, could probably fix this with some more css trickery
        //"O-O":  { cssClass: "no-rotate" },
        //"O=)":    { cssClass: "small-emoticon" }
      }

      var specialRegex = new RegExp( '(\\' + escapeCharacters.join('|\\') + ')', 'g' );
      // One of these characters must be present before the matched emoticon, or the matched emoticon must be the first character in the container HTML
      //  This is to ensure that the characters in the middle of HTML properties or URLs are not matched as emoticons
      //  Below matches ^ (first character in container HTML), \s (whitespace like space or tab), or \0 (NULL character)
      // (<\\S+.*>) matches <\\S+.*> (matches an HTML tag like <span> or <div>), but haven't quite gotten it working yet, need to push this fix now
      var preMatch = '(^|[\\s\\0])';

      for ( var i=threeCharacterEmoticons.length-1; i>=0; --i ){
        threeCharacterEmoticons[i] = threeCharacterEmoticons[i].replace(specialRegex,'\\$1');
        threeCharacterEmoticons[i] = new RegExp( preMatch+'(' + threeCharacterEmoticons[i] + ')', 'g' );
      }

      for ( var i=twoCharacterEmoticons.length-1; i>=0; --i ){
        twoCharacterEmoticons[i] = twoCharacterEmoticons[i].replace(specialRegex,'\\$1');
        twoCharacterEmoticons[i] = new RegExp( preMatch+'(' + twoCharacterEmoticons[i] + ')', 'g' );
      }

      for ( var emoticon in specialEmoticons ){
        specialEmoticons[emoticon].regexp = emoticon.replace(specialRegex,'\\$1');
        specialEmoticons[emoticon].regexp = new RegExp( preMatch+'(' + specialEmoticons[emoticon].regexp + ')', 'g' );
      }

      var exclude = 'span.css-emoticon';
      if(opts.exclude){ exclude += ','+opts.exclude; }
      var excludeArray = exclude.split(',')

      return this.not(exclude).each(function() {
        var container = $(this);
        var cssClass = 'css-emoticon'
        if(opts.animate){ cssClass += ' un-transformed-emoticon animated-emoticon'; }

        for( var emoticon in specialEmoticons ){
          var specialCssClass = cssClass + " " + specialEmoticons[emoticon].cssClass;
          container.html(container.html().replace(specialEmoticons[emoticon].regexp,"$1<span class='" + specialCssClass + "'>$2</span>"));
        }
        $(threeCharacterEmoticons).each(function(){
          container.html(container.html().replace(this,"$1<span class='" + cssClass + "'>$2</span>"));
        });
        $(twoCharacterEmoticons).each(function(){
          container.html(container.html().replace(this,"$1<span class='" + cssClass + " spaced-emoticon'>$2</span>"));
        });
        // fix emoticons that got matched more then once (where one emoticon is a subset of another emoticon), and thus got nested spans
        $.each(excludeArray,function(index,item){
          container.find($.trim(item)+" span.css-emoticon").each(function(){
            $(this).replaceWith($(this).text());
          });
        });
        if(opts.animate){
          setTimeout(function(){$('.un-transformed-emoticon').removeClass('un-transformed-emoticon');}, opts.delay);
        }
      });
    }

    $.fn.unemoticonize = function(options) {
      var opts = $.extend({}, $.fn.emoticonize.defaults, options);
      return this.each(function() {
        var container = $(this);
        container.find('span.css-emoticon').each(function(){
          // add delay equal to animate speed if animate is not false
          var span = $(this);
          if(opts.animate){
            span.addClass('un-transformed-emoticon');
            setTimeout(function(){span.replaceWith(span.text());}, opts.delay);
          }else{
            span.replaceWith(span.text());
          }
        });
      });
    }

    $.fn.emoticonize.defaults = {animate: true, delay: 500, exclude: 'pre,code,.no-emoticons'}
  })(jQuery);

  var chatboxManager = function () {

    var init = function (options) {
      $.extend(chatbox_config, options)
    };


    var delBox = function (id) {
      // TODO
    };

    var getNextOffset = function () {
      return (chatbox_config.width + chatbox_config.gap) * showList.length;
    };

    var boxClosedCallback = function (id) {
      // close button in the titlebar is clicked
      var idx = showList.indexOf(id);
      if (idx != -1) {
        showList.splice(idx, 1);
        var diff = chatbox_config.width + chatbox_config.gap;
        for (var i = idx; i < showList.length; i++) {
          chatbox_config.offset = $("#" + showList[i]).chatbox("option", "offset");
          $("#" + showList[i]).chatbox("option", "offset", chatbox_config.offset - diff);
        }
      } else {
        alert("NOTE: Id missing from array: " + id);
      }
    };

    // caller should guarantee the uniqueness of id
    var addBox = function (id, user, name) {
      var idx1 = showList.indexOf(id);
      var idx2 = boxList.indexOf(id);
      if (idx1 != -1) {
        // found one in show box, do nothing
      } else if (idx2 != -1) {
        // exists, but hidden
        // show it and put it back to showList
        $("#" + id).chatbox("option", "offset", getNextOffset());
        var manager = $("#" + id).chatbox("option", "boxManager");
        manager.toggleBox();
        showList.push(id);
      } else {
        var el = document.createElement('div');
        el.setAttribute('id', id);
        $(el).chatbox({
          id: id,
          user: user,
          title: '<i title="' + user.status + '"></i>' + user.first_name + " " + user.last_name,
          hidden: false,
          offset: getNextOffset(),
          width: chatbox_config.width,
          status: user.status,
          alertmsg: user.alertmsg,
          alertshow: user.alertshow,
          messageSent: dispatch,
          boxClosed: boxClosedCallback
        });
        boxList.push(id);
        showList.push(id);
        nameList.push(user.first_name);
      }
    };

    var messageSentCallback = function (id, user, msg) {
      var idx = boxList.indexOf(id);
      chatbox_config.messageSent(nameList[idx], msg);
    };

    // not used in demo
    var dispatch = function (id, user, msg) {
      //$("#log").append("<i>" + moment().calendar() + "</i> you said to <b>" + user.first_name + " " + user.last_name + ":</b> " + msg + "<br/>");
      if ($('#chatlog').length){
        $("#chatlog").append("You said to <b>" + user.first_name + " " + user.last_name + ":</b> " + msg + "<br/>").effect("highlight", {}, 500);;
      }
      $("#" + id).chatbox("option", "boxManager").addMsg("Me", msg);
    }

    return {
      init: init,
      addBox: addBox,
      delBox: delBox,
      dispatch: dispatch
    };
  }();

  var link = function (scope, element, attributes) {

    $('a[data-chat-id]').click(function (event, ui) {
      if(!$(this).hasClass('offline')){

        var $this = $(this),
          temp_chat_id = $this.attr("data-chat-id"),
          fname = $this.attr("data-chat-fname"),
          lname = $this.attr("data-chat-lname"),
          status = $this.attr("data-chat-status") || "online",
          alertmsg = $this.attr("data-chat-alertmsg"),
          alertshow =  $this.attr("data-chat-alertshow") || false;


        chatboxManager.addBox(temp_chat_id, {
          // dest:"dest" + counter,
          // not used in demo
          title: "username" + temp_chat_id,
          first_name: fname,
          last_name: lname,
          status: status,
          alertmsg: alertmsg,
          alertshow: alertshow
          //you can add your own options too
        });
      }

      event.preventDefault();

    });

  }

  angular.module('app.chat').directive('asideChatWidget', function (ChatApi) {
    return {
      restrict: 'A',
      replace: true,
      templateUrl: 'app/dashboard/chat/directives/aside-chat-widget.tpl.html',
      link: link
    }
  });

})();
"use strict";

angular.module('app.chat').directive('chatUsers', function(ChatApi){
  return {
    restrict: 'E',
    replace: true,
    templateUrl: 'app/dashboard/chat/directives/chat-users.tpl.html',
    scope: true,
    link: function(scope, element){
      scope.open = false;
      scope.openToggle = function(){
        scope.open = !scope.open;
      };

      scope.chatUserFilter = '';

      ChatApi.initialized.then(function () {
        scope.chatUsers = ChatApi.users;
      });
    }
  }
});
