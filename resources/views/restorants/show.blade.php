@extends('layouts.front', ['class' => ''])

@section('content')
@include('restorants.partials.modals')
    <script>
        var items=[];
        function setCurrentItem(id){
            $('#modalTitle').text(items[id].name);
            $('#modalName').text(items[id].name);
            $('#modalPrice').html(items[id].price);
            $('#modalID').text(items[id].id);
            $("#modalImg").attr("src",items[id].image);
            $('#modalDescription').html(items[id].description);
            $('#productModal').modal('show');
        }
        <?php
        $items=[];
        foreach ($restorant->categories as $key => $category) {

            foreach ($category->items as $key => $item) {
                $theArray=array(
                    'name'=>$item->name,
                    'id'=>$item->id,
                    'price'=>@money($item->price, env('CASHIER_CURRENCY','usd'),true)."",
                    'image'=>$item->logom,
                    'description'=>$item->description
                );
                echo "items[".$item->id."]=".json_encode($theArray).";";
            }
        }
        ?>
    </script>
    <section class="section-profile-cover section-shaped grayscale-05 d-none d-md-none d-lg-block d-lx-block">
      <!-- Circles background -->
      <img class="bg-image" src="{{ $restorant->coverm }}" style="width: 100%;">
      <!-- SVG separator -->
      <div class="separator separator-bottom separator-skew">
        <svg x="0" y="0" viewBox="0 0 2560 100" preserveAspectRatio="none" version="1.1" xmlns="http://www.w3.org/2000/svg">
          <polygon class="fill-white" points="2560 0 2560 100 0 100"></polygon>
        </svg>
      </div>
    </section>
    <section class="section section-lg pt-lg-0 mt--9 d-none d-md-none d-lg-block d-lx-block">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="title white"  <?php if($restorant->description || $openingTime && $closingTime){echo 'style="border-bottom: 1px solid #f2f2f2;"';} ?> >
                        <h1 class="display-3 text-white">{{ $restorant->name }}</h1>
                        <p class="display-4" style="margin-top: 120px">{{ $restorant->description }}</p>
                        @if(!empty($openingTime) && !empty($closingTime))
                            <p>{{ __('Today working hours') }}: <span><strong>{{ $openingTime }}</strong></span> - <span><strong>{{ $closingTime }}</strong></span></p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section section-lg d-md-block d-lg-none d-lx-none" style="padding-bottom: 0px">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="title">
                        <h1 class="display-3 text">{{ $restorant->name }}</h1>
                        <p class="display-4 text">{{ $restorant->description }}</p>
                        @if(!empty($openingTime) && !empty($closingTime))
                            <p>{{ __('Today working hours') }}: <span><strong>{{ $openingTime }}</strong></span> - <span><strong>{{ $closingTime }}</strong></span></p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section section-lg pt-lg-0" id="restaurant-content" style="padding-top: 0px">
        <div class="container container-restorant">
            @if(!$restorant->categories->isEmpty())
            @foreach ( $restorant->categories as $category)
                @if(!$category->items->isEmpty())
                <div class="">
                    <h1>{{ $category->name }}</h1><br />
                </div>
                @endif
                <div class="row">
                    @foreach ($category->items as $item)
                        @if($item->available == 1)
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="strip">
                                <figure>
                                    <a onClick="setCurrentItem({{ $item->id }})" href="javascript:void(0)"><img src="{{ $item->logom }}" data-src="{{ config('global.restorant_details_image') }}" class="img-fluid lazy" alt=""></a>
                                </figure>
                                <span class="res_title"><b><a onClick="setCurrentItem({{ $item->id }})" href="javascript:void(0)">{{ $item->name }}</a></b></span><br />
                                <span class="res_description">{{ $item->short_description}}</span><br />
                                <span class="res_mimimum">@money($item->price, env('CASHIER_CURRENCY','usd'),true)</span>

                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
            @else
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                        <p class="text-muted mb-0">{{ __('Hmmm... Nothing found!')}}</p>
                        <br/><br/><br/>
                        <div class="text-center" style="opacity: 0.2;">
                            <img src="https://www.jing.fm/clipimg/full/256-2560623_juice-clipart-pizza-box-pizza-box.png" width="200" height="200"></img>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </section>
@endsection
