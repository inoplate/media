<div class="media-tile">
    <div class="media-tile-container">
        <div class="media-container">
            <ul class="thumbnail-wrapper">
                <li class="next-libraries-loader" data-loading="false">
                    <a class="thumbnail" href="admin/inoplate-media/libraries?">
                        <div class="img-placeholder">
                        </div>
                        <div class="caption">Loading next library...</div>
                    </a>
                </li>
            </ul>
            <div class="row end-info hide">
                <div class="col-md-12">                              
                    <span class="end-info">{{ trans('inoplate-foundation::labels.pagination.no_more_items') }}</span>                                   
                </div>
            </div>
        </div>
    </div>
</div>

@addCss([
    'vendor/inoplate-media/library/tile.css'
])

@addJs([
    'vendor/inoplate-foundation/vendor/within-viewport/withinviewport.js',
    'vendor/inoplate-foundation/vendor/within-viewport/jquery.withinviewport.js',
    'vendor/inoplate-media/vendor/holderjs/holder.min.js',
    'vendor/inoplate-media/library/tile.js'
])