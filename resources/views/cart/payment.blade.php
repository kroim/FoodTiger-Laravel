<div class="card card-profile shadow mt--300">
    <div class="px-4">
      <div class="mt-5">
        <h3>{{ __('Checkout') }}<span class="font-weight-light"></span></h3>
      </div>
      <div  class="border-top">
        <!-- Price overview -->
        <div id="totalPrices" v-cloak>
            <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <!--<h6 class="card-title text-uppercase text-muted mb-0">Sales Volume ( 30 days )</h6>
                            <span class="h5 font-weight-bold mb-0">SD</span>-->
                            <span v-if="totalPrice==0">{{ __('Cart is empty') }}!</span>
                            <span v-if="totalPrice"><strong>{{ __('Subtotal') }}:</strong></span>
                            <span v-if="totalPrice" class="ammount"><strong>@{{ totalPriceFormat }}</strong></span>
                            <span v-if="totalPrice&&delivery"><br /><strong>{{ __('Delivery') }}:</strong></span>
                            <span v-if="totalPrice&&delivery" class="ammount"><strong>@money(config('global.delivery'), env('CASHIER_CURRENCY','usd'),true)</strong></span><br /><br />
                            <span v-if="totalPrice"><strong>{{ __('TOTAL') }}:</strong></span>
                            <span v-if="totalPrice" class="ammount"><strong>@{{ withDeliveryFormat   }}</strong></span>
                            <input v-if="totalPrice" type="hidden" id="tootalPricewithDeliveryRaw" :value="withDelivery" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End price overview -->

        <!-- Payment  Methods -->
        <div class="cards">
          <div class="card-body">
              <div class="row">
                  <div class="col">

                    <!-- COD -->
                    @if (!env('HIDE_COD',false))
                        <div class="custom-control custom-radio mb-3">
                            <input name="paymentType" class="custom-control-input" id="cashOnDelivery" type="radio" value="cod" {{ env('DEFAULT_PAYMENT','cod')=="cod"?"checked":""}}>
                            <label class="custom-control-label" for="cashOnDelivery"><span class="delTime">{{ __('Cash on delivery') }}</span> <span class="picTime">{{ __('Cash on pickup') }}</span></label>
                        </div>
                    @endif
                    
                    <!-- Errors on Stripe -->
                    @if (session('error'))
                        <div role="alert" class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- STIPE CART -->
                    @if (env('STRIPE_KEY',false)&&env('ENABLE_STRIPE',false))
                        <div class="custom-control custom-radio mb-3">
                            <input name="paymentType" class="custom-control-input" id="paymentStripe" type="radio" value="stripe" {{ env('DEFAULT_PAYMENT','cod')=="stripe"?"checked":""}}>
                            <label class="custom-control-label" for="paymentStripe">{{ __('Pay with card') }}</label>
                        </div>
                    @endif

                  </div>
              </div>
          </div>
        </div>
        <!-- END Payment -->


        <!-- Payment Actions -->

        <!-- COD -->
        @include('cart.payments.cod')
        
        </form>
        
         <!-- Stripe -->
        @include('cart.payments.stripe')

        
        

        

        <!-- END Payment Actions -->
    


      </div>
      <br />
      <br />
    </div>
  </div>
  <br />

  @if(env('IS_DEMO', false) && env('ENABLE_STRIPE', false))
    @include('cart.democards')    
  @endif
