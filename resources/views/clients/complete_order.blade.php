<h3>{{ __('Finalize order')}}</h3>
<br />
<form id="order-form" role="form" method="post" action="{{route('order.store')}}" autocomplete="off" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-md-10">
            <div class="form-group{{ $errors->has('addressID') ? ' has-danger' : '' }}">
            @if(count(auth()->user()->addresses))
                <label class="form-control-label" for="addressID">{{ __('Address')}}</label>
                    <select name="addressID" id="addressID" class="form-control{{ $errors->has('addressID') ? ' is-invalid' : '' }}" required>
                        @foreach (auth()->user()->addresses as $address)
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
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label class="text-white" for="exampleFormControlSelect1">{{ __('Or add new') }}</label>
                <button type="button" data-toggle="modal" data-target="#modal-order-new-address"  class="btn btn-primary">{{ __('Add new') }}</button>
            </div>
        </div>
    </div>
    @if(count(auth()->user()->addresses))
        <!--<div class="form-group">
            <div id="address_map" class="form-control"></div>
        </div>-->
    @endif
    <div class="form-group{{ $errors->has('phone') ? ' has-danger' : '' }}">
        <label class="form-control-label" for="phone">{{ __('Phone') }}</label>
        <input name="phone" id="phone" type="text" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" placeholder="{{ __( 'Your phone here' ) }} ..." value="{{auth()->user()->phone}}" required>
        @if ($errors->has('phone'))
            <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('phone') }}</strong>
            </span>
        @endif
    </div>
    <div class="form-group{{ $errors->has('comment') ? ' has-danger' : '' }}">
        <label class="form-control-label" for="comment">{{ __('Comment') }}</label>
        <textarea name="comment" id="comment" class="form-control{{ $errors->has('comment') ? ' is-invalid' : '' }}" placeholder="{{ __( 'Your comment here' ) }} ..." required></textarea>
        @if ($errors->has('comment'))
            <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('comment') }}</strong>
            </span>
        @endif
    </div>

    @if(env('IS_DEMO', false) && env('ENABLE_STRIPE', false))
    <br/>
    <div class="form-group">
        <label class="form-control-label" for="comment">{{ __('Demo Stripe Credentials') }}</label>
        <div class="card card-stats mb-4 mb-xl-0">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="table-responsive">
                            <table class="table align-items-center">
                                <thead>
                                    <tr>
                                        <th scope="col" class="sort" data-sort="name">NUMBER</th>
                                        <th scope="col" class="sort" data-sort="budget">BRAND</th>
                                        <th scope="col" class="sort" data-sort="status">CVC</th>
                                        <th scope="col" class="sort" data-sort="completion">DATE</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    <tr>
                                        <td>4242 4242 4242 4242</td>
                                        <td>Visa</td>
                                        <td>Any 3 digits</td>
                                        <td>Any future date</td>
                                    </tr>
                                    <tr>
                                        <td>5555 5555 5555 4444</td>
                                        <td>Mastercard</td>
                                        <td>Any 3 digits</td>
                                        <td>Any future date</td>
                                    </tr>
                                    <tr>
                                        <td>3782 822463 10005</td>
                                        <td>American Express</td>
                                        <td>Any 4 digits</td>
                                        <td>Any future date</td>
                                    </tr>
                                    <tr>
                                        <td>6011 1111 1111 1117</td>
                                        <td>Discover</td>
                                        <td>Any 3 digits</td>
                                        <td>Any future date</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif


    <div class="form-group">
        <br/>

        <h4>{{ __('Payment method') }}</h4>
        <br/>
        @if (!env('HIDE_COD',false))
            <div class="custom-control custom-radio mb-3">
                <input name="paymentType" class="custom-control-input" id="cashOnDelivery" type="radio" value="cod" {{ env('DEFAULT_PAYMENT','cod')=="cod"?"checked":""}}>
                <label class="custom-control-label" for="cashOnDelivery">{{ __('Cash on delivery') }}</label>
            </div>
        @endif
        @if (env('STRIPE_KEY',false)&&env('ENABLE_STRIPE',false))
            <div class="custom-control custom-radio mb-3">
                <input name="paymentType" class="custom-control-input" id="paymentStripe" type="radio" value="stripe" {{ env('DEFAULT_PAYMENT','cod')=="stripe"?"checked":""}}>
                <label class="custom-control-label" for="paymentStripe">{{ __('Pay with card') }}</label>
            </div>
        @endif
        @if (env('STRIPE_KEY',false)&&env('ENABLE_STRIPE_IDEAL',false))
            <div class="custom-control custom-radio mb-3">
                <input name="paymentType" class="custom-control-input" id="paymentIdeal" type="radio" value="ideal" {{ env('DEFAULT_PAYMENT','cod')=="ideal"?"checked":""}}>
                <label class="custom-control-label" for="paymentIdeal">{{ __('Pay via iDeal') }}</label>
            </div>
        @endif







    @if(count(auth()->user()->addresses)&&!env('HIDE_COD',false))
        <div class="text-center" id="totalSubmitCOD"  style="display: {{ env('DEFAULT_PAYMENT','cod')=="cod"&&!env('HIDE_COD',false)?"block":"none"}};" >
            <button v-if="totalPrice" type="submit" class="btn btn-success mt-4">{{ __('Place order') }}</button>
        </div>
    @endif
</form>
@if (session('error'))
    <div role="alert" class="alert alert-danger">{{ session('error') }}</div>
@endif


@if (env('STRIPE_KEY',false)&&env('ENABLE_STRIPE',false))
<form action="/charge" method="post" id="stripe-payment-form" style="display: {{ env('DEFAULT_PAYMENT','cod')=="stripe"?"block":"none"}};"   >

    <div style="width: 30em" class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
        <input name="name" id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" placeholder="{{ __( 'Name on card' ) }}" value="{{auth()->user()->name}}" required>
        @if ($errors->has('name'))
            <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('name') }}</strong>
            </span>
        @endif
    </div>

    <div class="form">
        <div  style="width: 30em" #stripecardelement  id="card-element">

        <!-- A Stripe Element will be inserted here. -->
      </div>

      <!-- Used to display form errors. -->
      <br />
      <div class="" id="card-errors" role="alert">

      </div>
    </div>
    @if(count(auth()->user()->addresses))
        <div class="text-center" id="totalSubmitStripe">
            <button v-if="totalPrice" type="submit" class="btn btn-success mt-4">{{ __('Place order') }}</button>
        </div>
    @endif

  </form>
@endif


@if (env('STRIPE_KEY',false)&&env('ENABLE_STRIPE_IDEAL',true))
<form id="ideal-payment-form" style="display: {{ env('DEFAULT_PAYMENT','cod')=="ideal"?"block":"none"}};">

    <div style="width: 30em" class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
        <input name="name" id="name-ideal" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" placeholder="{{ __( 'Name on card' ) }}" value="{{auth()->user()->name}}" required>
        @if ($errors->has('name'))
            <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('name') }}</strong>
            </span>
        @endif
    </div>

    <div class="form">
      <label for="ideal-bank-element">
        iDEAL Bank
      </label>
      <div  style="width: 30em" id="ideal-bank-element">
        <!-- A Stripe Element will be inserted here. -->
      </div>
    </div>

    @if(count(auth()->user()->addresses))
        <div class="text-center" id="totalSubmitStripe">
            <button v-if="totalPrice" type="submit" class="btn btn-success mt-4">{{ __('Submit payment') }}</button>
        </div>
    @endif

    <!-- Used to display form errors. -->
    <div id="error-message-ideal" role="alert"></div>
  </form>
@endif

  @section('js')
    <script async defer
        src= "https://maps.googleapis.com/maps/api/js?key=<?php echo env('GOOGLE_MAPS_API_KEY',''); ?>">
    </script>
    <script src="{{ asset('custom') }}/js/new_address.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript">
     window.onload?window.onload():console.log("No other windowonload foound");
     window.onload = function () {

        $('input:radio[name="paymentType"]').change(

            function(){
                //HIDE ALL
                $('#totalSubmitCOD').hide()
                $('#totalSubmitStripe').hide()
                $('#stripe-payment-form').hide()
                $('#totalSubmitIdeal').hide();
                $('#ideal-payment-form').hide()

                if($(this).val()=="cod"){
                    //SHOW COD
                    $('#totalSubmitCOD').show();
                }else if($(this).val()=="stripe"){
                    //SHOW STRIPE
                    $('#totalSubmitStripe').show();
                    $('#stripe-payment-form').show()
                }else if($(this).val()=="ideal"){
                    //SHOW ideal
                    $('#totalSubmitIdeal').show();
                    $('#ideal-payment-form').show()
                }
            });

        // Create a Stripe client.
        var stripe = Stripe("{{ env('STRIPE_KEY',"") }}");

        // Create an instance of Elements.
        var elements = stripe.elements();

        // Custom styling can be passed to options when creating an Element.
        // (Note that this demo uses a wider set of styles than the guide below.)
        var style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        var options = {
            // Custom styling can be passed to options when creating an Element.
            style: {
                base: {
                // Add your base input styles here. For example:
                fontSize: '16px',
                color: '#32325d',
                padding: '2px 2px 4px 2px',
                },
            }
            }

        // Create an instance of the idealBank Element.
        //var idealBank = elements.create('idealBank', options);

        // Add an instance of the idealBank Element into
        // the `ideal-bank-element` <div>.
        //idealBank.mount('#ideal-bank-element');

        // Create an instance of the card Element.
        var card = elements.create('card', {style: style});

        // Add an instance of the card Element into the `card-element` <div>.
        card.mount('#card-element');

        // Handle real-time validation errors from the card Element.
        card.addEventListener('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        const cardHolderName = document.getElementById('name');

        // Handle form submission  - for card.
        var form = document.getElementById('stripe-payment-form');
        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            const { paymentMethod, error } = await stripe.createPaymentMethod(
                    'card', card, {
                        billing_details: { name: cardHolderName.value }
                    }
                );

            if (error) {
                // Display "error.message" to the user...
                alert(error.message);
            } else {
                stripePaymentMethodHandler(paymentMethod.id);
            }

        });

        // Submit the form with the payment ID.
        function stripePaymentMethodHandler(payment_id) {
            // Insert the token ID into the form so it gets submitted to the server
            var form = document.getElementById('order-form');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripePaymentId');
            hiddenInput.setAttribute('value', payment_id);
            form.appendChild(hiddenInput);

            // Submit the form
            form.submit();
        }


        // Create a source or display an error when the form is submitted.
        //var formiDeal = document.getElementById('ideal-payment-form');

        /*formiDeal.addEventListener('submit', function(event) {
            event.preventDefault();


            var sourceData = {
                type: 'ideal',
                amount: parseInt($("#tootalPricewithDeliveryRaw").val()*100),
                currency: '{{ env('CASHIER_CURRENCY','usd') }}',
                owner: {
                    name: $("#name-ideal").val(),
                },
                // Specify the URL to which the customer should be redirected
                // after paying.
                redirect: {
                    return_url: '{{ env('APP_URL') }}'+'/order/ideal/'+$("#addressID").val()+'/'+$("#phone").val()+'/'+$("#comment").val()
                },
            };





            // Call `stripe.createSource` with the idealBank Element and
            // additional options.
            stripe.createSource(idealBank, sourceData).then(function(result) {
                if (result.error) {
                    // Inform the customer that there was an error.
                    $('#error-message-ideal').html(result.error.message)
                } else {
                // Redirect the customer to the authorization URL.
                stripeSourceHandler(result.source);
                }
            });
        });*/

        function stripeSourceHandler(source) {
            // Redirect the customer to the authorization URL.
            document.location.href = source.redirect.url;
        }

     }
</script>
@endsection

