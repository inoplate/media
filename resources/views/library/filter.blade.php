<div class="row">
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-addon">
                {{ trans('inoplate-media::labels.library.ownership.title') }}
            </span>
            <select class="form-control" name="ownership" style="width:100%">
                <option value="">{{ trans('inoplate-foundation::labels.form.no_filter') }}</option>
                <option value="1">{{ trans('inoplate-media::labels.library.ownership.owning') }}</option>
                <option value="2">{{ trans('inoplate-media::labels.library.ownership.shared') }}</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-addon">
                {{ trans('inoplate-media::labels.library.visibility.title') }}
            </span>
            <select class="form-control" name="visibility" style="width:100%">
                <option value="">{{ trans('inoplate-foundation::labels.form.no_filter') }}</option>
                <option value="public">{{ trans('inoplate-media::labels.library.visibility.public') }}</option>
                <option value="private">{{ trans('inoplate-media::labels.library.visibility.private') }}</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-addon">
                {{ trans('inoplate-foundation::labels.search.title') }}
            </span>
            <input name="search" class="form-control" placeholder="{{ trans('inoplate-foundation::labels.search.placeholder') }}" />
        </div>
    </div>
</div>