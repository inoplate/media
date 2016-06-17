@extends('inoplate-foundation::layouts.default')

@php($title = trans('inoplate-media::labels.library.title'))
@php($subtitle = trans('inoplate-media::labels.library.sub_title'))

@inject('authis', 'Roseffendi\Authis\Authis')

@addAsset('datatables')

@section('content')
    @include('inoplate-foundation::partials.content-header')
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-solid">
                    <div class="box-header"></div>
                    <div class="box-filter with-border">
                        @include('inoplate-media::library.filter')
                    </div>
                    <div class="box-body" id="library-wrapper">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="box-buttons clearfix">
                                    <button class="btn btn-sm btn-default btn-refresh pull-left no-margin" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>">
                                        <i class="fa fa-refresh"></i>
                                    </button>
                                    <button class="btn btn-sm pull-right" id="toggle-media-detail">
                                        <i class="fa fa-toggle-on"></i>
                                    </button>
                                    <button class="btn btn-sm btn-share btn-info pull-right disabled" id="btn-share">
                                        <i class="fa fa-share-alt"></i> {{ trans('inoplate-media::labels.library.share') }}
                                    </button>
                                    <button class="btn btn-sm btn-update btn-default pull-right disabled" id="btn-update">
                                        <i class="fa fa-pencil"></i> {{ trans('inoplate-foundation::labels.update') }}
                                    </button>
                                    @if($authis->check('media.admin.libraries.create.get'))
                                        <button class="btn btn-sm btn-create btn-primary pull-right" id="upload-new">
                                            <i class="fa fa-cloud-upload"></i> {{ trans('inoplate-media::labels.library.create') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="uploader-container hide">
                            @include('inoplate-media::library.uploader')
                        </div>
                    </div>
                    <div class="media-display">
                        @include('inoplate-media::library.tile')
                        @include('inoplate-media::library.detail')
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" data-backdrop="static" role="dialog" aria-labelledby="form-modal" id="library-update-form">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"> {{ trans('inoplate-foundation::labels.update') }} </h4>
                    </div>
                    <form class="ajax" method="post" data-control="general-form">
                        <input type="hidden" name="_method" value="put" />
                        <div class="modal-body">
                            @include('inoplate-media::library.general-form')
                        </div>
                        <div class="modal-footer">
                            @section('form-button')
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('inoplate-foundation::labels.cancel') }}</button>
                                <button type="submit" class="btn btn-primary">{{ trans('inoplate-foundation::labels.form.save') }}</button>
                            @show
                        </div>
                    </form>
                    <div class="overlay hide">
                        <div class="loading">Loading..</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" data-backdrop="static" role="dialog" aria-labelledby="form-modal" id="sharing-form">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"> {{ trans('inoplate-media::labels.library.form.sharing') }} </h4>
                    </div>
                    <form class="ajax" method="post" data-control="sharing">
                        <input type="hidden" name="_method" value="put" />
                        <div class="modal-body">
                            @include('inoplate-media::library.sharing-form')
                            @include('inoplate-media::library.sharing')
                        </div>
                        <div class="modal-footer">
                            @section('form-button')
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('inoplate-foundation::labels.cancel') }}</button>
                                <button type="submit" class="btn btn-primary">{{ trans('inoplate-foundation::labels.form.save') }}</button>
                            @show
                        </div>
                    </form>
                    <div class="overlay hide">
                        <div class="loading">Loading..</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@addCss('vendor/inoplate-media/library/index.css')
@addJs('vendor/inoplate-media/library/index.js')