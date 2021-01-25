<div class="card card-profile shadow">
    <div class="px-4">
      <div class="mt-5">
        <h3>{{ __('Delivery Address') }}<span class="font-weight-light"></span></h3>
      </div>
      <div class="card-content border-top">
        <br />
        <div class="form-group{{ $errors->has('addressID') ? ' has-danger' : '' }}">
            @if(count(auth()->user()->addresses))
                <select name="addressID" id="addressID" class="form-control{{ $errors->has('addressID') ? ' is-invalid' : '' }}" required>
                    @foreach (auth()->user()->addresses->reverse() as $address)
                        <option value={{ $address->id }}>{{$address->address}}</option>
                    @endforeach
                </select>
                @if ($errors->has('addressID'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('addressID') }}</strong>
                    </span>
                @endif
            @else
                <h6 id="address-complete-order">{{ __('You don`t have any address. Please add new one') }}.</h6>
            @endif
        </div>
        <div class="form-group">
            <button type="button" data-toggle="modal" data-target="#modal-order-new-address"  class="btn btn-outline-success">{{ __('Add new') }}</button>
        </div>
      </div>
      <br />
      <br />
    </div>
  </div>
  <br />
