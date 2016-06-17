(function() {
  var LibraryDisplay;

  LibraryDisplay = (function() {
    function LibraryDisplay($element, options) {
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
        height: '450px'
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

    LibraryDisplay.prototype.setPayload = function(payloads) {
      return this.payloads = payloads;
    };

    LibraryDisplay.prototype.setMarkup = function(markup) {
      return this.$markup = $(markup);
    };

    LibraryDisplay.prototype.getMarkup = function() {
      return this.$markup;
    };

    LibraryDisplay.prototype.setUploadingMarkup = function(markup) {
      return this.$uploadingMarkup = $(markup);
    };

    LibraryDisplay.prototype.getUploadingMarkup = function() {
      return this.$uploadingMarkup;
    };

    LibraryDisplay.prototype.addUploading = function(identifier, caption) {
      var $markup;
      $markup = this.__buildUploadingMarkup(identifier, caption);
      return $("" + this.wrapper, this.$element).prepend($markup);
    };

    LibraryDisplay.prototype.setProgress = function(identifier, progress) {
      var $img, $li, img;
      $li = $(this.holder + "[data-upload-id=\"" + identifier + "\"]", this.$element);
      img = this.__getUploadingHolder("(" + progress + ")Uploading..");
      $('.img-placeholder', $li).html(img);
      $img = $('img', $li);
      return Holder.run({
        images: $img[0]
      });
    };

    LibraryDisplay.prototype.retryUpload = function(identifier) {
      var $li;
      $li = $(this.holder + "[data-upload-id=\"" + identifier + "\"]", this.$element);
      $li.removeClass('error');
      return $li.addClass('uploading');
    };

    LibraryDisplay.prototype.markAsError = function(identifier, message) {
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

    LibraryDisplay.prototype.markAsSuccess = function(identifier, data) {
      var $img, $li, thumbnail;
      thumbnail = this.__getThumbnail(data);
      $li = $(this.holder + "[data-upload-id=\"" + identifier + "\"]", this.$element);
      $li.removeClass('uploading');
      $li.data('meta', data);
      $img = $("img", $li);
      return $img.prop('src', thumbnail);
    };

    LibraryDisplay.prototype.remove = function(identifier) {
      var $li;
      $li = $(this.holder + "[data-upload-id=\"" + identifier + "\"]", this.$element);
      return $li.remove();
    };

    LibraryDisplay.prototype.refineSearch = function(url, payloads) {
      var items;
      if (payloads == null) {
        payloads = {};
      }
      payloads = this.__normalizePayloads(payloads);
      url = "" + url + payloads;
      items = [];
      $('.overlay', this.$element).removeClass('hide');
      this.$nextLoader.addClass('hide');
      this.$element.trigger("media.library.refining-search");
      return $.get(url, (function(_this) {
        return function(result) {
          var i, item, len, ref;
          $(_this.holder + ":not(" + _this.nextLoader + ")", _this.$element).remove();
          ref = result.libraries.data;
          for (i = 0, len = ref.length; i < len; i++) {
            item = ref[i];
            _this.$nextLoader.before(_this.__buildMarkup(item));
          }
          $('.overlay', _this.$element).addClass('hide');
          if (result.libraries.next_page_url) {
            $('a', _this.$nextLoader).prop('href', result.libraries.next_page_url);
            _this.$nextLoader.data('loading', false);
            _this.$nextLoader.removeClass('hide');
            $(_this.endInfo, _this.$element).addClass('hide');
          } else {
            _this.$nextLoader.addClass('hide');
            $(_this.endInfo, _this.$element).removeClass('hide');
          }
          return _this.$element.trigger("media.library.refined-search");
        };
      })(this), 'json');
    };

    LibraryDisplay.prototype.checkPagination = function() {
      var url;
      if (this.$nextLoader.is(':within-viewport') && !this.$nextLoader.data('loading')) {
        this.$nextLoader.data('loading', true);
        url = $('a', this.$nextLoader).prop('href');
        return this.__loadMore(url);
      }
    };

    LibraryDisplay.prototype.__getUploadingHolder = function(text) {
      return "<img data-src=\"holder.js/149x149?auto=yes&bg=ecf0f5&text=" + text + "\" />";
    };

    LibraryDisplay.prototype.__loadMore = function(url, payloads) {
      var items;
      if (payloads == null) {
        payloads = {};
      }
      payloads = this.__normalizePayloads(payloads);
      url = "" + url + payloads;
      items = [];
      return $.get(url, (function(_this) {
        return function(result) {
          var i, item, len, ref;
          ref = result.libraries.data;
          for (i = 0, len = ref.length; i < len; i++) {
            item = ref[i];
            _this.$nextLoader.before(_this.__buildMarkup(item));
          }
          if (result.libraries.next_page_url) {
            $('a', _this.$nextLoader).prop('href', result.libraries.next_page_url);
            _this.$nextLoader.data('loading', false);
            return _this.checkPagination();
          } else {
            _this.$nextLoader.addClass('hide');
            return $(_this.endInfo, _this.$element).removeClass('hide');
          }
        };
      })(this), 'json');
    };

    LibraryDisplay.prototype.__normalizePayloads = function(payloads) {
      var globalPayloads;
      globalPayloads = this.payloads;
      if (typeof payloads === 'function') {
        payloads = payloads.apply(this);
      }
      if (typeof this.payloads === 'function') {
        globalPayloads = this.payloads.apply(this);
      }
      payloads = $.extend(globalPayloads, payloads, true);
      payloads = this.__stringifyPayload(payloads);
      return payloads;
    };

    LibraryDisplay.prototype.__getThumbnail = function(library) {
      if (typeof this.thumbnail === 'function') {
        return this.thumbnail.apply(this, [library]);
      } else {
        return this.thumbnail;
      }
    };

    LibraryDisplay.prototype.__getDefaultUploadingMarkup = function() {
      return $("<li class=\"uploading\" > <a class=\"thumbnail\"> <div class=\"img-placeholder\"></div> <div class=\"caption\"></div> <div class=\"btn-action-wrapper\"> <div class=\"btn-action\"> <span class=\"fa-stack fa-lg btn-resume hide\"> <i class=\"fa fa-circle fa-stack-2x\"></i> <i class=\"fa fa-repeat fa-stack-1x fa-inverse\"></i> </span> <span class=\"fa-stack fa-lg btn-pause\"> <i class=\"fa fa-circle fa-stack-2x\"></i> <i class=\"fa fa-pause fa-stack-1x fa-inverse\"></i> </span> <span class=\"fa-stack fa-lg btn-cancel\"> <i class=\"fa fa-circle fa-stack-2x\"></i> <i class=\"fa fa-times fa-stack-1x fa-inverse\"></i> </span> </div> </div> </a> </li>");
    };

    LibraryDisplay.prototype.__getDefaultMarkup = function() {
      return $("<li> <a class=\"thumbnail\"> <div class=\"img-placeholder\"> <img /> </div> <div class=\"caption\"></div> </a> </li>");
    };

    LibraryDisplay.prototype.__buildUploadingMarkup = function(identifier, caption) {
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

    LibraryDisplay.prototype.__buildMarkup = function(data) {
      var $markup, thumbnail;
      thumbnail = this.__getThumbnail(data);
      $markup = this.getMarkup().clone();
      if ($markup instanceof $) {
        $markup.prop("id", "library-" + data.id).data("meta", data);
        $('img', $markup).prop('src', thumbnail).prop('alt', data.description.title);
        $('.caption', $markup).text(data.description.title);
      } else if (typeof $markup === 'function') {
        $markup = $markup.apply(this, [data]);
      }
      return $markup;
    };

    LibraryDisplay.prototype.__attachEventToHolder = function(holder) {
      this.$element.on('click', this.nextLoader + " a", (function(_this) {
        return function(e) {
          return false;
        };
      })(this));
      return this.$element.on('click', holder, (function(_this) {
        return function(e) {
          var context, error, meta;
          context = $(e.currentTarget);
          if (context.hasClass('uploading')) {
            false;
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
            return context.trigger("media.library.selected", [meta, error]);
          } else {
            return context.trigger("media.library.unselected", [meta, error]);
          }
        };
      })(this));
    };

    LibraryDisplay.prototype.__stringifyPayload = function(payloads) {
      var k, string, v;
      string = "";
      for (k in payloads) {
        v = payloads[k];
        string = string + "&" + k + "=" + v;
      }
      return string;
    };

    return LibraryDisplay;

  })();

  $.fn.libraryDisplay = function(option) {
    var args, defaults;
    args = arguments;
    defaults = {
      height: "450px"
    };
    this.each(function() {
      var $this, argsToSent, data, k, options, v;
      $this = $(this);
      data = $this.data('library.display');
      options = $.extend({}, defaults, $this.data(), typeof option === 'object' && option);
      if (!data) {
        $this.data('library.display', (data = new LibraryDisplay($this, options)));
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

//# sourceMappingURL=display.js.map
