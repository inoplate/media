(function() {
  var LibraryFinder;

  LibraryFinder = (function() {
    function LibraryFinder($element, options) {
      this.$element = $element;
      this.id = null;
      this.selected = [];
      this.multiple = options.multiple || false;
      this.tile = this.__initTile();
      this.__attachEvent();
      this.__init();
    }

    LibraryFinder.prototype.show = function(id, multiple) {
      if (multiple == null) {
        multiple = false;
      }
      this.id = id;
      this.multiple = multiple;
      this.tile.libraryTile("setMultiple", multiple);
      return this.$element.modal('show');
    };

    LibraryFinder.prototype.getSelected = function() {
      return this.selected;
    };

    LibraryFinder.prototype.__init = function() {
      return this.$element.modal({
        show: false
      });
    };

    LibraryFinder.prototype.__initTile = function() {
      var tile;
      tile = $('.media-container', this.$element).libraryTile({
        height: "450px",
        thumbnail: function(library) {
          return getThumbnail(library.description.mime) || ("/uploads/" + library.description.path + "/thumb");
        }
      });
      tile.libraryTile('checkPagination');
      return tile;
    };

    LibraryFinder.prototype.__attachEvent = function() {
      $('.btn-refresh', this.$element).click((function(_this) {
        return function() {
          var ownership, payloads, search, visibility;
          ownership = $('select[name="ownership"]').val();
          visibility = $('select[name="visibility"]').val();
          search = $('input[name="search"]').val();
          $(".btn-select", _this.$element).addClass('disabled');
          payloads = {
            ownership: ownership,
            visibility: visibility,
            search: search
          };
          _this.tile.libraryTile('setPayload', payloads);
          _this.tile.libraryTile('refineSearch', "/admin/inoplate-media/libraries?page=1");
          return _this.tile.libraryTile('clearSelection');
        };
      })(this));
      $(".upload-new", this.$element).click((function(_this) {
        return function() {
          return $('.uploader-container', _this.$element).toggleClass("hide");
        };
      })(this));
      $(".uploader-dismiss", this.$element).click((function(_this) {
        return function() {
          return $('.uploader-container', _this.$element).addClass("hide");
        };
      })(this));
      $('.uploader', this.$element).on('uploader.fileAdded', (function(_this) {
        return function(e, file) {
          var identifier;
          console.log(_this.tile);
          identifier = file.uniqueIdentifier;
          _this.tile.libraryTile('addUploading', identifier, file.name);
          return $("[data-upload-id=\"" + identifier + "\"]", _this.tile).data('file', file);
        };
      })(this));
      $('.uploader', this.$element).on('uploader.fileProgress', (function(_this) {
        return function(e, file) {
          var identifier, progress;
          identifier = file.uniqueIdentifier;
          progress = file.progress() * 100;
          return _this.tile.libraryTile('setProgress', identifier, (progress.toFixed(2)) + "%25");
        };
      })(this));
      $('.uploader', this.$element).on('uploader.fileRetry', (function(_this) {
        return function(e, file, chunk) {
          var identifier;
          identifier = file.uniqueIdentifier;
          return _this.tile.libraryTile('retryUpload', identifier);
        };
      })(this));
      $('.uploader', this.$element).on('uploader.fileError', (function(_this) {
        return function(e, file, message, chunk) {
          var identifier;
          identifier = file.uniqueIdentifier;
          _this.tile.libraryTile('markAsError', identifier, message);
          $("[data-upload-id=\"" + identifier + "\"] .btn-pause", _this.tile).addClass('hide');
          return $("[data-upload-id=\"" + identifier + "\"] .btn-resume", _this.tile).removeClass('hide');
        };
      })(this));
      $('.uploader', this.$element).on('uploader.fileSuccess', (function(_this) {
        return function(e, file, result) {
          var identifier;
          identifier = file.uniqueIdentifier;
          result = JSON.parse(result);
          return _this.tile.libraryTile('markAsSuccess', identifier, result.library);
        };
      })(this));
      this.tile.on('click', 'li.uploading .btn-resume', (function(_this) {
        return function(e) {
          var $file, $li, $this, identifier;
          $this = $(e.currentTarget);
          $li = $this.parents('li');
          identifier = $li.data('uploadId');
          $file = $li.data('file');
          $("[data-upload-id=\"" + identifier + "\"] .btn-pause", _this.tile).removeClass('hide');
          $("[data-upload-id=\"" + identifier + "\"] .btn-resume", _this.tile).addClass('hide');
          return $file.resume();
        };
      })(this));
      this.tile.on('click', 'li.uploading .btn-pause', (function(_this) {
        return function(e) {
          var $file, $li, $this, identifier;
          $this = $(e.currentTarget);
          $li = $this.parents('li');
          identifier = $li.data('uploadId');
          $file = $li.data('file');
          $("[data-upload-id=\"" + identifier + "\"] .btn-pause", _this.tile).addClass('hide');
          $("[data-upload-id=\"" + identifier + "\"] .btn-resume", _this.tile).removeClass('hide');
          return $file.pause();
        };
      })(this));
      this.tile.on('click', 'li.error .btn-resume', (function(_this) {
        return function(e) {
          var $file, $li, $this, identifier;
          $this = $(e.currentTarget);
          $li = $this.parents('li');
          identifier = $li.data('uploadId');
          $li.addClass('uploading').removeClass('error').removeClass('selected');
          $("[data-upload-id=\"" + identifier + "\"] .btn-pause", _this.tile).removeClass('hide');
          $("[data-upload-id=\"" + identifier + "\"] .btn-cancel", _this.tile).removeClass('hide');
          $("[data-upload-id=\"" + identifier + "\"] .btn-resume", _this.tile).addClass('hide');
          $file = $li.data('file');
          return $file.retry();
        };
      })(this));
      this.tile.on('click', 'li.uploading .btn-cancel, li.error .btn-cancel', (function(_this) {
        return function(e) {
          var $file, $li, $this, identifier;
          $this = $(e.currentTarget);
          $li = $this.parents('li');
          $file = $li.data('file');
          identifier = $li.data('uploadId');
          $file.cancel();
          return _this.tile.libraryTile('remove', identifier);
        };
      })(this));
      this.tile.on('media.library.tile.loading', (function(_this) {
        return function(e) {
          return $('.btn-refresh', _this.$element).button('loading');
        };
      })(this));
      this.tile.on('media.library.tile.loaded', (function(_this) {
        return function(e) {
          return $('.btn-refresh', _this.$element).button('reset');
        };
      })(this));
      this.tile.on('media.library.tile.selected', (function(_this) {
        return function(e, meta, error) {
          if (meta) {
            if (_this.multiple) {
              _this.selected.push(meta);
            } else {
              _this.selected = [meta];
            }
          }
          if (_this.selected.length === 0) {
            return $('.btn-select', _this.$element).addClass('disabled');
          } else {
            return $('.btn-select', _this.$element).removeClass('disabled');
          }
        };
      })(this));
      this.tile.on('media.library.tile.unselected', (function(_this) {
        return function(e, meta, error) {
          var index;
          if (meta) {
            if (_this.multiple) {
              index = _this.selected.indexOf(meta);
              if (index !== -1) {
                _this.selected.splice(index, 1);
              }
            } else {
              _this.selected = [];
            }
          }
          if (_this.selected.length === 0) {
            return $('.btn-select', _this.$element).addClass('disabled');
          } else {
            return $('.btn-select', _this.$element).removeClass('disabled');
          }
        };
      })(this));
      return this.$element.on('click', '.btn-select', (function(_this) {
        return function() {
          if (_this.multiple) {
            _this.$element.trigger("media.finder.selected", [_this.id, _this.getSelected()]);
          } else {
            _this.$element.trigger("media.finder.selected", [_this.id, _this.getSelected()[0]]);
          }
          _this.selected = [];
          _this.$element.modal('hide');
          return _this.tile.libraryTile('clearSelection');
        };
      })(this));
    };

    return LibraryFinder;

  })();

  $.fn.libraryFinder = function(option) {
    var args, defaults;
    args = arguments;
    defaults = {};
    this.each(function() {
      var $this, argsToSent, data, k, options, v;
      $this = $(this);
      data = $this.data('library.finder');
      options = $.extend({}, defaults, $this.data(), typeof option === 'object' && option);
      if (!data) {
        $this.data('library.finder', (data = new LibraryFinder($this, options)));
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

  $('.library-finder').libraryFinder();

}).call(this);

//# sourceMappingURL=finder.js.map
