class LibraryDetail
    constructor: (@$element, options) ->
        @thumbnail = options.thumbnail
        @largePreview = options.largePreview

    showDetail: (identifier, path, mime, descriptions) ->
        @hideError()
        $ '.nothing-selected', @$element
            .addClass 'hide'

        preview = @__buildPreview(identifier, path, mime, descriptions)

        $ ".media-full-preview", @$element
            .html preview

        $ '.media-full-preview audio, .media-full-preview video', @$element
            .mediaelementplayer
                pluginPath: ''
                enablePluginDebug: false
                enableAutosize: true
                enableKeyboard: true
                audioHeight: 225
                features: ['playpause','progress','current','duration','tracks','volume','backlight']
                timerRate: 250
                success: (media, node, player) ->
                    $ "##{node.id}-mode"
                        .html "mode: #{media.pluginType}"

        $ "a.fancybox", @$element
            .fancybox()

        $ '.media-preview-container', @$element
            .removeClass 'hide'

        $ '.selection-exist', @$element
            .removeClass 'hide'

    showError: (errors = []) ->
        @clearDetail()
        @hideDetail()

        errors = @__buildErrors errors

        $ '.media-errors', @$element
            .html errors
            .removeClass 'hide'

    clearDetail: () ->
        @destroyDetail()

        $ '.selection-exist', @$element
            .addClass 'hide'

        $ '.nothing-selected', @$element
            .removeClass 'hide'

    hideDetail: () ->
        $ '.media-preview-container', @$element
            .addClass 'hide'

    destroyDetail: () ->
        $ '.media-full-preview', @$element
            .html ''

    hideError: () ->
        $ '.media-errors', @$element
            .addClass 'hide'

        $ '.media-preview-container', @$element
            .removeClass 'hide'

    __buildErrors: (errors) ->
        parsed = ''

        if $.isArray(errors)
            $.each errors, (key, value) ->
                parsed = "#{parsed}<li>#{value}</li>"
        else
            parsed = "<li>#{errors}</li>"

        "<ul>#{parsed}</ul>"

    __buildPreview: (identifier, path, mime, descriptions) ->
        thumbnail = @__getPreview(identifier, path, mime)
        if isImage mime
            largePreview = @__getLargePreview(identifier, path, mime) 

            preview = "<a class=\"fancybox fancybox.image\" 
                          href=\"#{largePreview}\"
                          title=\"uploads/#{descriptions.title}\"
                        >
                            <img src=\"#{thumbnail}\" alt=\"#{descriptions.title}\" />
                       </a>"

        else if isVideo(mime)
            preview = "<video width=\"300\" height=\"225\" poster=\"#{thumbnail}\">
                            <source type=\"#{mime}\" src=\"uploads/#{descriptions.path}\" />
                        </video>"
        else if isAudio(mime)
            preview = "<audio width=\"300\" poster=\"#{thumbnail}\">
                            <source type=\"#{mime}\" src=\"uploads/#{descriptions.path}\" />
                        </audio>"
        else
            preview = "<img src=\"#{thumbnail}\" alt=\"#{descriptions.title}\" />"

        preview

    __getLargePreview: (identifier, path, mime) ->
        if typeof @largePreview == 'function'
            @largePreview.apply this, [identifier, path, mime]
        else
            path

    __getPreview: (identifier, path, mime) ->
        if typeof @thumbnail == 'function'
            @thumbnail.apply this, [identifier, path, mime]
        else
            @thumbnail

# PLUGIN DEFINITION
# ============================

$.fn.libraryDetail = (option) ->
    args = arguments
    defaults = {}

    this.each () ->
        $this = $ this
        data = $this.data('library.detail')
        options = $.extend {}, defaults, $this.data(), typeof option == 'object' && option
        if !data 
            $this.data 'library.detail', (data = new LibraryDetail $this, options)

        if typeof option == 'string'
            argsToSent = []

            for k,v of args
                if k > 0
                    argsToSent.push v

            data[option].apply(data, argsToSent)

    this