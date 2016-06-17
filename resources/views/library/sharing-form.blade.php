<div class="form-group">
  <input type="hidden" name="_token" value="{{ csrf_token() }}" />
  <label for="sharing" class="control-label">{{ trans('inoplate-media::labels.library.form.share_to') }}</label>
  <select name="users" id="sharing" class="form-control not-select2" style="width:100%"></select>
</div>