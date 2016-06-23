(function() {
  var detail, openMediaDetail, sharingForm, sharingTable, tile, userFormatRepo, userFormatRepoSelection;

  openMediaDetail = getLocalStorage('inoplate.media.open-media-detail');

  userFormatRepo = function(repo) {
    var markup;
    if (repo.loading) {
      return repo.text;
    }
    markup = "<div class='select2-result-repository clearfix'> <div class='select2-result-repository__meta'> <div class='select2-result-repository__title'>" + repo.name + "</div> </div> </div>";
    return markup;
  };

  userFormatRepoSelection = function(repo) {
    return repo.name || repo.text;
  };

  $('#library-update-form').modal({
    show: false
  });

  sharingForm = $("select[name='users']", '#sharing-form').select2({
    ajax: {
      url: function() {
        var source;
        source = $("select[name=\"users\"]", "#sharing-form").data("source");
        return source;
      },
      dataType: 'json',
      delay: 250,
      data: function(params) {
        var param;
        param = {
          search: params.term,
          page: params.page
        };
        return param;
      },
      processResults: function(data, params) {
        var results;
        params.page = params.page || 1;
        results = {
          results: data.data,
          pagination: {
            more: (params.page * 5) < data.total
          }
        };
        return results;
      }
    },
    escapeMarkup: function(markup) {
      return markup;
    },
    minimumInputLength: 1,
    templateResult: userFormatRepo,
    templateSelection: userFormatRepoSelection
  });

  sharingTable = $('#shared-to-table').DataTable({
    dom: '<"row"<"col-sm-6"l><"col-sm-6"f>><"row"<"col-sm-12"rt>><"row"<"col-sm-5"i><"col-sm-7"p>>',
    serverSide: false,
    ajax: false,
    createdRow: function(row, data, index) {
      return true;
    },
    columnDefs: [
      {
        visible: false,
        targets: 0
      }, {
        orderable: false,
        targets: 2,
        render: function(data, type, full, meta) {
          return '<button class="btn btn-danger remove-share btn-sm pull-right"> <i class="fa fa-trash"></i> </button>';
        }
      }
    ]
  });

  $('#shared-to-table').on('click', '.remove-share', function() {
    var tr;
    tr = $(this).parents('tr');
    return sharingTable.row(tr).remove().draw();
  });

  sharingForm.on('select2:select', function() {
    var $this, data;
    $this = $(this);
    data = $this.select2('data');
    sharingTable.row.add([data[0].id, data[0].name, null]).draw();
    return $this.val(null).trigger('change');
  });

  if (openMediaDetail === 'close') {
    $(".media-detail").css('display', 'none');
    $("i", '#toggle-media-detail').removeClass("fa-toggle-on").addClass("fa-toggle-off");
  }

  tile = $('.media-container').libraryTile({
    height: "450px",
    thumbnail: function(library) {
      return getThumbnail(library.description.mime) || ("/uploads/" + library.description.path + "/thumb");
    }
  });

  detail = $('.media-detail').libraryDetail({
    thumbnail: function(identifier, path, mime) {
      return getThumbnail(mime) || ("/uploads/" + path + "/mini-display");
    },
    largePreview: function(identifier, path, mime) {
      return "uploads/" + path;
    }
  });

  tile.libraryTile('checkPagination');

  $('.btn-refresh').click(function() {
    var ownership, payloads, search, visibility;
    ownership = $('select[name="ownership"]').val();
    visibility = $('select[name="visibility"]').val();
    search = $('input[name="search"]').val();
    $("#btn-share").addClass('disabled');
    $("#btn-update").addClass('disabled');
    payloads = {
      ownership: ownership,
      visibility: visibility,
      search: search
    };
    tile.libraryTile('setPayload', payloads);
    tile.libraryTile('refineSearch', "/admin/inoplate-media/libraries?page=1");
    return detail.libraryDetail('clearDetail');
  });

  $("#upload-new").click(function() {
    return $('.uploader-container').toggleClass("hide");
  });

  $("#toggle-media-detail").click(function() {
    $(".media-detail").slideToggle(0, function() {
      var state;
      state = $(this).css('display');
      if (state === 'none') {
        setLocalStorage('inoplate.media.open-media-detail', 'close');
      } else {
        setLocalStorage('inoplate.media.open-media-detail', 'open');
      }
      return tile.libraryTile('checkPagination');
    });
    return $("i", this).toggleClass("fa-toggle-on fa-toggle-off");
  });

  $(".uploader-dismiss").click(function() {
    return $(this).parents(".uploader-container").addClass("hide");
  });

  $('.uploader').on('uploader.fileAdded', function(e, file) {
    var identifier;
    identifier = file.uniqueIdentifier;
    tile.libraryTile('addUploading', identifier, file.name);
    return $("[data-upload-id=\"" + identifier + "\"]", tile).data('file', file);
  });

  $('.uploader').on('uploader.fileProgress', function(e, file) {
    var identifier, progress;
    identifier = file.uniqueIdentifier;
    progress = file.progress() * 100;
    return tile.libraryTile('setProgress', identifier, (progress.toFixed(2)) + "%25");
  });

  $('.uploader').on('uploader.fileRetry', function(e, file, chunk) {
    var identifier;
    identifier = file.uniqueIdentifier;
    return tile.libraryTile('retryUpload', identifier);
  });

  $('.uploader').on('uploader.fileError', function(e, file, message, chunk) {
    var identifier;
    identifier = file.uniqueIdentifier;
    tile.libraryTile('markAsError', identifier, message);
    $("[data-upload-id=\"" + identifier + "\"] .btn-pause", tile).addClass('hide');
    return $("[data-upload-id=\"" + identifier + "\"] .btn-resume", tile).removeClass('hide');
  });

  $('.uploader').on('uploader.fileSuccess', function(e, file, result) {
    var identifier;
    identifier = file.uniqueIdentifier;
    result = JSON.parse(result);
    return tile.libraryTile('markAsSuccess', identifier, result.library);
  });

  tile.on('click', 'li.uploading .btn-resume', function() {
    var $file, $li, $this, identifier;
    $this = $(this);
    $li = $this.parents('li');
    identifier = $li.data('uploadId');
    $file = $li.data('file');
    $("[data-upload-id=\"" + identifier + "\"] .btn-pause", tile).removeClass('hide');
    $("[data-upload-id=\"" + identifier + "\"] .btn-resume", tile).addClass('hide');
    return $file.resume();
  });

  tile.on('click', 'li.uploading .btn-pause', function() {
    var $file, $li, $this, identifier;
    $this = $(this);
    $li = $this.parents('li');
    identifier = $li.data('uploadId');
    $file = $li.data('file');
    $("[data-upload-id=\"" + identifier + "\"] .btn-pause", tile).addClass('hide');
    $("[data-upload-id=\"" + identifier + "\"] .btn-resume", tile).removeClass('hide');
    return $file.pause();
  });

  tile.on('click', 'li.error .btn-resume', function() {
    var $file, $li, $this, identifier;
    $this = $(this);
    $li = $this.parents('li');
    identifier = $li.data('uploadId');
    $li.addClass('uploading').removeClass('error').removeClass('selected');
    $("[data-upload-id=\"" + identifier + "\"] .btn-pause", tile).removeClass('hide');
    $("[data-upload-id=\"" + identifier + "\"] .btn-cancel", tile).removeClass('hide');
    $("[data-upload-id=\"" + identifier + "\"] .btn-resume", tile).addClass('hide');
    $file = $li.data('file');
    return $file.retry();
  });

  tile.on('click', 'li.uploading .btn-cancel, li.error .btn-cancel', function() {
    var $file, $li, $this, identifier;
    $this = $(this);
    $li = $this.parents('li');
    $file = $li.data('file');
    identifier = $li.data('uploadId');
    $file.cancel();
    tile.libraryTile('remove', identifier);
    if ($li.hasClass('selected')) {
      detail.libraryDetail('hideError');
      return detail.libraryDetail('clearDetail');
    }
  });

  tile.on('media.library.tile.loading', function(e) {
    return $('.btn-refresh').button('loading');
  });

  tile.on('media.library.tile.loaded', function(e) {
    return $('.btn-refresh').button('reset');
  });

  tile.on('media.library.tile.selected', 'li', function(e, meta, error) {
    var author, i, len, ref;
    if (typeof error === 'undefined') {
      detail.libraryDetail('showDetail', meta.id, meta.description.path, meta.description.mime, meta.description);
      $('.title', detail).text(meta.description.title);
      $('.description', detail).text(meta.description.description);
      console.log(bytesToSize(meta.description.size, 2));
      $('.size', detail).text(bytesToSize(meta.description.size, 2));
      $("form[data-control=\"publish\"]", detail).prop('action', "/admin/inoplate-media/libraries/publish/" + meta.id);
      $("form[data-control=\"unpublish\"]", detail).prop('action', "/admin/inoplate-media/libraries/unpublish/" + meta.id);
      $("form[data-control=\"removal\"]", detail).prop('action', "/admin/inoplate-media/libraries/" + meta.id);
      $("a.dl-link", detail).prop("href", "download/" + meta.description.path);
      $('input[name="title"]', '#library-update-form').val(meta.description.title);
      $('textarea[name="description"]', '#library-update-form').val(meta.description.description);
      $("form[data-control=\"general-form\"]", '#library-update-form').prop('action', "/admin/inoplate-media/libraries/" + meta.id);
      $("form[data-control=\"sharing\"]", '#sharing-form').prop('action', "/admin/inoplate-media/libraries/share/" + meta.id);
      $("select[name=\"users\"]", "form[data-control=\"sharing\"]").data("source", "/admin/inoplate-media/libraries/shareable-users/" + meta.id);
      sharingTable.clear();
      ref = meta.sharedTo;
      for (i = 0, len = ref.length; i < len; i++) {
        author = ref[i];
        sharingTable.row.add([author.id, author.name, null]);
      }
      sharingTable.draw();
      if (meta.description.updateable) {
        $("#btn-update").removeClass('disabled');
      } else {
        $("#btn-update").addClass('disabled');
      }
      if (meta.description.shareable) {
        $("#btn-share").removeClass('disabled');
      } else {
        $("#btn-share").addClass('disabled');
      }
      if (meta.description.deletable) {
        $("form[data-control=\"removal\"]", detail).removeClass('hide');
      } else {
        $("form[data-control=\"removal\"]", detail).addClass('hide');
      }
      if (meta.description.publishable) {
        if (meta.description.visibility === 'private') {
          $("form[data-control=\"publish\"]", detail).removeClass('hide');
          return $("form[data-control=\"unpublish\"]", detail).addClass('hide');
        } else {
          $("form[data-control=\"publish\"]", detail).addClass('hide');
          return $("form[data-control=\"unpublish\"]", detail).removeClass('hide');
        }
      } else {
        $("form[data-control=\"publish\"]", detail).addClass('hide');
        return $("form[data-control=\"unpublish\"]", detail).addClass('hide');
      }
    } else {
      detail.libraryDetail('clearDetail');
      return detail.libraryDetail('showError', error.file);
    }
  });

  $("form[data-control=\"publish\"], form[data-control=\"unpublish\"]").on('ajax.form.success', function(event, data, textStatus, jqXHR) {
    $("form[data-control=\"publish\"]").toggleClass('hide');
    return $("form[data-control=\"unpublish\"]").toggleClass('hide');
  });

  $("form[data-control=\"removal\"]").on('ajax.form.success', function(event, data, textStatus, jqXHR) {
    detail.libraryDetail('clearDetail');
    $("#library-" + data.library.id).remove();
    tile.libraryTile('checkPagination');
    $("#btn-share").addClass('disabled');
    return $("#btn-update").addClass('disabled');
  });

  $("form[data-control=\"sharing\"]").on('ajax.form.beforeSend', function(e, jqXHR, settings) {
    var authors, sharingTableData;
    sharingTableData = sharingTable.data();
    authors = "";
    sharingTableData.each(function(d) {
      return authors = authors + "&authors[]=" + d[0];
    });
    return settings.data = "" + settings.data + authors;
  });

  $('#btn-update').on('click', function() {
    return $('#library-update-form').modal('show');
  });

  $('#btn-share').on('click', function() {
    return $('#sharing-form').modal('show');
  });

  $("form[data-control=\"sharing\"], form[data-control=\"publish\"], form[data-control=\"unpublish\"], form[data-control=\"general-form\"]").on('ajax.form.success', function(event, data, textStatus, jqXHR) {
    var $li;
    $li = $("#library-" + data.library.id);
    $li.data('meta', data.library);
    return $li.trigger('media.library.tile.selected', data.library);
  });

  $("form[data-control=\"sharing\"]").on('ajax.form.success', function(event, data, textStatus, jqXHR) {
    return $('#sharing-form').modal('hide');
  });

  $("form[data-control=\"general-form\"]").on('ajax.form.success', function(event, data, textStatus, jqXHR) {
    return $('#library-update-form').modal('hide');
  });

}).call(this);

//# sourceMappingURL=index.js.map
