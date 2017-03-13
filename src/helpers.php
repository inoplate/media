<?php

if (! function_exists('is_image')) {

    /**
     * Determine if mime is image
     * @param  string   $mime
     * @return boolean
     */
    function is_image($mime)
    {
        return substr($mime, 0, 5) == 'image' ?: false;
    }

}

if (! function_exists('is_video')) {

    /**
     * Determine if mime is video
     * @param  string   $video
     * @return boolean
     */
    function is_video($mime)
    {
        return substr($mime, 0, 5) == 'video' ?: false;
    }

}

if (! function_exists('is_audio')) {

    /**
     * Determine if mime is audio
     * @param  string   $audio
     * @return boolean
     */
    function is_audio($mime)
    {
        return substr($mime, 0, 5) == 'audio' ?: false;
    }

}

if( !function_exists('get_thumbnail')) {

    /**
     * Retrieve image thumbnail
     *
     * @param  string $mime
     * @return string
     */
    function get_thumbnail($mime, $forImage)
    {
        if(is_image($mime))
            return $forImage;
        elseif(is_video($mime))
            return "/vendor/inoplate-media/images/medias/video_128px.png";
        elseif(is_audio($mime))
            return "/vendor/inoplate-media/images/medias/music_128px.png";
        elseif(($mime == 'application/excel')||($mime == 'application/vnd.ms-excel')||($mime == 'application/x-excel')||($mime == 'application/x-msexcel'))
            return "/vendor/inoplate-media/images/medias/xls_128px.png";
        elseif(($mime == 'application/mspowerpoint')||($mime == 'application/powerpoint')||($mime == 'application/vnd.ms-powerpoint')||($mime == 'application/x-mspowerpoint'))
            return "/vendor/inoplate-media/images/medias/xls_128px.png";
        else
            return "/vendor/inoplate-media/images/medias/file_128px.png";
    }
}

if(! function_exists('format_size_units')) {

    /**
     * Format php to readable size units
     * @param  string $size
     * @return string
     */
    function format_size_units($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

}

if(! function_exists('size_to_bytes')) {

    /**
     * Convert size unit to bytes
     * @param  string $size
     * @return string
     */
    function size_to_bytes($size)
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size)-1]);
        $size = preg_replace("/[^0-9]/", "", $size);
        
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }

        return $size;
    }

}
