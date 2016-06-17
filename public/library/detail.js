(function() {
  var LibraryDetail;

  LibraryDetail = (function() {
    function LibraryDetail($element, options) {
      this.$element = $element;
      this.thumbnail = options.thumbnail;
      this.largePreview = options.largePreview;
    }

    LibraryDetail.prototype.showDetail = function(identifier, path, mime, descriptions) {
      var preview;
      this.hideError();
      $('.nothing-selected', this.$element).addClass('hide');
      preview = this.__buildPreview(identifier, path, mime, descriptions);
      $(".media-full-preview", this.$element).html(preview);
      $('.media-full-preview audio, .media-full-preview video', this.$element).mediaelementplayer({
        pluginPath: '',
        enablePluginDebug: false,
        enableAutosize: true,
        enableKeyboard: true,
        audioHeight: 225,
        features: ['playpause', 'progress', 'current', 'duration', 'tracks', 'volume', 'backlight'],
        timerRate: 250,
        success: function(media, node, player) {
          return $("#" + node.id + "-mode").html("mode: " + media.pluginType);
        }
      });
      $("a.fancybox", this.$element).fancybox();
      $('.media-preview-container', this.$element).removeClass('hide');
      return $('.selection-exist', this.$element).removeClass('hide');
    };

    LibraryDetail.prototype.showError = function(errors) {
      if (errors == null) {
        errors = [];
      }
      this.clearDetail();
      this.hideDetail();
      errors = this.__buildErrors(errors);
      return $('.media-errors', this.$element).html(errors).removeClass('hide');
    };

    LibraryDetail.prototype.clearDetail = function() {
      this.destroyDetail();
      $('.selection-exist', this.$element).addClass('hide');
      return $('.nothing-selected', this.$element).removeClass('hide');
    };

    LibraryDetail.prototype.hideDetail = function() {
      return $('.media-preview-container', this.$element).addClass('hide');
    };

    LibraryDetail.prototype.destroyDetail = function() {
      return $('.media-full-preview', this.$element).html('');
    };

    LibraryDetail.prototype.hideError = function() {
      $('.media-errors', this.$element).addClass('hide');
      return $('.media-preview-container', this.$element).removeClass('hide');
    };

    LibraryDetail.prototype.__buildErrors = function(errors) {
      var parsed;
      parsed = '';
      if ($.isArray(errors)) {
        $.each(errors, function(key, value) {
          return parsed = parsed + "<li>" + value + "</li>";
        });
      } else {
        parsed = "<li>" + errors + "</li>";
      }
      return "<ul>" + parsed + "</ul>";
    };

    LibraryDetail.prototype.__buildPreview = function(identifier, path, mime, descriptions) {
      var largePreview, preview, thumbnail;
      thumbnail = this.__getPreview(identifier, path, mime);
      if (isImage(mime)) {
        largePreview = this.__getLargePreview(identifier, path, mime);
        preview = "<a class=\"fancybox fancybox.image\" href=\"" + largePreview + "\" title=\"uploads/" + descriptions.title + "\" > <img src=\"" + thumbnail + "\" alt=\"" + descriptions.title + "\" /> </a>";
      } else if (isVideo(mime)) {
        preview = "<video width=\"300\" height=\"225\" poster=\"" + thumbnail + "\"> <source type=\"" + mime + "\" src=\"uploads/" + descriptions.path + "\" /> </video>";
      } else if (isAudio(mime)) {
        preview = "<audio width=\"300\" poster=\"" + thumbnail + "\"> <source type=\"" + mime + "\" src=\"uploads/" + descriptions.path + "\" /> </audio>";
      } else {
        preview = "<img src=\"" + thumbnail + "\" alt=\"" + descriptions.title + "\" />";
      }
      return preview;
    };

    LibraryDetail.prototype.__getLargePreview = function(identifier, path, mime) {
      if (typeof this.largePreview === 'function') {
        return this.largePreview.apply(this, [identifier, path, mime]);
      } else {
        return path;
      }
    };

    LibraryDetail.prototype.__getPreview = function(identifier, path, mime) {
      if (typeof this.thumbnail === 'function') {
        return this.thumbnail.apply(this, [identifier, path, mime]);
      } else {
        return this.thumbnail;
      }
    };

    return LibraryDetail;

  })();

  $.fn.libraryDetail = function(option) {
    var args, defaults;
    args = arguments;
    defaults = {};
    this.each(function() {
      var $this, argsToSent, data, k, options, v;
      $this = $(this);
      data = $this.data('library.detail');
      options = $.extend({}, defaults, $this.data(), typeof option === 'object' && option);
      if (!data) {
        $this.data('library.detail', (data = new LibraryDetail($this, options)));
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

//# sourceMappingURL=detail.js.map
