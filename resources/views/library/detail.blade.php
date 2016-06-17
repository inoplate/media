<div class="media-detail">
    <div class="media-detail-container">
        <div class="media-errors text-red hide">
            <ul></ul>
        </div>
        <div class="media-preview-container">
            <div class="nothing-selected">
                <i class="fa fa-5x fa-eye-slash" aria-hidden="true"></i>
                <p>{{ trans('inoplate-media::messages.library.nothing_selected') }}</p>
            </div>
            <div class="selection-exist hide">
                <div class="media-full-preview"></div>
                <div class="media-description">
                    <section>{{ trans('inoplate-media::labels.library.information') }}</section>
                    <table class="table no-border">
                        <tbody>
                            <tr>
                                <td>{{ trans('inoplate-media::labels.library.form.title') }}</td>
                                <td>:</td>
                                <td><span class="title"></span></td>
                            </tr>
                            <tr>
                                <td>{{ trans('inoplate-media::labels.library.form.description') }}</td>
                                <td>:</td>
                                <td><span class="description"></span></td>
                            </tr>
                            <tr>
                                <td>{{ trans('inoplate-media::labels.library.size') }}</td>
                                <td>:</td>
                                <td><span class="size"></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="media-action-btn box-buttons clearfix">
                    <form class="ajax undoable" method="post" data-control="removal">
                        <input type="hidden" value="delete" name="_method" />
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <button type="submit" class="btn btn-sm btn-danger pull-right" data-loading-text="<i class='fa fa-trash' aria-hidden='true'></i> Delete <i class='fa fa-circle-o-notch fa-spin'></i>">
                            <i class="fa fa-trash" aria-hidden="true"></i> {{ trans('inoplate-media::labels.library.form.delete') }}
                        </button>
                    </form>
                    <form class="ajax" method="post" data-control="publish">
                        <input type="hidden" value="put" name="_method" />
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <button type="submit" class="btn btn-sm btn-warning pull-right" data-loading-text="<i class='fa fa-eye' aria-hidden='true'></i> Publish <i class='fa fa-circle-o-notch fa-spin'></i>">
                            <i class="fa fa-eye" aria-hidden="true"></i> {{ trans('inoplate-media::labels.library.form.publish') }}
                        </button>
                    </form>
                    <form class="ajax" method="post" data-control="unpublish">
                        <input type="hidden" value="put" name="_method" />
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <button type="submit" class="btn btn-sm btn-warning pull-right" data-loading-text="<i class='fa fa-eye-slash' aria-hidden='true'></i> Publish <i class='fa fa-circle-o-notch fa-spin'></i>">
                            <i class="fa fa-eye-slash" aria-hidden="true"></i> {{ trans('inoplate-media::labels.library.form.unpublish') }}
                        </button>
                    </form>
                    <a target="_blank" class="dl-link btn btn-sm btn-primary pull-right" href="#"><i class="fa fa-download" aria-hidden="true"></i> {{ trans('inoplate-media::labels.library.form.download') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

@addCss([
    'vendor/inoplate-media/vendor/fancybox/jquery.fancybox.css',
    'vendor/inoplate-media/vendor/mediaelement/mediaelementplayer.min.css',
    'vendor/inoplate-media/library/detail.css'
])

@addJs([
    'vendor/inoplate-media/vendor/fancybox/jquery.fancybox.pack.js',
    'vendor/inoplate-media/vendor/mediaelement/mediaelement-and-player.min.js',
    'vendor/inoplate-media/library/detail.js'
])