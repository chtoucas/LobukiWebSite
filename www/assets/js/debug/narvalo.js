// Narvalo namespace.
(function(window, $) {
  var location = window.location;

  var narvalo = {
    isIntKey: function(keyCode) {
      // Standard keyboard numbers
      var isStandard = (keyCode > 47 && keyCode < 58),
          // Extended keyboard numbers (keypad)
          isExtended = (keyCode > 95 && keyCode < 106),
          // 8 Backspace, 9 TAB, 46 Forward Delete, 13 Return
          // 37 Left Arrow, 38 Up Arrow, 39 Right Arrow, 40 Down Arrow
          //validKeyCodes = ',8,9,13,37,38,39,40,46,116,',
          //isOther = validKeyCodes.indexOf(',' + keyCode + ',') > -1;
          isOther = ',8,9,13,37,38,39,40,46,116,'.indexOf(',' + keyCode + ',') > -1;

      return isStandard || isExtended || isOther;
    },

    // See: http://code.google.com/intl/fr-FR/web/ajaxcrawling/docs/specification.html
    parseHash: function(hash) {
      // WARNING: window.location.search starts with "#".
      hash = hash || location.hash.substring(1);

      var result = {
        anchor: '',
        hashbang: {}
      };

      if (!hash) {
        return result;
      }

      var parts = hash.split('!');

      if (parts[0]) {
        result.anchor = parts[0];

        if (parts[1]) {
          result.hashbang = narvalo.parseQuery(parts[1]);
        }
      }

      return result;
    },

    parseQuery: function(query) {
      // WARNING: window.location.search starts with "?".
      query = query || location.search.substring(1);

      var params = {};

      if (!query) {
        return params;
      }

      var regex = /([^&=]+)=?([^&]*)/g,
          decode = function(s) {
            return decodeURIComponent(s.replace(/\+/g, ' '));
          };

      var e;
      while (e = regex.exec(query)) {
        params[decode(e[1])] = decode(e[2]);
      }

      return params;
    },

    // See: http://blog.stevenlevithan.com/archives/parseuri
    parseUri: function(str, opts) {
      var o = $.extend({}, _parseUriOptions, opts),
          m = o.parser[o.strictMode ? 'strict' : 'loose'].exec(str),
          uri = {},
          i = 14;

      while (i--) {
        uri[o.key[i]] = m[i] || '';
      }

      uri[o.q.name] = {};
      uri[o.key[12]].replace(o.q.parser, function($1, $2, $3) {
        if ($2) {
          uri[o.q.name][$2] = $3;
        }
      });

      return uri;
    },

    redirect: function(href) {
      location.href = href;
    },

    /*
    extractHash: function(href) {
      href = href || location.href;

      var pos = href.indexOf("#"), url, hash;

      if (-1 == pos) {
        url = href;
        hash = "";
      } else {
        url = href.substring(0, pos);
        hash = href.substring(pos + 1);
      }

      return { url: url, hash: hash };
    },

    hashbangToQuery: function(hashbang) {

    },
    */

    setHashbang: function(href, hashbang) {
      href = href || location.href;

      var pos = href.indexOf('#'), url, hash;

      if (-1 == pos) {
        url = href;
        hash = '';
      } else {
        url = href.substring(0, pos);
        hash = href.substring(pos + 1);
      }

      var anchor = narvalo.parseHash(hash).anchor;

      return url + '#' + narvalo.serializeHash(anchor, hashbang);
    },

    serializeHash: function(anchor, hashbang) {
      return anchor + '!' + $.param(hashbang);
    }
  };

  var _parseUriOptions = {
    strictMode: false,
    key: ['source', 'protocol', 'authority', 'userInfo', 'user',
      'password', 'host', 'port', 'relative', 'path', 'directory',
      'file', 'query', 'anchor'],
    q: {
      name: 'queryKey',
      parser: /(?:^|&)([^&=]*)=?([^&]*)/g
    },
    parser: {
      strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
      loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
    }
  };

  /*
  (function() {
    // Cf. http://engineeredweb.com/blog/09/12/preloading-images-jquery-and-javascript
    var cache = [];
    // Arguments are image paths relative to the current page.
    $.preLoadImages = function() {
      var args_len = arguments.length;
      for (var i = args_len; i--;) {
        var cacheImage = document.createElement('img');
        cacheImage.src = arguments[i];
        cache.push(cacheImage);
      }
    }
  });
   */

  window['narvalo'] = narvalo;
})(this, jQuery);

