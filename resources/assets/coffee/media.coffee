window.getThumbnail = (mime) ->
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

window.isImage = (mime) ->
    if(mime.substring(0,5) == 'image')
        return true

    return false

window.isVideo = (mime) ->
    if(mime.substring(0,5) == 'video')
        return true

    return false

window.isAudio = (mime) ->
    if(mime.substring(0,5) == 'audio')
        return true

    return false

window.bytesToSize = (bytes, precision) ->
    if bytes == 0 
        'n/a'
    else
        i = Math.floor( Math.log(bytes) / Math.log(1024) );
        ( bytes / Math.pow(1024, i) ).toFixed(precision) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];