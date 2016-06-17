openMediaDetail = getLocalStorage 'inoplate.media.open-media-detail'

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

$ '#library-update-form'
    .modal
        show: false

sharingForm = $ "select[name='users']", '#sharing-form'
                .select2
                    ajax:
                        url: () ->
                            source = $ "select[name=\"users\"]", "#sharing-form"
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

sharingTable = $ '#shared-to-table'
                    .DataTable
                        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>><"row"<"col-sm-12"rt>><"row"<"col-sm-5"i><"col-sm-7"p>>'
                        serverSide: false
                        ajax: false
                        createdRow: (row, data, index) ->
                            true
                        columnDefs: [
                            visible: false
                            targets: 0
                        ,
                            orderable: false
                            targets: 2
                            render: (data, type, full, meta) ->
                                '<button class="btn btn-danger remove-share btn-sm pull-right">
                                    <i class="fa fa-trash"></i>
                                </button>'
                        ]

$ '#shared-to-table'
    .on 'click', '.remove-share', () ->
        tr = $ this
                .parents 'tr'

        sharingTable.row tr
                    .remove()
                    .draw()

sharingForm.on 'select2:select', () ->
    $this = $ this
    data = $this.select2 'data'

    sharingTable.row.add [ data[0].id, data[0].name, null ]
                    .draw()

    $this.val null
         .trigger 'change'

if openMediaDetail == 'close'
    $ ".media-detail"
        .css 'display', 'none'

    $ "i", '#toggle-media-detail'
        .removeClass "fa-toggle-on"
        .addClass "fa-toggle-off"

tile =  $ '.media-container'
            .libraryTile
                height: "450px"
                thumbnail: (library) ->
                    getThumbnail(library.description.mime) || "/uploads/#{library.description.path}/thumb"

detail = $ '.media-detail'
            .libraryDetail
                thumbnail: (identifier, path, mime) ->
                    getThumbnail(mime) || "/uploads/#{path}/mini-display"
                largePreview: (identifier, path, mime) ->
                    "uploads/#{path}"

tile.libraryTile 'checkPagination'

$ '.btn-refresh'
    .click () ->
        ownership = $ 'select[name="ownership"]'
                    .val()

        visibility = $ 'select[name="visibility"]'
                        .val()

        search = $ 'input[name="search"]'
                    .val()

        $ "#btn-share"
            .addClass 'disabled'

        $ "#btn-update"
            .addClass 'disabled'

        payloads = 
            ownership: ownership
            visibility: visibility
            search: search

        tile.libraryTile 'setPayload', payloads
        tile.libraryTile 'refineSearch', "/admin/inoplate-media/libraries?page=1"
        detail.libraryDetail 'clearDetail'

$ "#upload-new"
    .click () ->
        $ '.uploader-container'
            .toggleClass "hide"

$ "#toggle-media-detail"
    .click () ->
        $ ".media-detail"
            .slideToggle 0, ()->
                state = $ this
                            .css 'display'

                if state == 'none'
                    setLocalStorage 'inoplate.media.open-media-detail', 'close'
                else
                    setLocalStorage 'inoplate.media.open-media-detail', 'open'

                tile.libraryTile 'checkPagination'

        $ "i", this
            .toggleClass "fa-toggle-on fa-toggle-off"

$ ".uploader-dismiss"
    .click () ->
        $ this
            .parents ".uploader-container"
                .addClass "hide"

$ '.uploader'
    .on 'uploader.fileAdded', (e, file) ->
        identifier = file.uniqueIdentifier
        tile.libraryTile 'addUploading', identifier, file.name

        $ "[data-upload-id=\"#{identifier}\"]", tile
            .data 'file', file

$ '.uploader'
    .on 'uploader.fileProgress', (e, file) ->
        identifier = file.uniqueIdentifier
        progress = file.progress() * 100
        tile.libraryTile 'setProgress', identifier, "#{progress.toFixed(2)}%25"

$ '.uploader'
    .on 'uploader.fileRetry', (e, file, chunk) ->
        identifier = file.uniqueIdentifier
        tile.libraryTile 'retryUpload', identifier

$ '.uploader'
    .on 'uploader.fileError', (e, file, message, chunk) ->
        identifier = file.uniqueIdentifier
        tile.libraryTile 'markAsError', identifier, message
        $ "[data-upload-id=\"#{identifier}\"] .btn-pause", tile
            .addClass 'hide'

        $ "[data-upload-id=\"#{identifier}\"] .btn-resume", tile
            .removeClass 'hide'

$ '.uploader'
    .on 'uploader.fileSuccess', (e, file, result) ->
        identifier = file.uniqueIdentifier
        result = JSON.parse result
        tile.libraryTile 'markAsSuccess', identifier, result.library

tile.on 'click', 'li.uploading .btn-resume', () ->
    $this = $ this
    $li = $this.parents 'li'
    identifier = $li.data 'uploadId'
    $file = $li.data 'file'

    $ "[data-upload-id=\"#{identifier}\"] .btn-pause", tile
        .removeClass 'hide'

    $ "[data-upload-id=\"#{identifier}\"] .btn-resume", tile
        .addClass 'hide'

    $file.resume()

tile.on 'click', 'li.uploading .btn-pause', () ->
    $this = $ this
    $li = $this.parents 'li'
    identifier = $li.data 'uploadId'
    $file = $li.data 'file'

    $ "[data-upload-id=\"#{identifier}\"] .btn-pause", tile
        .addClass 'hide'

    $ "[data-upload-id=\"#{identifier}\"] .btn-resume", tile
        .removeClass 'hide'

    $file.pause()

tile.on 'click', 'li.error .btn-resume', () ->
    $this = $ this
    $li = $this.parents 'li'
    identifier = $li.data 'uploadId'

    $li.addClass 'uploading'
       .removeClass 'error'
       .removeClass 'selected'

    $ "[data-upload-id=\"#{identifier}\"] .btn-pause", tile
        .removeClass 'hide'

    $ "[data-upload-id=\"#{identifier}\"] .btn-cancel", tile
        .removeClass 'hide'

    $ "[data-upload-id=\"#{identifier}\"] .btn-resume", tile
        .addClass 'hide'

    $file = $li.data 'file'
    $file.retry()

tile.on 'click', 'li.uploading .btn-cancel, li.error .btn-cancel', () ->
    $this = $ this
    $li = $this.parents 'li'
    $file = $li.data 'file'
    identifier = $li.data 'uploadId'

    $file.cancel()
    tile.libraryTile 'remove', identifier

    if $li.hasClass 'selected'
        detail.libraryDetail 'hideError'
        detail.libraryDetail 'clearDetail'

tile.on 'media.library.tile.loading', (e) ->
    $ '.btn-refresh'
        .button 'loading'

tile.on 'media.library.tile.loaded', (e) ->
    $ '.btn-refresh'
        .button 'reset'

tile.on 'media.library.tile.selected', 'li', (e, meta, error) ->
    if(typeof error == 'undefined')
        detail.libraryDetail 'showDetail', meta.id, meta.description.path, meta.description.mime, meta.description
        $ '.title', detail
            .text meta.description.title

        $ '.description', detail
            .text meta.description.description

        $ '.size', detail
            .text bytesToSize(meta.description.size, 2)

        $ "form[data-control=\"publish\"]", detail
            .prop 'action', "/admin/inoplate-media/libraries/publish/#{meta.id}"

        $ "form[data-control=\"unpublish\"]", detail
            .prop 'action', "/admin/inoplate-media/libraries/unpublish/#{meta.id}"

        $ "form[data-control=\"removal\"]", detail
            .prop 'action', "/admin/inoplate-media/libraries/#{meta.id}"

        $ "a.dl-link", detail
            .prop "href", "download/#{meta.description.path}"

        $ 'input[name="title"]', '#library-update-form'
            .val meta.description.title

        $ 'textarea[name="description"]', '#library-update-form'
            .val meta.description.description

        $ "form[data-control=\"general-form\"]", '#library-update-form'
            .prop 'action', "/admin/inoplate-media/libraries/#{meta.id}"

        $ "form[data-control=\"sharing\"]", '#sharing-form'
            .prop 'action', "/admin/inoplate-media/libraries/share/#{meta.id}"

        $ "select[name=\"users\"]", "form[data-control=\"sharing\"]"
            .data "source", "/admin/inoplate-media/libraries/shareable-users/#{meta.id}"

        sharingTable.clear()
        sharingTable.row.add( [author.id, author.name, null] ) for author in meta.sharedTo
        sharingTable.draw()

        if meta.description.updateable
            $ "#btn-update"
                .removeClass 'disabled'
        else
            $ "#btn-update"
                .addClass 'disabled'

        if meta.description.shareable
            $ "#btn-share"
                .removeClass 'disabled'
        else
            $ "#btn-share"
                .addClass 'disabled'

        if meta.description.deletable
            $ "form[data-control=\"removal\"]", detail
                .removeClass 'hide'
        else
            $ "form[data-control=\"removal\"]", detail
                .addClass 'hide'

        if meta.description.publishable

            if meta.description.visibility == 'private'
                $ "form[data-control=\"publish\"]", detail
                    .removeClass 'hide'
                $ "form[data-control=\"unpublish\"]", detail
                    .addClass 'hide'
            else
                $ "form[data-control=\"publish\"]", detail
                    .addClass 'hide'
                $ "form[data-control=\"unpublish\"]", detail
                    .removeClass 'hide'
        else
            $ "form[data-control=\"publish\"]", detail
                .addClass 'hide'
            $ "form[data-control=\"unpublish\"]", detail
                .addClass 'hide'
    else
        detail.libraryDetail 'clearDetail'
        detail.libraryDetail 'showError', error.file

$ "form[data-control=\"publish\"], form[data-control=\"unpublish\"]"
    .on 'ajax.form.success', (event, data, textStatus, jqXHR)->
        $ "form[data-control=\"publish\"]"
            .toggleClass 'hide'

        $ "form[data-control=\"unpublish\"]"
            .toggleClass 'hide'

$ "form[data-control=\"removal\"]"
    .on 'ajax.form.success', (event, data, textStatus, jqXHR)->
        detail.libraryDetail 'clearDetail'
        $ "#library-#{data.library.id}"
            .remove()

        tile.libraryTile 'checkPagination'

        $ "#btn-share"
            .addClass 'disabled'

        $ "#btn-update"
            .addClass 'disabled'

$ "form[data-control=\"sharing\"]"
    .on 'ajax.form.beforeSend', (e, jqXHR, settings) ->
        sharingTableData = sharingTable.data()
        authors = ""

        sharingTableData.each (d) ->
            authors = "#{authors}&authors[]=#{d[0]}"

        settings.data = "#{settings.data}#{authors}"

$ '#btn-update'
    .on 'click', () ->
        $ '#library-update-form'
            .modal 'show'

$ '#btn-share'
    .on 'click', () ->
        $ '#sharing-form'
            .modal 'show'

$ "form[data-control=\"sharing\"], form[data-control=\"publish\"], form[data-control=\"unpublish\"], form[data-control=\"general-form\"]"
    .on 'ajax.form.success', (event, data, textStatus, jqXHR)->
        $li = $ "#library-#{data.library.id}"
        $li.data 'meta', data.library
        $li.trigger 'media.library.tile.selected', data.library

$ "form[data-control=\"sharing\"]"
    .on 'ajax.form.success', (event, data, textStatus, jqXHR)->
        $ '#sharing-form'
            .modal 'hide'

$ "form[data-control=\"general-form\"]"
    .on 'ajax.form.success', (event, data, textStatus, jqXHR)->
        $ '#library-update-form'
            .modal 'hide'