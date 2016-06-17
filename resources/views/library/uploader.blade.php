<div class="uploader" data-chunk="{{ config('inoplate.media.library.upload.size.chunk', size_to_bytes('1M')) }}" data-maxupload="{{ size_to_bytes(config('inoplate.media.library.upload.size.max', 8).'m') }}">
    <div class="uploader-dropzone">
        <div class="uploader-dismiss">
            <i class="fa fa-times"></i>
        </div>
        <div class="drag-drop-inside">
            <p class="info">{{ trans('inoplate-media::labels.library.drop_files') }}</p>
            <p>or</p>
            <p class="buttons">
                <span class="btn btn-primary btn-browse">{{ trans('inoplate-media::labels.library.select_files') }}</span>
            </p>
            <p> {{ config('inoplate.media.library.upload.size.max', 8) }} MB {{ trans('inoplate-media::labels.library.maximum') }}</p>
        </div>
    </div>
</div>

@addCss([
    'vendor/inoplate-media/library/uploader.css'
])

@addJs([
    'vendor/inoplate-media/vendor/flowjs/flow.min.js',
    'vendor/inoplate-media/library/uploader.js'
])