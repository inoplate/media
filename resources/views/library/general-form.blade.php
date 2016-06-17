<div class="form-group">
  <input type="hidden" name="_token" value="{{ csrf_token() }}" />
  <label for="title" class="control-label">{{ trans('inoplate-media::labels.library.form.title') }}</label>
  <input type="text" name="title" id="title" class="form-control" data-rule-required=true value="{{ old('title', isset($library['title']) ? $library['title'] : '' ) }}" placeholder="{{ trans('inoplate-media::labels.library.form.title') }}">
  @include('inoplate-adminutes::partials.form-error', ['field' => 'title'])
</div>
<div class="form-group">
  <input type="hidden" name="_token" value="{{ csrf_token() }}" />
  <label for="description" class="control-label">{{ trans('inoplate-media::labels.library.form.description') }}</label>
  <textarea name="description" id="description" class="form-control" placeholder="{{ trans('inoplate-media::labels.library.form.description') }}">{{ old('description', isset($library['description']) ? $library['description'] : '' ) }}</textarea>
  @include('inoplate-adminutes::partials.form-error', ['field' => 'description'])
</div>