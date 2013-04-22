;

// ECMA-5 functions, if not already supported.
if (!Array.prototype.forEach) {
  Array.prototype.forEach = function(block, thisObject) {
    var len = this.length >>> 0;
    for (var i = 0; i < len; i++) {
      if (i in this) {
        block.call(thisObject, this[i], i, this);
      }
    }
  };
}

; (function($, narvalo) {
  var isNotIntPattern = new RegExp('[^0-9]+', 'g');
  var _routes = {};

  // Register Home Controller's routes.
  ['about', 'cgv', 'contact', 'faq', 'help', 'index', 'news', 'payment',
    'resellers', 'sitemap'].forEach(function(action) {
    _addRoute('home', action, false);
  });

  // Register Store Controller's routes.
  _addRoute('store', 'basket', _handleStoreBasket);
  _addRoute('store', 'category', _handleStoreCategory);
  _addRoute('store', 'collection', _handleStoreCollection);
  _addRoute('store', 'gift', _handleStoreGift);
  _addRoute('store', 'news', false);
  _addRoute('store', 'personalizeSticker', false);
  _addRoute('store', 'product', _handleStoreProduct);

  window['main'] = function(ctrl, action) {
    // On document's ready event.
    $(function() {
      $('a[rel=external]').on('click', function() {
        window.open(this.href);
        return false;
      });
    });

    var fn = _routes[_getRouteKey(ctrl, action)];

    if (fn) {
      fn();
    }
  };

  // {{{ jQuery.lightbox()

  $.fn.lightbox = function() {
    return this.each(function() {
      $(this).colorbox({
        transition: 'fade',
        slideshow: 'true',
        current: '{current} / {total}',
        previous: 'Précédent',
        next: 'Suivant',
        close: 'Fermer',
        slideshowStart: 'Démarrer',
        slideshowStop: 'Arrêter',
        maxHeight: '90%'
      });
    });
  };

  // }}}

  // {{{ _addRoute()

  function _addRoute(ctrl, action, fn) {
    _routes[_getRouteKey(ctrl, action)] = function() {
      $(function() {
        _initMaster();

        if (fn) {
          fn();
        }
      });
    };
  }

  // }}}
  // {{{ _getRouteKey()

  function _getRouteKey(ctrl, action) {
    return ctrl + '/' + action;
  }

  // }}}

  // {{{ _preloadColorbox()

  function _preloadColorbox() {
    yepnope(getPaths(['jquery.colorbox.js'], 'preload!'));
  }

  // }}}

  // {{{ _handleStoreBasket()

  function _handleStoreBasket() {
    yepnope([{
      load: getPaths(['jquery.colorbox.js']),
      complete: function() { $('#products a[rel=lightbox]').lightbox(); }
    }, {
      load: getPaths(['jquery.validate.js']),
      complete: _initStoreBasketOrder
    }]);

    _initStoreBasketProducts();
  };

  // }}}
  // {{{ _handleStoreCategory()

  function _handleStoreCategory() {
    var page = narvalo.parseQuery()['p'];

    if (page) {
      // Rewrite products' links to include a hashbang.
      $('#products A').on('click', function(evt) {
        evt.preventDefault();
        var target = $(this).attr('href');
        narvalo.redirect(
            narvalo.setHashbang(target, { p: page, f: 'category' }));
      });
    }

    _preloadColorbox();
  }

  // }}}
  // {{{ _handleStoreCollection()

  function _handleStoreCollection() {
    var page = narvalo.parseQuery()['p'];

    if (page) {
      $('#products A').on('click', function(evt) {
        evt.preventDefault();
        var target = $(this).attr('href');
        narvalo.redirect(
            narvalo.setHashbang(target, { p: page, f: 'collection' }));
      });
    }

    _preloadColorbox();
  }

  // }}}
  // {{{ _handleStoreGift()

  function _handleStoreGift() {
    yepnope({
      load: getPaths(['jquery.colorbox.js']),
      complete: function() { $('a[rel=lightbox]').lightbox(); }
    });
  }

  // }}}
  // {{{ _handleStoreProduct()

  function _handleStoreProduct() {
    var hash = narvalo.parseHash(),
        page = hash.hashbang.p;

    if (page !== undefined) {
      var from = hash.hashbang.f,
          id = '#nav-' + ('category' == from ? '1' : '2');
      $(id).on('click', function(evt) {
        evt.preventDefault();
        var target = $(this).attr('href'),
            anchor = narvalo.parseUri(target).anchor,
            href = target.replace(anchor, '') +
            '?' + $.param({p: page}) +
            (anchor ? '#' + anchor : '');
        narvalo.redirect(href);
      });
    }

    _initStoreProductImages();
    _initStoreProductForm();

    yepnope({
      load: getPaths(['jquery.colorbox.js']),
      complete: function() { $('a[rel=lightbox]').lightbox(); }
    });
  }

  // }}}

  // {{{ _initMaster()

  function _initMaster() {
    // Update basket count.
    _updateBasketMenu();
    // Initialize menu.
    _initMenu();
    // Configure global ajax feedback.
    $('#ajax_status').ajaxStart(function() {
      $(this).show();
    }).ajaxStop(function() {
      $(this).hide();
    });
  }

  // }}}
  // {{{ _initMenu()

  function _initMenu() {
    var IS_HOVER = '_hover';

    // Mouse hover shows submenu.
    $('#menu_categories LI.more').each(function() {
      var $this = $(this);
      // NB: don't use toggle. If the user click and stay on the entry
      // we do not want to display the submenu.
      // mouseenter ? mouseleave ?
      $this.on('mouseover', function() {
        $this.addClass(IS_HOVER);
        $this.children('ul').show();
        return;
      }).on('mouseout', function() {
        if ($this.hasClass(IS_HOVER)) {
          $this.removeClass(IS_HOVER);
          $this.children('ul').hide();
        }
        return;
      });
    });
  }

  // }}}
  // {{{ _updateBasketMenu()

  function _updateBasketMenu() {
    var $basket = $('#store_basket'),
        cookie = $.cookie('basket');

    if (cookie) {
      var count = parseInt(cookie);

      if (count > 0) {
        $basket.html('Panier (' + count + ')');
        $basket.addClass('active');
      } else {
        $basket.html('Panier');
        $basket.removeClass('active');
      }
    } else if ($basket.hasClass('active')) {
      $basket.html('Panier');
      $basket.removeClass('active');
    }
  }

  // }}}

  // {{{ _initStoreBasketProducts()

  function _initStoreBasketProducts() {
    $('#products').ajaxStop(function() {
      // Only update the DOM when there is no other ajax request running.
      var $this = $(this),
          url = $this.find('form').first().attr('action');

      $.ajax({
        timeout: 3000,
        async: true,
        cache: false,
        // Do not conflict with other global ajax events.
        global: false,
        type: 'GET',
        url: url,
        data: {},
        dataTypeString: 'html',
        // TODO Handle errors.
        success: function(data) {
          var $products = $('#products', data);

          if ($products.length > 0) {
            // Remove event handlers and update the DOM
            // as fast as possible.
            // XXX does not work with IE.
            //$this.empty()[0].innerHTML = $newproducts.html();
            $this.html($products.html());
            _initStoreBasketProducts();
          } else {
            // Remove the order form.
            $('#order').html('');
            $this.hide();
            $this.replaceWith('<p id=empty>Votre panier est vide</p>');
          }

          _updateBasketMenu();
        }
      });
    });

    $('#products TR').each(function() {
      var $this = $(this),
          $form = $this.find('.qtity form'),
          $oqtity = $form.find('INPUT:hidden[name=oqty]'),
          $qtity = $form.find('.qtity_val');

      $qtity.on('keydown', function(evt) {
        return narvalo.isIntKey(evt.which);
      }).on('blur', function() {
        $qtity.val($qtity.val().replace(isNotIntPattern, ''));

        if ('' == $qtity.val()) {
          // Empty value, we restore the original value.
          $qtity.val($oqtity.val());
        }

        if ($oqtity.val() != $qtity.val()) {
          _submitForm($form);
        }
      });

      $form.find('.minus').on('click', function() {
        $qtity.val(Math.max(0, parseInt($qtity.val()) - 1));
        _submitForm($form);
      });
      $form.find('.plus').on('click', function() {
        $qtity.val(Math.min(99, 1 + parseInt($qtity.val())));
        _submitForm($form);
      });
      $this.find('.delete form').on('submit', function(evt) {
        evt.preventDefault();
        _submitForm($(this));
      });
    });

    function _submitForm($form) {
      var $useAjax = $form.find('INPUT:hidden[name=ajax]');

      $useAjax.val('true');

      $.ajax({
        timeout: 3000,
        async: true,
        cache: false,
        global: true,
        type: 'POST',
        url: $form.attr('action'),
        data: $form.serialize(),
        dataTypeString: 'html',
        complete: function() {
          $useAjax.val('false');
        },
        // TODO Handle errors.
        success: function(data) {
          // Don't do anything, see the ajaxStop event.
        }
      });
    }
  }

  // }}}
  // {{{ _initStoreBasketOrder()

  function _initStoreBasketOrder() {
    var submitted = false,
        $mandatory = $('.mandatory'),
        $shipping = $('#shipping');

    if ($('#show:checked').length > 0) {
      $shipping.show();
    }

    // See: http://docs.jquery.com/Plugins/Validation/validate
    var validator = $('#order').validate({
      invalidHandler: function(form, validator) {
        // Show error.
        $mandatory.fadeOut('fast')
                 .addClass('highlight_mandatory')
                 .fadeIn();
      },
      submitHandler: function(form) {
        submitted = true;
        form.submit();
      },
      errorPlacement: function(error, elmt) {
        // Completely bypass individual error messages.
      },
      hightlight: function(elmt) {
        $(elmt).addClass('error');
      },
      unhightlight: function(elmt) {
        $(elmt).removeClass('error');
      },
      rules: {
        bname: { required: true, maxlength: 50 },
        bfirstname: { required: true, maxlength: 50 },
        bstreet: { required: true, maxlength: 200 },
        bzipcode: { required: true, maxlength: 5 },
        bcity: { required: true, maxlength: 50 },
        bemail: { required: true, email: true, maxlength: 50 },
        bphone: { digits: true, minlength: 10, maxlength: 10 },
        sname: { required: '#show:checked', maxlength: 50 },
        sfirstname: { required: '#show:checked', maxlength: 50 },
        sstreet: { required: '#show:checked', maxlength: 200 },
        szipcode: { required: '#show:checked', digits: true,
          maxlength: 5 },
        scity: { required: '#show:checked', maxlength: 50 },
        semail: { email: true, maxlength: 50 },
        sphone: { digits: '#show:checked', minlength: 10,
          maxlength: 10 }
      }
    });

    if ($('.highlight_mandatory').length > 0) {
      validator.form();
    }

    $('#show').on('click', function() {
      $(this).find('INPUT').attr('checked', 'checked');
      $shipping.show();
      if (submitted) {
        // Revalidate the form.
        validator.form();
      }
    });
    $('#hide').on('click', function() {
      $(this).find('INPUT').attr('checked', 'checked');
      $shipping.hide();
      if (submitted) {
        // Revalidate the form.
        validator.form();
      }
    });
  };

  // }}}

  // {{{ _initStoreProductImages()

  function _initStoreProductImages() {
    var currId = 1,
        $medium = $('#medium_imgs LI');

    $('#small_imgs IMG').each(function(id) {
      id++;
      $(this).hover(function() {
        if (id != currId) {
          $medium.each(function(j) {
            if (j == id - 1) {
              $(this).show();
            } else {
              $(this).hide();
            }
          });
          currId = id;
        }
      });
    });
  };

  // }}}
  // {{{ _initStoreProductForm()

  function _initStoreProductForm() {
    var $form = $('#add_to_basket'),
        url = $form.attr('action'),
        $useAjax = $form.find('INPUT:hidden[name=ajax]'),
        $update = $form.find('INPUT:hidden[name=update]'),
        $oqtity = $form.find('INPUT:hidden[name=oqty]'),
        $qtity = $form.find('INPUT#qty'),
        $feedback = $('#basket_feedback'),
        $basketBtn = $('#basket'),
        $submitBtn = $form.find('INPUT:submit');

    $form.find('.minus').on('click', function() {
      var update = 'true' == $update.val();
      $qtity.val(Math.max(update ? 0 : 1, parseInt($qtity.val()) - 1));
      _updateForm(false);
    });

    $form.find('.plus').on('click', function() {
      $qtity.val(Math.min(99, 1 + parseInt($qtity.val())));
      _updateForm(false);
    });

    _updateForm(false);

    $qtity.on('keydown', function(evt) {
      // XXX Should we always update the form?
      _updateForm(false);

      return narvalo.isIntKey(evt.which);
    }).on('blur', function() {
      $qtity.val($qtity.val().replace(isNotIntPattern, ''));

      _updateForm(true);
    });

    $form.on('submit', function(evt) {
      evt.preventDefault();

      var otxt = $submitBtn.val();

      $submitBtn.val('En cours...');
      $submitBtn.attr('disabled', 'disabled');

      $useAjax.val('true');

      var qtity = $qtity.val(),
          dataToSend = $form.serialize();

      $.ajax({
        timeout: 3000,
        async: true,
        cache: false,
        global: true,
        type: 'POST',
        url: url,
        data: dataToSend,
        dataTypeString: 'html',
        beforeSend: function() {
          if ($feedback.is(':visible')) {
            $feedback.find('p').addClass('loading');
          }
        },
        complete: function() {
          $useAjax.val('false');
        },
        success: function(data) {
          if ('ajax_result' != $(data).attr('id')) {
            // The result could be corrupted and at the same
            // time the operation might have completed
            // successfuly.
            // FIXME
            narvalo.redirect('/panier');
          }
          _feedback(data);
        },
        error: function(xhr, textStatus, errorThrown) {
          var msg = 'Une erreur est intervenue.' +
                        ' Veuillez réessayer plus tard';
          _feedback('<p class=KO>' + msg + '</p>');
        }
      });

      function _feedback(data) {
        var success = $(data).find('.OK').length > 0;

        // Stop current animations.
        $feedback.stop(true, true).hide();
        $feedback.html(data).fadeIn('slow');

        if (success) {
          if (qtity > 0) {
            $update.val('true');
            $oqtity.val(qtity);
          } else {
            // Product removed from basket.
            $update.val('false');
            $oqtity.val(1);
            $qtity.val(1);
            $feedback.fadeOut(3000);
          }

          _updateBasketMenu();
        }

        _updateForm(false);
      }
    });

    function _updateForm(cleanup) {
      var txt,
          update = 'true' == $update.val();

      // Cleanup data
      if (cleanup) {
        if ('' == $qtity.val()) {
          $qtity.val(update ? 0 : 1);
        }
      }

      // Update text
      if ($qtity.val() > 0) {
        txt = update ? 'Mettre à jour !' : 'Ajouter au panier !';
      } else {
        txt = 'Enlever du panier';
      }
      $submitBtn.val(txt);

      // Show or hide the submit button.
      if (!update || $oqtity.val() != $qtity.val()) {
        $basketBtn.show();
        //$update_notice.hide();
        $submitBtn.removeAttr('disabled');
      } else {
        $basketBtn.hide();
        //$update_notice.show();
        $submitBtn.attr('disabled', 'disabled');
      }
      if (update) {
        $qtity.addClass('update');
      } else {
        $qtity.removeClass('update');
      }
    }
  };

  // }}}

})(jQuery, narvalo);

