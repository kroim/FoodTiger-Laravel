@extends('layouts.app', ['title' => __('Orders')])

@section('content')
    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    </div>
    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        @if(count($orders))
                        <form method="GET" action="{{ route('orders.index') }}">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h3 class="mb-0">{{ __('Orders') }}</h3>
                                </div>
                                <div class="col-4 text-right">
                                    <button id="show-hide-filters" class="btn btn-icon btn-1 btn-sm btn-outline-secondary" type="button">
                                        <span class="btn-inner--icon"><i id="button-filters" class="ni ni-bold-down"></i></span>
                                    </button>
                                </div>
                            </div>
                            <br/>
                            <div class="tab-content orders-filters">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="input-daterange datepicker row align-items-center">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-control-label">{{ __('Filter by Date From') }}</label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                                            </div>
                                                            <input name="fromDate" class="form-control" placeholder="{{ __('Date from') }}" type="text" <?php if(isset($_GET['fromDate'])){echo 'value="'.$_GET['fromDate'].'"';} ?> >
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-control-label">{{ __('To') }}</label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                                            </div>
                                                            <input name="toDate" class="form-control" placeholder="{{ __('Date to') }}" type="text"  <?php if(isset($_GET['toDate'])){echo 'value="'.$_GET['toDate'].'"';} ?>>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @hasrole('admin|driver')
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="form-control-label" for="restorant">{{ __('Filter by Restaurant') }}</label>
                                                    <select class="form-control select2" name="restorant_id">
                                                        <option disabled selected value> -- {{ __('Select an option') }} -- </option>
                                                        @foreach ($restorants as $restorant)
                                                            <option <?php if(isset($_GET['restorant_id'])&&$_GET['restorant_id'].""==$restorant->id.""){echo "selected";} ?> value="{{ $restorant->id }}">{{$restorant->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endhasrole
                                        @hasrole('admin|owner')
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-control-label" for="client">{{ __('Filter by Client') }}</label>

                                                <select class="form-control select2" id="blabla" name="client_id">
                                                    <option disabled selected value> -- {{ __('Select an option') }} -- </option>
                                                    @foreach ($clients as $client)
                                                        <option  <?php if(isset($_GET['client_id'])&&$_GET['client_id'].""==$client->id.""){echo "selected";} ?>  value="{{ $client->id }}">{{$client->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @endhasrole
                                        @hasrole('admin|owner')
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-control-label" for="driver">{{ __('Filter by Driver') }}</label>
                                                <select class="form-control select2" name="driver_id">
                                                    <option disabled selected value> -- {{ __('Select an option') }} -- </option>
                                                    @foreach ($drivers as $driver)
                                                        <option <?php if(isset($_GET['driver_id'])&&$_GET['driver_id'].""==$driver->id.""){echo "selected";} ?>   value="{{ $driver->id }}">{{$driver->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @endhasrole
                                    </div>

                                        <div class="col-md-6 offset-md-6">
                                            <div class="row">
                                                @if ($parameters)
                                                    <div class="col-md-4">
                                                        <a href="{{ route('orders.index') }}" class="btn btn-md btn-block">{{ __('Clear Filters') }}</a>
                                                    </div>
                                                    <div class="col-md-4">
                                                    <a href="{{Request::fullUrl()."&report=true" }}" class="btn btn-md btn-success btn-block">{{ __('Download report') }}</a>
                                                    </div>
                                                @else
                                                    <div class="col-md-8"></div>
                                                @endif

                                                <div class="col-md-4">
                                                    <button type="submit" class="btn btn-primary btn-md btn-block">{{ __('Filter') }}</button>
                                                </div>
                                        </div>
                                    </div>
                             </div>
                        </form>
                        @endif
                    </div>
                    <div class="col-12">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                    </div>
                    @if(count($orders))
                    <div class="table-responsive">
                        <table class="table align-items-center">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">{{ __('ID') }}</th>
                                    @hasrole('admin|driver')
                                        <th scope="col">{{ __('Restaurant') }}</th>
                                    @endhasrole
                                    <th class="table-web" scope="col">{{ __('Created') }}</th>
                                    <th class="table-web" scope="col">{{ __('Time Slot') }}</th>
                                    <th class="table-web" scope="col">{{ __('Method') }}</th>
                                    <th scope="col">{{ __('Last status') }}</th>
                                    @hasrole('admin|owner|driver')
                                        <th class="table-web" scope="col">{{ __('Client') }}</th>
                                    @endhasrole
                                    @role('admin')
                                        <th class="table-web" scope="col">{{ __('Address') }}</th>
                                    @endrole
                                    @role('owner')
                                        <th class="table-web" scope="col">{{ __('Items') }}</th>
                                    @endrole
                                    @hasrole('admin|owner')
                                        <th class="table-web" scope="col">{{ __('Driver') }}</th>
                                    @endhasrole
                                    <th class="table-web" scope="col">{{ __('Price') }}</th>
                                    <th class="table-web" scope="col">{{ __('Delivery') }}</th>
                                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('owner') || auth()->user()->hasRole('driver'))
                                        <th scope="col">{{ __('Actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>
                                    <!--<span class="text-primary order_id" name="order-id" style="cursor:pointer" value='{{ $order->id }}' data-toggle="modal" data-target="#modal-order-details">{{ $order->id }}</span>-->
                                    <a class="btn badge badge-success badge-pill" href="{{ route('orders.show',$order->id )}}">#{{ $order->id }}</a>
                                </td>
                                @hasrole('admin|driver')
                                <th scope="row">
                                    <div class="media align-items-center">
                                        <a class="avatar-custom mr-3">
                                            <img class="rounded" alt="..." src={{ $order->restorant->icon }}>
                                        </a>
                                        <div class="media-body">
                                            <span class="mb-0 text-sm">{{ $order->restorant->name }}</span>
                                        </div>
                                    </div>
                                </th>
                                @endhasrole

                                <td class="table-web">
                                    {{ $order->created_at->format('D M Y H:i') }}
                                </td>
                                <td class="table-web">
                                    {{ $order->time_formated }}
                                </td>
                                <td class="table-web">
                                    @if ($order->delivery_method==1)
                                        <span class="badge badge-primary badge-pill">{{ __('Delivery') }}</span>
                                    @else
                                        <span class="badge badge-success badge-pill">{{ __('Pickup') }}</span>
                                    @endif

                                </td>
                                <td>
                                    @if($order->status->pluck('id')->last() == "1")
                                        <span class="badge badge-primary badge-pill">{{ __($order->status->pluck('name')->last()) }}</span>
                                    @elseif($order->status->pluck('id')->last() == "2" || $order->status->pluck('id')->last() == "3")
                                        <span class="badge badge-success badge-pill">{{ __($order->status->pluck('name')->last()) }}</span>
                                    @elseif($order->status->pluck('id')->last() == "4")
                                        <span class="badge badge-default badge-pill">{{ __($order->status->pluck('name')->last()) }}</span>
                                    @elseif($order->status->pluck('id')->last() == "5")
                                        <span class="badge badge-warning badge-pill">{{ __($order->status->pluck('name')->last()) }}</span>
                                    @elseif($order->status->pluck('id')->last() == "6")
                                        <span class="badge badge-success badge-pill">{{ __($order->status->pluck('name')->last()) }}</span>
                                    @elseif($order->status->pluck('id')->last() == "7")
                                        <span class="badge badge-info badge-pill">{{ __($order->status->pluck('name')->last()) }}</span>
                                    @elseif($order->status->pluck('id')->last() == "8" || $order->status->pluck('id')->last() == "9")
                                        <span class="badge badge-danger badge-pill">{{ __($order->status->pluck('name')->last()) }}</span>
                                    @endif
                                </td>
                                @hasrole('admin|owner|driver')
                                <td class="table-web">
                                   {{ $order->client->name }}
                                </td>
                                @endhasrole
                                @role('admin')
                                    <td class="table-web">
                                        {{ $order->address?$order->address->address:"" }}
                                    </td>
                                @endrole
                                @role('owner')
                                    <td class="table-web">
                                        {{ count($order->items) }}
                                    </td>
                                @endrole
                                @hasrole('admin|owner')
                                    <td class="table-web">
                                        {{ !empty($order->driver->name) ? $order->driver->name : "" }}
                                    </td>
                                @endhasrole
                                <td class="table-web">
                                    @money( $order->order_price, env('CASHIER_CURRENCY','usd'),true)

                                </td>
                                <td class="table-web">
                                    @money( $order->delivery_price, env('CASHIER_CURRENCY','usd'),true)
                                </td>
                                @include('orders.partials.actions.table',['order' => $order ])
                            </tr>
                            </tbody>
                            @endforeach
                        </table>
                    </div>
                    @endif
                    <div class="card-footer py-4">
                        @if(count($orders))
                        <nav class="d-flex justify-content-end" aria-label="...">
                            {{ $orders->appends(Request::all())->links() }}
                        </nav>
                        @else
                            <h4>{{ __('You don`t have any orders') }} ...</h4>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth')
        @include('orders.partials.modals')
    </div>
@endsection

