class LibraryTile
    constructor: (@$element, options) ->
        @$markup = options.markup || @__getDefaultMarkup()
        @$uploadingMarkup = options.uploadingMarkup || @__getDefaultUploadingMarkup()
        @payloads = options.payloads || {}
        @endInfo = options.endInfo || '.end-info'
        @thumbnail = options.thumbnail
        @multiple = options.multiple || false
        @wrapper = options.holder || 'ul'
        @holder = options.holder || 'li'
        @nextLoader = options.nextLoader || '.next-libraries-loader'
        @$nextLoader = $ @nextLoader, @$element

        @__attachEventToHolder @holder

        @$element.append '<div class="overlay hide"></div>'

        @$element.slimScroll
                height: options.height || '450px'
            .bind 'slimscrolling', (e, pos) =>
                @checkPagination()

        $ window
            .scroll () =>
                @checkPagination()

        $ ".slimScrollBar"
            .hide()

    setPayload: (payloads) ->
        @payloads = payloads

    setMultiple: (multiple) ->
        @multiple = multiple

    setMarkup: (markup) ->
        @$markup = $ markup

    getMarkup: () ->
        return @$markup

    setUploadingMarkup: (markup) ->
        @$uploadingMarkup = $ markup

    getUploadingMarkup: () ->
        return @$uploadingMarkup

    addUploading: (identifier, caption) ->
        $markup = @__buildUploadingMarkup identifier, caption
        $ "#{@wrapper}", @$element
            .prepend $markup

    setProgress: (identifier, progress) ->
        $li = $ "#{@holder}[data-upload-id=\"#{identifier}\"]", @$element
        img = this.__getUploadingHolder "(#{progress})Uploading.."
        $ '.img-placeholder', $li
            .html img

        $img = $ 'img', $li

        Holder.run
            images: $img[0]

    retryUpload: (identifier) ->
        $li = $ "#{@holder}[data-upload-id=\"#{identifier}\"]", @$element
        $li.removeClass 'error'
        $li.addClass 'uploading'

    markAsError: (identifier, message) ->
        $li = $ "#{@holder}[data-upload-id=\"#{identifier}\"]", @$element
        img = this.__getUploadingHolder "Error"
        $img = $ 'img', $li

        $img.attr
            'data-src': img
        
        Holder.run
            images: $img[0]

        $li.removeClass 'uploading'
            .addClass 'error'

        $li.data 'errorMessage', message

    markAsSuccess: (identifier, data) ->
        thumbnail = this.__getThumbnail data
        $li = $ "#{@holder}[data-upload-id=\"#{identifier}\"]", @$element
        $li.removeClass 'uploading'
        $li.data 'meta', data
        $li.attr 'id', "library-#{data.id}"
        $img = $ "img", $li

        $img.prop 'src', thumbnail

    remove: (identifier) ->
        $li = $ "#{@holder}[data-upload-id=\"#{identifier}\"]", @$element
        $li.remove()

    refineSearch: (url, payloads = {}) ->
        $ '.overlay', @$element
            .removeClass 'hide'

        @$element.trigger "media.library.tile.refining-search"

        @__loadMore url, payloads, () =>
            $ "#{@holder}:not(#{@nextLoader})", @$element
                .remove()

            $ '.overlay', @$element
                .addClass 'hide'

            @$element.trigger "media.library.tile.refined-search"

    checkPagination: () ->
        console.log(@$nextLoader.is(':within-viewport'));
        if @$nextLoader.is(':within-viewport') && !@$nextLoader.data('loading')
            url = $ 'a', @$nextLoader
                    .prop 'href'

            @__loadMore url

    clearSelection: () ->
        $ @holder, @$element
            .removeClass "selected"

    __getUploadingHolder: (text) ->
        "<img data-src=\"holder.js/149x149?auto=yes&bg=ecf0f5&text=#{text}\" />"

    __loadMore: (url, payloads = {}, callback) ->
        payloads = this.__normalizePayloads payloads
        url = "#{url}#{payloads}"
        items = []
        @$element.trigger "media.library.tile.loading"
        @$nextLoader.data 'loading', true

        $.get url, (result) =>
            if typeof callback == 'function'
                callback.apply this

            @$nextLoader.before this.__buildMarkup item for item in result.libraries.data

            if result.libraries.next_page_url
                $ 'a', @$nextLoader
                    .prop 'href', result.libraries.next_page_url

                @$nextLoader
                    .data 'loading', false

                $ @endInfo, @$element
                    .addClass 'hide'

                @checkPagination()
            else
                @$nextLoader
                    .addClass 'hide'

                $ @endInfo, @$element
                    .removeClass 'hide'

            @$element.trigger "media.library.tile.loaded"
        ,
            'json'

    __normalizePayloads: (payloads) ->
        globalPayloads = @payloads

        if typeof payloads == 'function'
            payloads = payloads.apply this

        if typeof @payloads == 'function'
            globalPayloads = @payloads.apply this

        payloads = $.extend globalPayloads, payloads, true
        payloads['_'] = Math.random()
        payloads = this.__stringifyPayload payloads

        payloads

    __getThumbnail: (library) ->
        if typeof @thumbnail == 'function'
            @thumbnail.apply this, [library]
        else
            @thumbnail

    __getDefaultUploadingMarkup: () ->
        $ "<li class=\"uploading\" >
                <a class=\"thumbnail\">
                    <div class=\"img-placeholder\"></div>
                    <div class=\"caption\"></div>
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
                </a>
              </li>"

    __getDefaultMarkup: () ->
         $ "<li>
                <a class=\"thumbnail\">
                    <div class=\"img-placeholder\">
                        <img />
                    </div>
                    <div class=\"caption\"></div>
                </a>
            </li>"

    __buildUploadingMarkup: (identifier, caption) ->
        img = @__getUploadingHolder 'Uploading...'
        $markup = @getUploadingMarkup().clone()

        if $markup instanceof $
            $markup
                .attr "data-upload-id", "#{identifier}"

            $ '.img-placeholder', $markup
                .html img

            $ '.caption', $markup
                .text caption

            $img = $ 'img', $markup

            Holder.run
                images: $img[0]

        else if typeof $markup == 'function'
            $markup = $markup.apply this, [identifier, caption]

        $markup

    __buildMarkup: (data) ->
        thumbnail = @__getThumbnail data
        $markup = @getMarkup().clone()

        if $markup instanceof $
            $markup
                .attr "id", "library-#{data.id}"
                .data "meta", data

            $ 'img', $markup
                .prop 'src', thumbnail
                .prop 'alt', data.description.title

            $ '.caption', $markup
                .text data.description.title
        else if typeof $markup == 'function'
            $markup = $markup.apply this, [data]

        $markup

    __attachEventToHolder: (holder) ->
        @$element
            .on 'click', "#{@nextLoader} a", (e) =>
                false

        @$element
            .on 'click', holder, (e) =>
                context = $ e.currentTarget
                target = $ e.target

                actionButtons = target.parents '.btn-action'
                isUploading = context.hasClass('uploading')

                if isUploading || actionButtons.length
                    return

                if !@multiple
                    $ holder, @$element
                        .removeClass "selected"

                context.toggleClass "selected"
                meta = context.data "meta"
                error = context.data 'error-message'

                if typeof meta == 'string'
                    meta = JSON.parse meta

                if typeof error == 'string'
                    error = JSON.parse error

                if  context.hasClass 'selected'
                    context.trigger "media.library.tile.selected", [meta, error]
                else
                    context.trigger "media.library.tile.unselected", [meta, error]

    __stringifyPayload: (payloads) ->
        string = ""

        for k,v of payloads
            string = "#{string}&#{k}=#{v}"

        string


# PLUGIN DEFINITION
# ============================

$.fn.libraryTile = (option) ->
    args = arguments
    defaults = 
        height: "450px"

    this.each () ->
        $this = $ this
        data = $this.data('library.tile')
        options = $.extend {}, defaults, $this.data(), typeof option == 'object' && option
        if !data 
            $this.data 'library.tile', (data = new LibraryTile $this, options)

        if typeof option == 'string'
            argsToSent = []

            for k,v of args
                if k > 0
                    argsToSent.push v

            data[option].apply(data, argsToSent)

    this