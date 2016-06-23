(function() {
  var LibraryTile;

  LibraryTile = (function() {
    function LibraryTile($element, options) {
      this.$element = $element;
      this.$markup = options.markup || this.__getDefaultMarkup();
      this.$uploadingMarkup = options.uploadingMarkup || this.__getDefaultUploadingMarkup();
      this.payloads = options.payloads || {};
      this.endInfo = options.endInfo || '.end-info';
      this.thumbnail = options.thumbnail;
      this.multiple = options.multiple || false;
      this.wrapper = options.holder || 'ul';
      this.holder = options.holder || 'li';
      this.nextLoader = options.nextLoader || '.next-libraries-loader';
      this.$nextLoader = $(this.nextLoader, this.$element);
      this.__attachEventToHolder(this.holder);
      this.$element.append('<div class="overlay hide"></div>');
      this.$element.slimScroll({
        height: options.height || '450px'
      }).bind('slimscrolling', (function(_this) {
        return function(e, pos) {
          return _this.checkPagination();
        };
      })(this));
      $(window).scroll((function(_this) {
        return function() {
          return _this.checkPagination();
        };
      })(this));
      $(".slimScrollBar").hide();
    }

    LibraryTile.prototype.setPayload = function(payloads) {
      return this.payloads = payloads;
    };

    LibraryTile.prototype.setMultiple = function(multiple) {
      return this.multiple = multiple;
    };

    LibraryTile.prototype.setMarkup = function(markup) {
      return this.$markup = $(markup);
    };

    LibraryTile.prototype.getMarkup = function() {
      return this.$markup;
    };

    LibraryTile.prototype.setUploadingMarkup = function(markup) {
      return this.$uploadingMarkup = $(markup);
    };

    LibraryTile.prototype.getUploadingMarkup = function() {
      return this.$uploadingMarkup;
    };

    LibraryTile.prototype.addUploading = function(identifier, caption) {
      var $markup;
      $markup = this.__buildUploadingMarkup(identifier, caption);
      return $("" + this.wrapper, this.$element).prepend($markup);
    };

    LibraryTile.prototype.setProgress = function(identifier, progress) {
      var $img, $li, img;
      $li = $(this.holder + "[data-upload-id=\"" + identifier + "\"]", this.$element);
      img = this.__getUploadingHolder("(" + progress + ")Uploading..");
      $('.img-placeholder', $li).html(img);
      $img = $('img', $li);
      return Holder.run({
        images: $img[0]
      });
    };

    LibraryTile.prototype.retryUpload = function(identifier) {
      var $li;
      $li = $(this.holder + "[data-upload-id=\"" + identifier + "\"]", this.$element);
      $li.removeClass('error');
      return $li.addClass('uploading');
    };

    LibraryTile.prototype.markAsError = function(identifier, message) {
      var $img, $li, img;
      $li = $(this.holder + "[data-upload-id=\"" + identifier + "\"]", this.$element);
      img = this.__getUploadingHolder("Error");
      $img = $('img', $li);
      $img.attr({
        'data-src': img
      });
      Holder.run({
        images: $img[0]
      });
      $li.removeClass('uploading').addClass('error');
      return $li.data('errorMessage', message);
    };

    LibraryTile.prototype.markAsSuccess = function(identifier, data) {
      var $img, $li, thumbnail;
      thumbnail = this.__getThumbnail(data);
      $li = $(this.holder + "[data-upload-id=\"" + identifier + "\"]", this.$element);
      $li.removeClass('uploading');
      $li.data('meta', data);
      $li.attr('id', "library-" + data.id);
      $img = $("img", $li);
      return $img.prop('src', thumbnail);
    };

    LibraryTile.prototype.remove = function(identifier) {
      var $li;
      $li = $(this.holder + "[data-upload-id=\"" + identifier + "\"]", this.$element);
      return $li.remove();
    };

    LibraryTile.prototype.refineSearch = function(url, payloads) {
      if (payloads == null) {
        payloads = {};
      }
      $('.overlay', this.$element).removeClass('hide');
      this.$element.trigger("media.library.tile.refining-search");
      return this.__loadMore(url, payloads, (function(_this) {
        return function() {
          $(_this.holder + ":not(" + _this.nextLoader + ")", _this.$element).remove();
          $('.overlay', _this.$element).addClass('hide');
          return _this.$element.trigger("media.library.tile.refined-search");
        };
      })(this));
    };

    LibraryTile.prototype.checkPagination = function() {
      var url;
      if (this.$nextLoader.is(':within-viewport') && !this.$nextLoader.data('loading')) {
        url = $('a', this.$nextLoader).prop('href');
        return this.__loadMore(url);
      }
    };

    LibraryTile.prototype.clearSelection = function() {
      return $(this.holder, this.$element).removeClass("selected");
    };

    LibraryTile.prototype.__getUploadingHolder = function(text) {
      return "<img data-src=\"holder.js/149x149?auto=yes&bg=ecf0f5&text=" + text + "\" />";
    };

    LibraryTile.prototype.__loadMore = function(url, payloads, callback) {
      var items;
      if (payloads == null) {
        payloads = {};
      }
      payloads = this.__normalizePayloads(payloads);
      url = "" + url + payloads;
      items = [];
      this.$element.trigger("media.library.tile.loading");
      this.$nextLoader.data('loading', true);
      return $.get(url, (function(_this) {
        return function(result) {
          var i, item, len, ref;
          if (typeof callback === 'function') {
            callback.apply(_this);
          }
          ref = result.libraries.data;
          for (i = 0, len = ref.length; i < len; i++) {
            item = ref[i];
            _this.$nextLoader.before(_this.__buildMarkup(item));
          }
          if (result.libraries.next_page_url) {
            $('a', _this.$nextLoader).prop('href', result.libraries.next_page_url);
            _this.$nextLoader.data('loading', false);
            $(_this.endInfo, _this.$element).addClass('hide');
            _this.checkPagination();
          } else {
            _this.$nextLoader.addClass('hide');
            $(_this.endInfo, _this.$element).removeClass('hide');
          }
          return _this.$element.trigger("media.library.tile.loaded");
        };
      })(this), 'json');
    };

    LibraryTile.prototype.__normalizePayloads = function(payloads) {
      var globalPayloads;
      globalPayloads = this.payloads;
      if (typeof payloads === 'function') {
        payloads = payloads.apply(this);
      }
      if (typeof this.payloads === 'function') {
        globalPayloads = this.payloads.apply(this);
      }
      payloads = $.extend(globalPayloads, payloads, true);
      payloads['_'] = Math.random();
      payloads = this.__stringifyPayload(payloads);
      return payloads;
    };

    LibraryTile.prototype.__getThumbnail = function(library) {
      if (typeof this.thumbnail === 'function') {
        return this.thumbnail.apply(this, [library]);
      } else {
        return this.thumbnail;
      }
    };

    LibraryTile.prototype.__getDefaultUploadingMarkup = function() {
      return $("<li class=\"uploading\" > <a class=\"thumbnail\"> <div class=\"img-placeholder\"></div> <div class=\"caption\"></div> <div class=\"btn-action-wrapper\"> <div class=\"btn-action\"> <span class=\"fa-stack fa-lg btn-resume hide\"> <i class=\"fa fa-circle fa-stack-2x\"></i> <i class=\"fa fa-repeat fa-stack-1x fa-inverse\"></i> </span> <span class=\"fa-stack fa-lg btn-pause\"> <i class=\"fa fa-circle fa-stack-2x\"></i> <i class=\"fa fa-pause fa-stack-1x fa-inverse\"></i> </span> <span class=\"fa-stack fa-lg btn-cancel\"> <i class=\"fa fa-circle fa-stack-2x\"></i> <i class=\"fa fa-times fa-stack-1x fa-inverse\"></i> </span> </div> </div> </a> </li>");
    };

    LibraryTile.prototype.__getDefaultMarkup = function() {
      return $("<li> <a class=\"thumbnail\"> <div class=\"img-placeholder\"> <img /> </div> <div class=\"caption\"></div> </a> </li>");
    };

    LibraryTile.prototype.__buildUploadingMarkup = function(identifier, caption) {
      var $img, $markup, img;
      img = this.__getUploadingHolder('Uploading...');
      $markup = this.getUploadingMarkup().clone();
      if ($markup instanceof $) {
        $markup.attr("data-upload-id", "" + identifier);
        $('.img-placeholder', $markup).html(img);
        $('.caption', $markup).text(caption);
        $img = $('img', $markup);
        Holder.run({
          images: $img[0]
        });
      } else if (typeof $markup === 'function') {
        $markup = $markup.apply(this, [identifier, caption]);
      }
      return $markup;
    };

    LibraryTile.prototype.__buildMarkup = function(data) {
      var $markup, thumbnail;
      thumbnail = this.__getThumbnail(data);
      $markup = this.getMarkup().clone();
      if ($markup instanceof $) {
        $markup.attr("id", "library-" + data.id).data("meta", data);
        $('img', $markup).prop('src', thumbnail).prop('alt', data.description.title);
        $('.caption', $markup).text(data.description.title);
      } else if (typeof $markup === 'function') {
        $markup = $markup.apply(this, [data]);
      }
      return $markup;
    };

    LibraryTile.prototype.__attachEventToHolder = function(holder) {
      this.$element.on('click', this.nextLoader + " a", (function(_this) {
        return function(e) {
          return false;
        };
      })(this));
      return this.$element.on('click', holder, (function(_this) {
        return function(e) {
          var actionButtons, context, error, isUploading, meta, target;
          context = $(e.currentTarget);
          target = $(e.target);
          actionButtons = target.parents('.btn-action');
          isUploading = context.hasClass('uploading');
          if (isUploading || actionButtons.length) {
            return;
          }
          if (!_this.multiple) {
            $(holder, _this.$element).removeClass("selected");
          }
          context.toggleClass("selected");
          meta = context.data("meta");
          error = context.data('error-message');
          if (typeof meta === 'string') {
            meta = JSON.parse(meta);
          }
          if (typeof error === 'string') {
            error = JSON.parse(error);
          }
          if (context.hasClass('selected')) {
            return context.trigger("media.library.tile.selected", [meta, error]);
          } else {
            return context.trigger("media.library.tile.unselected", [meta, error]);
          }
        };
      })(this));
    };

    LibraryTile.prototype.__stringifyPayload = function(payloads) {
      var k, string, v;
      string = "";
      for (k in payloads) {
        v = payloads[k];
        string = string + "&" + k + "=" + v;
      }
      return string;
    };

    return LibraryTile;

  })();

  $.fn.libraryTile = function(option) {
    var args, defaults;
    args = arguments;
    defaults = {
      height: "450px"
    };
    this.each(function() {
      var $this, argsToSent, data, k, options, v;
      $this = $(this);
      data = $this.data('library.tile');
      options = $.extend({}, defaults, $this.data(), typeof option === 'object' && option);
      if (!data) {
        $this.data('library.tile', (data = new LibraryTile($this, options)));
      }
      if (typeof option === 'string') {
        argsToSent = [];
        for (k in args) {
          v = args[k];
          if (k > 0) {
            argsToSent.push(v);
          }
        }
        return data[option].apply(data, argsToSent);
      }
    });
    return this;
  };

}).call(this);

//# sourceMappingURL=tile.js.map
