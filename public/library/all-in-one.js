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
      return "uploads/" + path + "/mini-display";
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
        sharingTable.row.add([author.id, author.description.name, null]);
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
      return authors = authors + "authors[]=" + d[0];
    });
    return settings.data = settings.data + "&" + authors;
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


  /*$ "#upload-new"
      .click () ->
          $ '.uploader-container'
              .toggleClass "hide"
  
  $ '.uploader'
      .on 'uploader.fileAdded', (e, file) ->
          $dom = $ "<li class=\"uploading\" data-upload-id=\"#{file.uniqueIdentifier}\">
                      <a class=\"thumbnail\">
                          <div class=\"btn-action-wrapper\">
                              <div class=\"btn-action\">
                                  <span class=\"fa-stack fa-lg btn-resume hide\">
                                      <i class=\"fa fa-circle fa-stack-2x\"></i>
                                      <i class=\"fa fa-repeat fa-stack-1x fa-inverse\"></i>
                                  </span>
                                  <span class=\"fa-stack fa-lg btn-pause\">
                                      <i class=\"fa fa-circle fa-stack-2x\"></i>
                                      <i class=\"fa fa-pause fa-stack-1x fa-inverse\"></i>
                                  </span>
                                  <span class=\"fa-stack fa-lg btn-cancel\">
                                      <i class=\"fa fa-circle fa-stack-2x\"></i>
                                      <i class=\"fa fa-times fa-stack-1x fa-inverse\"></i>
                                  </span>
                              </div>
                          </div>
                          <img data-src=\"holder.js/150x150?text=Uploading..\" />
                          <div class=\"caption\">#{file.name}</div>
                      </a>
                    </li>"
  
          $img = $ 'img', $dom
  
          $ '.btn-pause', $dom
              .on 'click', () ->
                  file.pause()
                  progress = file.progress() * 100
                  $img.attr
                      'data-src': "holder.js/150x150?text=(#{progress.toFixed(2)}%25)Paused"
                  Holder.run
                      images: $img[0]
                  $ this
                      .addClass 'hide'
  
                  $ '.btn-resume', $dom
                      .removeClass 'hide'
  
          $ '.btn-resume', $dom
              .on 'click', () ->
                  file.resume()
                  progress = file.progress() * 100
                  $img.attr
                      'data-src': "holder.js/150x150?text=(#{progress.toFixed(2)}%25)Uploading.."
                  Holder.run
                      images: $img[0]
                  $ this
                      .addClass 'hide'
  
                  $ '.btn-pause', $dom
                      .removeClass 'hide'
  
          $ '.btn-cancel', $dom
              .on 'click', () ->
                  file.cancel()
                  $dom.remove()
  
          Holder.run
              images: $img[0]
  
          $ '.thumbnail-wrapper'
              .prepend $dom
  
  $ '.uploader'
      .on 'uploader.fileProgress', (e, file) ->
          $img = $ 'img', "[data-upload-id=\"#{file.uniqueIdentifier}\"]"
          $li = $ "[data-upload-id=\"#{file.uniqueIdentifier}\"]"
          progress = file.progress() * 100
          $img.attr
              'data-src': "holder.js/150x150?text=(#{progress.toFixed(2)}%25)Uploading.."
  
          Holder.run
              images: $img[0]
  
  $ '.uploader'
      .on 'uploader.fileRetry', (e, file, chunk) ->
          $li = $ "[data-upload-id=\"#{file.uniqueIdentifier}\"]"
          $li.removeClass 'error'
          $li.addClass 'uploading'
  
          $ '.btn-resume', $li
              .click ()->
                  file.resume()
  
                  $ '.btn-pause', $li
                      .removeClass 'hide'
  
                  $ this
                      .addClass 'hide'
  
  $ '.uploader'
      .on 'uploader.fileError', (e, file, message, chunk) ->
          $img = $ 'img', "[data-upload-id=\"#{file.uniqueIdentifier}\"]"
          $li = $ "[data-upload-id=\"#{file.uniqueIdentifier}\"]"
  
          $img.attr
              'data-src': "holder.js/150x150?text=Error"
          
          Holder.run
              images: $img[0]
  
          $li.removeClass 'uploading'
              .addClass 'error'
  
          $ '.btn-pause', $li
              .addClass 'hide'
  
          $ '.btn-resume', $li
              .removeClass 'hide'
  
          $ '.btn-resume', $li
              .one 'click', ()->
                  file.retry()
  
                  $li.removeClass 'error'
                      .addClass 'uploading'
  
          $li.attr 'data-error-message', message
  
  $ '.uploader'
      .on 'uploader.fileSuccess', (e, file, result) ->
          result = JSON.parse result
          library = result.library
          thumbnail = getThumbnail(library.description.mime) || "/uploads/#{library.description.path}/thumb"
  
          $ "[data-upload-id=\"#{file.uniqueIdentifier}\"]"
              .removeClass 'uploading'
  
          $ "[data-upload-id=\"#{file.uniqueIdentifier}\"]"
              .data 'meta', result.library
  
          $ "img", "[data-upload-id=\"#{file.uniqueIdentifier}\"]"
              .prop 'src', thumbnail
  
  $ "#toggle-media-detail"
      .click () ->
          $ ".media-detail"
              .slideToggle 0, ()->
                  $ ".media-container" 
                      .trigger 'scroll';
          $ "i", this
              .toggleClass "fa-toggle-on fa-toggle-off"
  
  $ ".uploader-dismiss"
      .click () ->
          $ this
              .parents ".uploader-container"
                  .addClass "hide"
  
  $ ".media-container"
      .slimScroll
          height: '450px'
      .bind 'slimscrolling', (e, pos) ->
          $pager = $ '.file-pager a'
  
          if $pager.is(':within-viewport') && $pager.css('display') != 'none'
              $ '.file-pager a'
                  .hide()
  
              url = $ '.file-pager a'
                          .prop 'href'
  
              fetchMedias url
  
  $ ".media-detail-container .media-form"
      .slimScroll
          height: '270px'
  
  $ ".slimScrollBar"
      .hide()
  
  $ "ul.thumbnail-wrapper"
      .on "click", "li", () ->
          uploading =  $ this
                          .hasClass 'uploading'
  
          if uploading
              return false
  
          parents = $ this
                      .parents "ul.thumbnail-wrapper"
  
          $ "li", parents
              .removeClass "selected"
  
          $ this
              .addClass "selected"
  
          meta = $ this
                  .data "meta"
  
          error = $ this
                      .data 'error-message'
  
          if typeof meta == 'string'
              meta = JSON.parse meta
  
          if typeof error == 'string'
              error = JSON.parse error
  
          $ this
              .trigger "media.library.selected", [meta, error]
  
  $ "ul.thumbnail-wrapper"
      .on "media.library.selected", "li", (event, meta, error) ->
          $ ".media-full-preview"
              .removeClass "no-border"
  
  $ "ul.thumbnail-wrapper"
      .on "media.library.selected", "li.error", (event, meta, error) ->
          $li = $ this
          $ ".media-action-btn"
              .addClass "hide"
  
          $ ".media-preview-container"
              .addClass "hide"
  
          $ ".media-form"
              .addClass "hide"
  
          error = error.file
          parsed = ''
  
          if $.isArray(error)
              $.each error, (key, value) ->
                  parsed += "<li>#{value}</li>"
          else
              parsed = "<li>#{error}</li>"
  
          $ '.media-error ul'
              .html parsed
  
          $ '.media-error'
              .removeClass 'hide'
  
  $ "ul.thumbnail-wrapper"
      .on "media.library.selected", "li:not('.error')", (event, meta, error) ->
          $ '.media-error'
              .addClass 'hide'
  
          mime = meta.description.mime
  
          preview = ''
  
          if isImage(mime)
              preview = "<a class=\"fancybox fancybox.image\" 
                            href=\"uploads/#{meta.description.path}/full-display\"
                            title=\"uploads/#{meta.description.title}\"
                          >
                              <img src=\"uploads/#{meta.description.path}/mini-display\" 
                                   alt=\"#{meta.description.title}\" 
                              />
                         </a>"
  
          else if isVideo(mime)
              preview = "<video width=\"300\" height=\"225\">
                              <source type=\"#{mime}\" src=\"uploads/#{meta.description.path}\" />
                          </video>"
          else if isAudio(mime)
              preview = "<audio width=\"300\">
                              <source type=\"#{mime}\" src=\"uploads/#{meta.description.path}\" />
                          </audio>"
          else 
              thumbnail = getThumbnail(mime)
              preview = "<img src=\"#{thumbnail}\" alt=\"#{meta.description.title}\" />"
  
          $ ".media-full-preview"
              .html preview
  
          $ 'audio, video', '.media-full-preview'
              .mediaelementplayer
                  pluginPath: ''
                  enablePluginDebug: false
                  enableAutosize: true
                  enableKeyboard: true
                  features: ['playpause','progress','current','duration','tracks','volume','backlight']
                  timerRate: 250
                  success: (media, node, player) ->
                      $ "##{node.id}-mode"
                          .html "mode: #{media.pluginType}"
  
          $ ".media-action-btn"
              .removeClass "hide"
  
          $ ".media-preview-container"
              .removeClass "hide"
  
          $ ".media-form"
              .removeClass "hide"
  
          $ 'input[name="title"]', '.media-form'
              .val meta.description.title
  
          $ 'span.title'
              .text meta.description.title
  
          $ 'textarea[name="description"]', '.media-form'
              .val meta.description.description
  
          $ 'span.description'
              .text meta.description.description
  
          if meta.description.updateable
              $ "[data-control=\"information\"]"
                  .addClass 'hide'
              $ "[data-control=\"general-form\"]"
                  .removeClass 'hide'
          else
              $ "[data-control=\"information\"]"
                  .removeClass 'hide'
              $ "[data-control=\"general-form\"]"
                  .addClass 'hide'
  
          if meta.description.shareable
              $ "[data-control=\"sharing\"]"
                  .removeClass 'hide'
          else
              $ "[data-control=\"sharing\"]"
                  .addClass 'hide'
  
          if meta.description.publishable
  
              if !meta.description.visibility
                  $ "form[data-control=\"publish\"]"
                      .removeClass 'hide'
                  $ "form[data-control=\"unpublish\"]"
                      .addClass 'hide'
              else
                  $ "form[data-control=\"publish\"]"
                      .addClass 'hide'
                  $ "form[data-control=\"unpublish\"]"
                      .removeClass 'hide'
          else
              $ "form[data-control=\"publish\"]"
                  .addClass 'hide'
              $ "form[data-control=\"unpublish\"]"
                  .addClass 'hide'
  
          if meta.description.deletable
              $ "form[data-control=\"removal\"]"
                  .removeClass 'hide'
          else
              $ "form[data-control=\"removal\"]"
                  .addClass 'hide'
  
          $ "form[data-control=\"general-form\"]"
              .prop 'action', "/admin/inoplate-media/libraries/#{meta.id}"
  
          $ "form[data-control=\"sharing\"]"
              .prop 'action', "/admin/inoplate-media/libraries/share/#{meta.id}"
  
          $ "select[name=\"users[]\"]", "form[data-control=\"sharing\"]"
              .data "source", "/admin/inoplate-media/libraries/shareable-users/#{meta.id}"
  
          $ "form[data-control=\"publish\"]"
              .prop 'action', "/admin/inoplate-media/libraries/publish/#{meta.id}"
  
          $ "form[data-control=\"unpublish\"]"
              .prop 'action', "/admin/inoplate-media/libraries/unpublish/#{meta.id}"
  
          $ "form[data-control=\"removal\"]"
              .prop 'action', "/admin/inoplate-media/libraries/#{meta.id}"
  
          $ "a.dl-link", ".media-action-btn"
              .prop "href", "download/#{meta.description.path}"
  
          $ "a.fancybox", ".media-full-preview"
              .fancybox()
  
          authors = (new Option(author.description.name, author.id, true, true) for author in meta.sharedTo)
  
          $ "select[name=\"users[]\"]", "form[data-control=\"sharing\"]"
              .html ''
              .append authors
              .trigger 'change.select2'
  
  $ "ul.thumbnail-wrapper"
      .on 'media.library.reset-selected', (event) ->
          $ '.media-error'
              .addClass 'hide'
  
          $ ".media-full-preview"
              .html "<i class=\"fa fa-5x fa-eye-slash\" aria-hidden=\"true\"></i><p>Nothing is selected.</p>"
  
          $ ".media-full-preview"
              .addClass 'no-border'
  
          $ ".media-action-btn"
              .addClass "hide"
  
          $ ".media-form"
              .addClass "hide"
  
          $ "[data-control=\"information\"]"
              .addClass 'hide'
  
          $ "[data-control=\"general-form\"]"
              .addClass 'hide'
  
  addParams = () ->
      ownership = $ 'select[name="ownership"]'
                      .val()
  
      visibility = $ 'select[name="visibility"]'
                      .val()
  
      search = $ 'input[name="search"]'
                  .val()
  
      return "&ownership=#{ownership}&visibility=#{visibility}&search=#{search}"
  
  
  $ '.btn-refresh'
      .click () ->
          $ '.thumbnail-wrapper'
              .html ''
  
          $ '.file-pager a'
              .hide()
  
          $ '.file-pager .end-info'
              .addClass 'hide'
  
          $ '.file-pager .overlay'
              .removeClass 'hide'
  
          $ "ul.thumbnail-wrapper"
              .trigger "media.library.reset-selected"
  
          fetchMedias '/admin/inoplate-media/libraries?page=1'
  
  parseItem = (item) ->
      meta = JSON.stringify item
      thumbnail = getThumbnail(item.description.mime) || "/uploads/#{item.description.path}/thumb"
  
      $dom = $ "<li id=\"library-#{item.id}\">
                  <a class=\"thumbnail\">
                      <img class=\"lazy\" src=\"#{thumbnail}\" alt=\"#{item.description.title}\" />
                      <div class=\"caption\">#{item.description.title}</div>
                  </a>
                </li>"
  
      $dom.data 'meta', item
  
      return $dom
  
  fetchMedias = (url) ->
      $ '.file-pager .overlay'
          .removeClass 'hide'
  
      url += addParams()
      items = []
  
      $.get url, (result) ->
  
          $ '.thumbnail-wrapper'
              .append parseItem item for item in result.libraries.data
  
          if result.libraries.next_page_url
              $ '.file-pager a'
                  .prop 'href', result.libraries.next_page_url
  
              $ '.file-pager a'
                  .show()
          else
              $ '.file-pager .end-info'
                  .removeClass 'hide'
  
          $ '.file-pager .overlay'
              .addClass 'hide'
      ,
          'json'
  
  getThumbnail = (mime) ->
      if isImage(mime)
          return null
  
      else if isVideo(mime)
          return "/vendor/inoplate-media/images/medias/video_128px.png"
  
      else if isAudio(mime)
          return "/vendor/inoplate-media/images/medias/music_128px.png"
  
      else if(mime == 'application/msword')
          return "/vendor/inoplate-media/images/medias/doc_128px.png"
  
      else if(mime == 'application/excel') || (mime == 'application/vnd.ms-excel') || (mime == 'application/x-excel') || (mime == 'application/x-msexcel')
          return "/vendor/inoplate-media/images/medias/xls_128px.png"
  
      else if(mime == 'application/mspowerpoint') || (mime == 'application/powerpoint') || (mime == 'application/vnd.ms-powerpoint') || (mime == 'application/x-mspowerpoint')
          return "/vendor/inoplate-media/images/medias/xls_128px.png"
  
      else
          return "/vendor/inoplate-media/images/medias/file_128px.png"
  
  isImage = (mime) ->
      if(mime.substring(0,5) == 'image')
          return true
  
      return false
  
  isVideo = (mime) ->
      if(mime.substring(0,5) == 'video')
          return true
  
      return false
  
  isAudio = (mime) ->
      if(mime.substring(0,5) == 'audio')
          return true
  
      return false
  
  userFormatRepo = (repo) ->
    if (repo.loading) 
      return repo.text;
  
    markup = "<div class='select2-result-repository clearfix'>
                  <div class='select2-result-repository__meta'>
                    <div class='select2-result-repository__title'>#{repo.name}</div>
                  </div>
              </div>";
  
    return markup;
  
  userFormatRepoSelection = (repo) ->
    return repo.name||repo.text;
  
  $ "select[name='users[]']"
      .select2
          ajax:
              url: () ->
                  source = $ "select[name=\"users[]\"]", "form[data-control=\"sharing\"]"
                              .data "source"
  
                  source
              ,
              dataType: 'json'
              ,
              delay: 250
              ,
              data: (params) ->
                  param =
                      search: params.term
                      page: params.page
  
                  param
              ,
              processResults: (data, params) ->
                  params.page = params.page || 1;
  
                  results = 
                      results: data.data
                      pagination:
                          more: (params.page * 5) < data.total
  
                  results
              ,
          escapeMarkup: (markup) -> 
              return markup
              
          minimumInputLength: 1
          templateResult: userFormatRepo
          templateSelection: userFormatRepoSelection
  
  $ "form[data-control=\"sharing\"], form[data-control=\"publish\"], form[data-control=\"unpublish\"], form[data-control=\"general-form\"]"
      .on 'ajax.form.success', (event, data, textStatus, jqXHR)->
          $ "#library-#{data.library.id}"
              .data 'meta', data.library
  
  $ "form[data-control=\"publish\"], form[data-control=\"unpublish\"]"
      .on 'ajax.form.success', (event, data, textStatus, jqXHR)->
          $ "form[data-control=\"publish\"]"
              .toggleClass 'hide'
  
          $ "form[data-control=\"unpublish\"]"
              .toggleClass 'hide'
  
  $ "form[data-control=\"removal\"]"
      .on 'ajax.form.success', (event, data, textStatus, jqXHR)->
          $ "#library-#{data.library.id}"
              .parents 'ul.thumbnail-wrapper'
              .trigger 'media.library.reset-selected'
  
          $ "#library-#{data.library.id}"
              .remove()
  
          if $('.file-pager a').is(":visible")
              $ '.file-pager a'
                  .hide()
  
              url = $ '.file-pager a'
                      .prop 'href'
  
              fetchMedias url
   */

}).call(this);

//# sourceMappingURL=all-in-one.js.map
