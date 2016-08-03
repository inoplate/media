class LibraryFinder
    constructor: (@$element, options) ->
        @id = null
        @selected = []
        @multiple = options.multiple || false

        @tile = @__initTile()
        @__attachEvent()
        @__init()

    show: (id, multiple = false) ->
        @id = id
        @multiple = multiple

        @tile.libraryTile "setMultiple", multiple

        @$element.modal 'show'

    getSelected: () ->
        @selected

    __init: () ->
        @$element.modal
            show: false

    __initTile: () ->
        tile =  $ '.media-container', @$element
            .libraryTile
                height: "450px"
                thumbnail: (library) ->
                    getThumbnail(library.description.mime) || "/uploads/#{library.description.path}/thumb"

        tile

    __attachEvent: () ->
        $ '.btn-refresh', @$element
            .click () =>
                ownership = $ 'select[name="ownership"]'
                            .val()

                visibility = $ 'select[name="visibility"]'
                                .val()

                search = $ 'input[name="search"]'
                            .val()

                $ ".btn-select", @$element
                    .addClass 'disabled'

                payloads = 
                    ownership: ownership
                    visibility: visibility
                    search: search

                @tile.libraryTile 'setPayload', payloads
                @tile.libraryTile 'refineSearch', "/admin/inoplate-media/libraries?page=1"
                @tile.libraryTile 'clearSelection'

        $ ".upload-new", @$element
            .click () =>
                $ '.uploader-container', @$element
                    .toggleClass "hide"

        $ ".uploader-dismiss", @$element
            .click () =>
                $ '.uploader-container', @$element
                    .addClass "hide"

        $ '.uploader', @$element
            .on 'uploader.fileAdded', (e, file) =>
                identifier = file.uniqueIdentifier
                @tile.libraryTile 'addUploading', identifier, file.name

                $ "[data-upload-id=\"#{identifier}\"]", @tile
                    .data 'file', file

        $ '.uploader', @$element
            .on 'uploader.fileProgress', (e, file) =>
                identifier = file.uniqueIdentifier
                progress = file.progress() * 100
                @tile.libraryTile 'setProgress', identifier, "#{progress.toFixed(2)}%25"

        $ '.uploader', @$element
            .on 'uploader.fileRetry', (e, file, chunk) =>
                identifier = file.uniqueIdentifier
                @tile.libraryTile 'retryUpload', identifier

        $ '.uploader', @$element
            .on 'uploader.fileError', (e, file, message, chunk) =>
                identifier = file.uniqueIdentifier
                @tile.libraryTile 'markAsError', identifier, message
                $ "[data-upload-id=\"#{identifier}\"] .btn-pause", @tile
                    .addClass 'hide'

                $ "[data-upload-id=\"#{identifier}\"] .btn-resume", @tile
                    .removeClass 'hide'

        $ '.uploader', @$element
            .on 'uploader.fileSuccess', (e, file, result) =>
                identifier = file.uniqueIdentifier
                result = JSON.parse result
                @tile.libraryTile 'markAsSuccess', identifier, result.library

        @tile.on 'click', 'li.uploading .btn-resume', (e) =>
            $this = $ e.currentTarget
            $li = $this.parents 'li'
            identifier = $li.data 'uploadId'
            $file = $li.data 'file'

            $ "[data-upload-id=\"#{identifier}\"] .btn-pause", @tile
                .removeClass 'hide'

            $ "[data-upload-id=\"#{identifier}\"] .btn-resume", @tile
                .addClass 'hide'

            $file.resume()

        @tile.on 'click', 'li.uploading .btn-pause', (e) =>
            $this = $ e.currentTarget
            $li = $this.parents 'li'
            identifier = $li.data 'uploadId'
            $file = $li.data 'file'

            $ "[data-upload-id=\"#{identifier}\"] .btn-pause", @tile
                .addClass 'hide'

            $ "[data-upload-id=\"#{identifier}\"] .btn-resume", @tile
                .removeClass 'hide'

            $file.pause()

        @tile.on 'click', 'li.error .btn-resume', (e) =>
            $this = $ e.currentTarget
            $li = $this.parents 'li'
            identifier = $li.data 'uploadId'

            $li.addClass 'uploading'
               .removeClass 'error'
               .removeClass 'selected'

            $ "[data-upload-id=\"#{identifier}\"] .btn-pause", @tile
                .removeClass 'hide'

            $ "[data-upload-id=\"#{identifier}\"] .btn-cancel", @tile
                .removeClass 'hide'

            $ "[data-upload-id=\"#{identifier}\"] .btn-resume", @tile
                .addClass 'hide'

            $file = $li.data 'file'
            $file.retry()

        @tile.on 'click', 'li.uploading .btn-cancel, li.error .btn-cancel', (e) =>
            $this = $ e.currentTarget
            $li = $this.parents 'li'
            $file = $li.data 'file'
            identifier = $li.data 'uploadId'

            $file.cancel()
            @tile.libraryTile 'remove', identifier

        @tile.on 'media.library.tile.loading', (e) =>
            $ '.btn-refresh', @$element
                .button 'loading'

        @tile.on 'media.library.tile.loaded', (e) =>
            $ '.btn-refresh', @$element
                .button 'reset'

        @tile.on 'media.library.tile.selected', (e, meta, error) =>
            if meta
                if @multiple
                    @selected.push meta
                else
                    @selected = [meta]

            if @selected.length == 0
                $ '.btn-select', @$element
                    .addClass 'disabled'
            else
                $ '.btn-select', @$element
                    .removeClass 'disabled'

        @tile.on 'media.library.tile.unselected', (e, meta, error) =>
            if meta
                if @multiple
                    index = @selected.indexOf meta

                    if index != -1
                        @selected.splice(index, 1)
                else
                    @selected = []

            if @selected.length == 0
                $ '.btn-select', @$element
                    .addClass 'disabled'
            else
                $ '.btn-select', @$element
                    .removeClass 'disabled'

        @$element.on 'click', '.btn-select', () =>
            if @multiple
                @$element.trigger "media.finder.selected", [@id, @getSelected()]
            else
                @$element.trigger "media.finder.selected", [@id, @getSelected()[0]]

            $ ".btn-select", @$element
                .addClass 'disabled'

            @selected = []
            @$element.modal 'hide'
            @tile.libraryTile 'clearSelection'

# PLUGIN DEFINITION
# ============================

$.fn.libraryFinder = (option) ->
    args = arguments
    defaults = {}

    this.each () ->
        $this = $ this
        data = $this.data('library.finder')
        options = $.extend {}, defaults, $this.data(), typeof option == 'object' && option
        if !data 
            $this.data 'library.finder', (data = new LibraryFinder $this, options)

        if typeof option == 'string'
            argsToSent = []

            for k,v of args
                if k > 0
                    argsToSent.push v

            data[option].apply(data, argsToSent)

        $this.on 'shown.bs.modal', () ->
            $ '.media-container', this
                .libraryTile 'checkPagination'

    this

# INIT PLUGIN TO DOM
# ===========================

$ '.library-finder'
    .libraryFinder()