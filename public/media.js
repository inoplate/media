(function() {
  window.getThumbnail = function(mime) {
    if (isImage(mime)) {
      return null;
    } else if (isVideo(mime)) {
      return "/vendor/inoplate-media/images/medias/video_128px.png";
    } else if (isAudio(mime)) {
      return "/vendor/inoplate-media/images/medias/music_128px.png";
    } else if (mime === 'application/msword') {
      return "/vendor/inoplate-media/images/medias/doc_128px.png";
    } else if ((mime === 'application/excel') || (mime === 'application/vnd.ms-excel') || (mime === 'application/x-excel') || (mime === 'application/x-msexcel')) {
      return "/vendor/inoplate-media/images/medias/xls_128px.png";
    } else if ((mime === 'application/mspowerpoint') || (mime === 'application/powerpoint') || (mime === 'application/vnd.ms-powerpoint') || (mime === 'application/x-mspowerpoint')) {
      return "/vendor/inoplate-media/images/medias/xls_128px.png";
    } else {
      return "/vendor/inoplate-media/images/medias/file_128px.png";
    }
  };

  window.isImage = function(mime) {
    if (mime.substring(0, 5) === 'image') {
      return true;
    }
    return false;
  };

  window.isVideo = function(mime) {
    if (mime.substring(0, 5) === 'video') {
      return true;
    }
    return false;
  };

  window.isAudio = function(mime) {
    if (mime.substring(0, 5) === 'audio') {
      return true;
    }
    return false;
  };

  window.bytesToSize = function(bytes, precision) {
    var posttxt, sizes;
    sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    posttxt = 0;
    if (bytes === 0) {
      'n/a';
    }
    if (bytes < 1024) {
      (Number(bytes)) + " " + sizes[posttxt];
    }
    while (bytes >= 1024) {
      posttxt++;
      bytes = bytes / 1024;
    }
    return (bytes.toPrecision(precision)) + " " + sizes[posttxt];
  };

}).call(this);

//# sourceMappingURL=media.js.map
