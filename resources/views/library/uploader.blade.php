@push('header-styles-stack')
    <link href="/vendor/inoplate-media/library/uploader.css" type="text/css" rel="stylesheet" />
@endpush

@push('footer-scripts-stack')
    <script src="/vendor/inoplate-media/vendor/flowjs/flow.min.js" type="text/javascript"></script>
    <script src="/vendor/inoplate-media/vendor/holderjs/holder.min.js" type="text/javascript"></script>
    <script src="/vendor/inoplate-media/library/uploader.js" type="text/javascript"></script>
@endpush

<div class="uploader" data-chunk="{{ config('inoplate.media.library.upload.chunk', size_to_bytes('1M')) }}" data-maxupload="{{ size_to_bytes(config('inoplate.media.library.upload.maxupload', 8).'m') }}">
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
            <p> {{ config('inoplate.media.library.upload.maxupload', 8) }} MB {{ trans('inoplate-media::labels.library.maximum') }}</p>
        </div>
    </div>
</div>