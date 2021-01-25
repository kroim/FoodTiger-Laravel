<div class="modal fade modal-xl" id="modal-order-details" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
    <div class="modal-dialog modal-l modal-dialog-centered" style="max-width:1140px" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modal-title-order"></h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-7">
                        <h3 id="restorant-name"><h3>
                        <p id="restorant-address"></p>
                        <p id="restorant-info"></p>
                        <h4 id="client-name"><h4>
                        <p id="client-info"></p>
                        <h4>Order</h4>
                        <p>
                            <ol id="order-items">
                            </ol>
                        </p>
                        <h4 id="delivery-price"><h4>
                        <h4>Total<h4>
                        <p id="total-price"></p>
                    </div>
                    <div class="col-md-5">
                        @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('owner') || auth()->user()->hasRole('client'))
                        <div class="card">
                            <!-- Card header -->
                            <div class="card-header">
                            <!-- Title -->
                                <h5 class="h3 mb-0">{{ __("Status History")}}</h5>
                            </div>
                            <!-- Card body -->
                            <div class="card-body">
                                <div class="timeline timeline-one-side" id="status-history" style="height: 240px; overflow-y: scroll" data-timeline-content="axis" data-timeline-axis-style="dashed">
                                <!--<div class="timeline-block">
                                    <span class="timeline-step badge-success">
                                        <i class="ni ni-bell-55"></i>
                                    </span>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between pt-1">
                                            <div>
                                                <span class="text-muted text-sm font-weight-bold">Order received</span>
                                            </div>
                                            <div class="text-right">
                                                <small class="text-muted"><i class="fas fa-clock mr-1"></i>2 hrs ago</small>
                                            </div>
                                        </div>
                                        <h6 class="text-sm mt-1 mb-0">Client CLIENT_NAME makes the order</h6>
                                    </div>
                                </div>-->
                            </div>
                        </div>
                        @endif
                        @if(auth()->user()->hasRole('driver'))
                        <div class="card card-status-history-driver">
                            <!-- Card header -->
                            <div class="card-header">
                            <!-- Title -->
                                <h5 class="h3 mb-0">{{ __("Status History")}}</h5>
                            </div>
                            <!-- Card body -->
                            <div class="card-body">
                                <div class="timeline timeline-one-side" id="status-history" style="height: 240px; overflow-y: scroll;" data-timeline-content="axis" data-timeline-axis-style="dashed">
                                <!--<div class="timeline-block">
                                    <span class="timeline-step badge-success">
                                        <i class="ni ni-bell-55"></i>
                                    </span>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between pt-1">
                                            <div>
                                                <span class="text-muted text-sm font-weight-bold">Order received</span>
                                            </div>
                                            <div class="text-right">
                                                <small class="text-muted"><i class="fas fa-clock mr-1"></i>2 hrs ago</small>
                                            </div>
                                        </div>
                                        <h6 class="text-sm mt-1 mb-0">Client CLIENT_NAME makes the order</h6>
                                    </div>
                                </div>-->
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

</div>
<div class="modal fade" id="modal-asign-driver" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
    <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-title-new-item">{{ __('Assign Driver') }}</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="card bg-secondary shadow border-0">
                    <div class="card-body px-lg-5 py-lg-5">
                        <form id="form-assing-driver" method="GET" action="">
                            <div class="form-group{{ $errors->has('driver') ? ' has-danger' : '' }}">
                                <label class="form-control-label" for="drive">{{ __('Assign Driver') }}</label>
                                <select class="form-control select2" name="driver">
                                    <option disabled selected value> -- Select a Driver -- </option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver->id }}">{{$driver->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary my-4">{{ __('Save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
