@extends('layouts.front', ['class' => ''])

@section('content')
    @include('layouts.headers.search')
   <section class="section" id="main-content">
        <div class="container mt--100">
            <h1>{{ $title }}</h1>
            <br />
            <div class="row">
                @forelse ($restorants as $restorant)
                    <?php $link=route('vendor',['alias'=>$restorant->alias]); ?>
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="strip">
                            <figure>
                            <a href="{{ $link }}"><img src="{{ $restorant->logom }}" data-src="{{ config('global.restorant_details_image') }}" class="img-fluid lazy" alt=""></a>
                            </figure>
                            <span class="res_title"><b><a href="{{ $link }}">{{ $restorant->name}}</a></b></span><br />
                            <span class="res_description">{{ $restorant->description}}</span><br />
                            <span class="res_mimimum">{{ __('Minimum order') }}: @money($restorant->minimum, env('CASHIER_CURRENCY','usd'),true)</span>

                        </div>
                    </div>
                    @empty
                    <div class="col-md-12">
                    <p class="text-muted mb-0">{{ __('Hmmm... Nothing found!')}}</p>
                    </div>

                    @endforelse
            </div>
            @if(config('global.playstore') || config('global.appstore'))
            <hr>
            <div class="row row-grid align-items-center mb-5">
                <div class="col-lg-6">
                    <!--<h3 class="text-primary mb-2">{{ __('Download the food you love') }}</h3>
                    <h4 class="mb-0 font-weight-light">{{ __('It`s all at your fingertips - the restaurants you love') }}. {{ __ ('Find the right food to suit your mood, and make the first bite last') }}. {{ __('Go ahead, download us') }}.</h4>-->
                    <h3 class="text-primary mb-2">{{ __(config('global.mobile_info_title')) }}</h3>
                    <h4 class="mb-0 font-weight-light">{{ __(config('global.mobile_info_subtitle')) }}</h4>
                </div>
                <div class="col-lg-6 text-lg-center btn-wrapper">
                    <div class="row">
                        @if(config('global.playstore'))
                        <div class="col-6">
                            <a href="{{config('global.playstore')}}" target="_blank"><img class="img-fluid" src="/default/playstore.png" alt="..."/></a>
                        </div>
                        @endif
                        @if(config('global.appstore'))
                        <div class="col-6">
                            <a href="{{config('global.appstore')}}" target="_blank"><img class="img-fluid" src="/default/appstore.png" alt="..."/></a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
@endsection
