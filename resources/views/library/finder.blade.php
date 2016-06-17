@inject('authis', 'Roseffendi\Authis\Authis')

<div class="modal fade library-finder" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="box-filter with-border">
                @include('inoplate-media::library.filter')
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="box-buttons clearfix">
                            <button class="btn btn-sm btn-default btn-refresh pull-left no-margin" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>">
                                <i class="fa fa-refresh"></i>
                            </button>
                            @if($authis->check('media.admin.libraries.create.get'))
                                <button class="btn btn-sm btn-create btn-primary pull-right upload-new">
                                    <i class="fa fa-cloud-upload"></i> {{ trans('inoplate-media::labels.library.create') }}
                                </button>
                            @endif
                            <button class="btn btn-sm btn-share btn-info pull-right disabled btn-select">
                                <i class="fa fa-check-circle-o"></i> {{ trans('inoplate-media::labels.library.select') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="uploader-container hide">
                    @include('inoplate-media::library.uploader')
                </div>
            </div>
            <div class="media-display">
                @include('inoplate-media::library.tile')
            </div>
        </div>
    </div>
</div>

@addCss([
    'vendor/inoplate-media/library/finder.css'
])

@addJs([
    'vendor/inoplate-media/library/finder.js'
])