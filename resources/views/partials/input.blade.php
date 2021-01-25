<div class="form-group{{ $errors->has($id) ? ' has-danger' : '' }}">
    <label class="form-control-label" for="{{ $id }}">{{ __($name) }}</label>
    <input type="text" name="{{ $id }}" id="{{ $id }}" class="form-control form-control-alternative{{ $errors->has($id) ? ' is-invalid' : '' }}" placeholder="{{ __($placeholder) }}" value="{{ old($id, isset($value)?$value:'') }}" <?php if($required) {echo 'required';} ?> autofocus>
    @if ($errors->has($id))
        <span class="invalid-feedback" role="alert">
            <strong>{{ $errors->first($id) }}</strong>
        </span>
    @endif
</div>
