@extends('inoplate-foundation::layouts.default')

{{--*/ $title = trans('inoplate-media::labels.library.title') /*--}}
{{--*/ $subtitle = trans('inoplate-media::labels.library.sub_title') /*--}}

@inject('authis', 'Roseffendi\Authis\Authis')

@push('header-styles-stack')
    <link href="/vendor/inoplate-media/library/index.css" type="text/css" rel="stylesheet" />
@endpush

@section('content')
    @include('inoplate-foundation::partials.content-header')

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-solid">
                    <div class="box-header"></div>
                    <div class="box-filter with-border">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        {{ trans('inoplate-media::labels.library.ownership.title') }}
                                    </span>
                                    <select class="form-control" name="ownership">
                                        <option value="">{{ trans('inoplate-foundation::labels.form.no_filter') }}</option>
                                        <option value="owning">{{ trans('inoplate-media::labels.library.ownership.owning') }}</option>
                                        <option value="shared">{{ trans('inoplate-media::labels.library.ownership.shared') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        {{ trans('inoplate-media::labels.library.visibility.title') }}
                                    </span>
                                    <select class="form-control" name="visibility">
                                        <option value="">{{ trans('inoplate-foundation::labels.form.no_filter') }}</option>
                                        <option value="owning">{{ trans('inoplate-media::labels.library.visibility.public') }}</option>
                                        <option value="shared">{{ trans('inoplate-media::labels.library.visibility.private') }}</option>
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
                    </div>
                    <div class="box-body" id="library-wrapper">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="box-buttons clearfix">
                                    <button class="btn btn-sm btn-bulk btn-unselect btn-default pull-left disabled">
                                        <span><i class="fa fa-circle-o"></i></span>
                                    </button>
                                    <button class="btn btn-sm btn-default btn-refresh pull-left">
                                        <span><i class="fa fa-refresh"></i></span>
                                    </button>
                                    @if($authis->check('media.admin.library.create.get'))
                                        <button class="btn btn-sm btn-create btn-primary pull-right">
                                            <span><i class="fa fa-cloud-upload"></i> {{ trans('inoplate-media::labels.library.create') }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @include('inoplate-media::library.uploader')
                        <div class="file-container">
                            <div class="row"></div>                                
                        </div>
                        @if(!count($libraries))
                            <div class="file-pager">
                                <div class="overlay">
                                    <span></span>
                                    <span></span>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <span class="end-info">{{ trans('inoplate-foundation::labels.pagination.no_more_items') }}</span>
                                        <a class="btn btn-default" data-page="1" href="admin/media/library">{{ trans('inoplate-foundation::labels.pagination.next') }}</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection