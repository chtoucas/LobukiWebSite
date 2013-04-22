// ECMA-5 map function, if not already available.
if (!Array.prototype.map) {
  Array.prototype.map = function(fun /*, thisp*/) {
    var len = this.length >>> 0;
    var res = new Array(len);
    var thisp = arguments[1];

    for (var i = 0; i < len; i++) {
      if (i in this) {
        res[i] = fun.call(thisp, this[i], i, this);
      }
    }
    return res;
  };
}

(function(window) {
  var basePath = BASE + (DEBUG ? 'debug/' : '');
  var scripts = DEBUG
    ? ['jquery.js', 'jquery.cookie.js', 'narvalo.js', 'site.js']
    : ['site.js'];

  window.getPaths = function(files, opt_prefix) {
    var prefix = (opt_prefix || '') + basePath;
    return files.map(function(file) {
      return prefix + file;
    });
  };

  yepnope([{
    // Google Analytics.
    test: window._gaq || false,
    yep: '//www.google-analytics.com/ga.js'
  }, {
    // Main.
    load: getPaths(scripts),
    complete: function() {
      main(ROUTE[0], ROUTE[1]);
    }
  }]);
})(this);
