(function() {
  var LibraryUploader;

  LibraryUploader = (function() {
    function LibraryUploader($element, options) {
      this.$element = $element;
      this.__attachEvent();
      this.__init();
    }

    LibraryUploader.prototype.__attachEvent = function() {
      $(document).on('drop dragover', function(e) {
        e.preventDefault();
      });
      $(document).on('dragleave drop', function(e) {
        return $('.uploader-dropzone', this.$element).removeClass('hover');
      });
      return $(document).on('dragover', function(e) {
        var dropzone, found, node, timeout;
        dropzone = $('.uploader-dropzone', this.$element);
        timeout = window.dropZoneTimeout;
        if (!timeout) {
          dropzone.addClass('in');
        } else {
          clearTimeout(timeout);
        }
        found = false;
        node = e.target;
        if ($(node).hasClass('uploader-dropzone')) {
          if (!$(node).hasClass('hover')) {
            $(node).addClass('hover');
          }
        } else if ($(node).parents('.uploader-dropzone').length > 0) {
          if (!$(node).parents('.uploader-dropzone').hasClass('hover')) {
            $(node).parents('.uploader-dropzone').addClass('hover');
          }
        } else {
          dropzone.removeClass('hover');
        }
        window.dropZoneTimeout = setTimeout(function() {
          window.dropZoneTimeout = null;
        }, 100);
      });
    };

    LibraryUploader.prototype.__init = function() {
      var browseId, chunkSize, dropzoneId, fileContainerId, maxUploadSize, target, uploader;
      target = '/admin/inoplate-media/libraries/upload';
      chunkSize = this.$element.data('chunk');
      maxUploadSize = this.$element.data('maxupload');
      browseId = $(".btn-browse", this.$element);
      dropzoneId = $(".uploader-dropzone", this.$element);
      fileContainerId = $(".file-container", this.$element);

      /*
          The simulaneous upload set to 1
          Its because Laravel framework on server side has mysterious session persistence problem
       */
      uploader = new Flow({
        target: target,
        forceChunkSize: true,
        chunkSize: chunkSize,
        simultaneousUploads: 1,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      uploader.assignBrowse(browseId[0]);
      uploader.assignDrop(dropzoneId[0]);
      uploader.on('fileAdded', (function(_this) {
        return function(file) {
          if (file.size > maxUploadSize) {
            return false;
          }
          _this.$element.trigger('uploader.fileAdded', [file]);
        };
      })(this));
      uploader.on('fileProgress', (function(_this) {
        return function(file) {
          _this.$element.trigger('uploader.fileProgress', [file]);
        };
      })(this));
      uploader.on('filesSubmitted', (function(_this) {
        return function(file, event) {
          uploader.upload();
          _this.$element.trigger('uploader.filesSubmitted', [file, event]);
        };
      })(this));
      uploader.on('fileSuccess', (function(_this) {
        return function(file, message) {
          _this.$element.trigger('uploader.fileSuccess', [file, message]);
        };
      })(this));
      uploader.on('fileRetry', (function(_this) {
        return function(file, chunk) {
          _this.$element.trigger('uploader.fileRetry', [file, chunk]);
        };
      })(this));
      return uploader.on('fileError', (function(_this) {
        return function(file, message, chunk) {
          _this.$element.trigger('uploader.fileError', [file, message, chunk]);
        };
      })(this));
    };

    return LibraryUploader;

  })();

  $.fn.libraryUploader = function(option) {
    var args, defaults;
    args = arguments;
    defaults = {};
    this.each(function() {
      var $this, argsToSent, data, k, options, v;
      $this = $(this);
      data = $this.data('library.uploader');
      options = $.extend({}, defaults, $this.data(), typeof option === 'object' && option);
      if (!data) {
        $this.data('library.uploader', (data = new LibraryUploader($this, options)));
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

  $(function() {
    return $('.uploader').libraryUploader();
  });

}).call(this);

//# sourceMappingURL=uploader.js.map
