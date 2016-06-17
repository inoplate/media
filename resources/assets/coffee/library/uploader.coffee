class LibraryUploader
    constructor: (@$element, options) ->
        @__attachEvent()
        @__init()

    __attachEvent: () ->
        $ document 
            .on 'drop dragover', (e)->
                e.preventDefault()
                return

        $ document
            .on 'dragleave drop', (e)->
                $ '.uploader-dropzone', @$element
                    .removeClass 'hover'

        $ document
            .on 'dragover', (e)->
                dropzone = $ '.uploader-dropzone', @$element
                timeout = window.dropZoneTimeout

                if (!timeout)
                    dropzone.addClass('in')
                else
                    clearTimeout(timeout)

                found = false
                node = e.target

                if $(node).hasClass('uploader-dropzone')
                    if !$(node).hasClass('hover')
                        $(node).addClass 'hover'

                else if $(node).parents('.uploader-dropzone').length > 0
                    if !$(node).parents('.uploader-dropzone').hasClass('hover')
                        $(node).parents('.uploader-dropzone').addClass('hover')
                else
                    dropzone.removeClass 'hover'

                window.dropZoneTimeout = setTimeout ()->
                    window.dropZoneTimeout = null

                    return
                , 100

                return

    __init: () ->
        target = '/admin/inoplate-media/libraries/upload'
        chunkSize = @$element.data('chunk')
        maxUploadSize = @$element.data('maxupload')
        browseId = $ ".btn-browse", @$element
        dropzoneId = $ ".uploader-dropzone", @$element
        fileContainerId = $ ".file-container", @$element

        ###
            The simulaneous upload set to 1
            Its because Laravel framework on server side has mysterious session persistence problem
        ###

        uploader = new Flow
            target: target
            forceChunkSize: true
            chunkSize: chunkSize
            simultaneousUploads: 1
            headers:
                'X-Requested-With' : 'XMLHttpRequest'
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')


        uploader.assignBrowse(browseId[0])
        uploader.assignDrop(dropzoneId[0])

        uploader.on 'fileAdded', (file) =>
            if file.size > maxUploadSize
                return false

            @$element.trigger 'uploader.fileAdded', [file]

            return

        uploader.on 'fileProgress', (file) =>
            @$element.trigger 'uploader.fileProgress', [file]

            return

        uploader.on 'filesSubmitted', (file, event) =>
            uploader.upload()

            @$element.trigger 'uploader.filesSubmitted', [file, event]

            return

        uploader.on 'fileSuccess', (file, message) =>
            @$element.trigger 'uploader.fileSuccess', [file, message]

            return

        uploader.on 'fileRetry', (file, chunk) =>
            @$element.trigger 'uploader.fileRetry', [file, chunk]

            return

        uploader.on 'fileError', (file, message, chunk) =>
            @$element.trigger 'uploader.fileError', [file, message, chunk]

            return

# PLUGIN DEFINITION
# ============================

$.fn.libraryUploader = (option) ->
    args = arguments
    defaults = {}

    this.each () ->
        $this = $ this
        data = $this.data('library.uploader')
        options = $.extend {}, defaults, $this.data(), typeof option == 'object' && option
        if !data 
            $this.data 'library.uploader', (data = new LibraryUploader $this, options)

        if typeof option == 'string'
            argsToSent = []

            for k,v of args
                if k > 0
                    argsToSent.push v

            data[option].apply(data, argsToSent)

    this

# INIT PLUGIN TO DOM
# ===========================

$ ->
    $ '.uploader'
        .libraryUploader()