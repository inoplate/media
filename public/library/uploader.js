(function() {
  var uploader;

  uploader = [];

  $(document).on('drop dragover', function(e) {
    e.preventDefault();
  });

  $(document).on('dragleave drop', function(e) {
    return $('.uploader-dropzone').removeClass('hover');
  });

  $(document).on('dragover', function(e) {
    var dropzone, found, node, timeout;
    dropzone = $('.uploader-dropzone');
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

  $('.uploader').each(function() {
    var $that, browseId, chunkSize, dropzoneId, fileContainerId, index, maxUploadSize, target;
    $that = $(this);
    target = '/admin/inoplate-media/libraries/upload';
    chunkSize = $(this).data('chunk');
    maxUploadSize = $(this).data('maxupload');
    index = uploader.length;
    browseId = "btn-browse-" + index;
    dropzoneId = "uploader-dropzone-" + index;
    fileContainerId = "file-container-" + index;
    $('.file-container', this).attr('id', fileContainerId);
    $('.uploader-dropzone', this).attr('id', dropzoneId);
    $('.btn-browse', this).attr('id', browseId);

    /*
        The simulaneous upload set to 1
        Its because Laravel framework on server side has mysterious session persistence problem
     */
    uploader[index] = new Flow({
      target: target,
      forceChunkSize: true,
      chunkSize: chunkSize,
      simultaneousUploads: 1,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });
    uploader[index].assignBrowse(document.getElementById(browseId));
    uploader[index].assignDrop(document.getElementById(dropzoneId));
    uploader[index].on('fileAdded', function(file) {
      if (file.size > maxUploadSize) {
        return false;
      }
      $that.trigger('uploader.fileAdded', [file]);
    });
    uploader[index].on('fileProgress', function(file) {
      $that.trigger('uploader.fileProgress', [file]);
    });
    uploader[index].on('filesSubmitted', function(file, event) {
      uploader[index].upload();
      $that.trigger('uploader.filesSubmitted', [file, event]);
    });
    uploader[index].on('fileSuccess', function(file, message) {
      $that.trigger('uploader.fileSuccess', [file, message]);
    });
    return uploader[index].on('fileError', function(file, message, chunk) {
      $that.trigger('uploader.fileError', [file, message, chunk]);
    });
  });

  return;

}).call(this);

//# sourceMappingURL=uploader.js.map
