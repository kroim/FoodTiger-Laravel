
var cartContent=null;
var cartTotal=null;

/**
 * getCartContentAndTotalPrice
 * This functions connect to laravel to get the current cart items and total price
 * Saves the values in vue
 */
function getCartContentAndTotalPrice(){
   axios.get('/cart-getContent').then(function (response) {
     cartContent.items=response.data.data;
     cartTotal.totalPrice=response.data.total;
     cartTotal.totalPriceFormat=response.data.totalFormat;
     cartTotal.delivery=true;
     cartTotal.withDelivery=response.data.withDelivery
     cartTotal.withDeliveryFormat=response.data.withDeliveryFormat;
     //complete order total price in submit button
     total.totalPrice=response.data.total;
     //cartTotal.minimalOrder=response.data.minimalOrder;
   })
   .catch(function (error) {
     console.log(error);
   });
 };

/**
 * Removes product from cart, and calls getCartConent
 * @param {Number} product_id
 */
function removeProductIfFromCart(product_id){
    axios.post('/cart-remove', {id:product_id}).then(function (response) {
      getCartContentAndTotalPrice();
    }).catch(function (error) {
      console.log(error);
    });
 }

 /**
 * Update the product quantity, and calls getCartConent
 * @param {Number} product_id
 */
function incCart(product_id){
  axios.get('/cartinc/'+product_id).then(function (response) {
    getCartContentAndTotalPrice();
  }).catch(function (error) {
    console.log(error);
  });
}


function decCart(product_id){
  axios.get('/cartdec/'+product_id).then(function (response) {
    getCartContentAndTotalPrice();
  }).catch(function (error) {
    console.log(error);
  });
}

//GET PAGES FOR FOOTER
function getPages(){
    axios.get('/footer-pages').then(function (response) {
      footerPages.pages=response.data.data;
    })
    .catch(function (error) {
      console.log(error);
    });

};

  function deliveryTypeSwitcher(){
    $('.picTime').hide();
    $('input:radio[name="deliveryType"]').change(function() {
      var mod=$(this).val();
      console.log("Change mod to "+mod);

      $('.delTime').hide();
      $('.picTime').hide();

     

      if(mod=="pickup"){
        console.log(cartTotal.totalPriceFormat);
          cartTotal.delivery=false;
          cartTotal.withDelivery=cartTotal.totalPrice;
          cartTotal.withDeliveryFormat=cartTotal.totalPriceFormat;
          //cartTotal.totalPrice=132;
          cartTotal.totalPriceFormat=cartTotal.totalPriceFormat+" ";
          $('.picTime').show();
          $('#addressBox').hide();

          
          
          
          
      }

      if(mod=="delivery"){
          $('.delTime').show();
          $('#addressBox').show();
          getCartContentAndTotalPrice();
      }
    })
  }

  function paymentTypeSwitcher(){
    $('input:radio[name="paymentType"]').change(
            
      function(){
          //HIDE ALL
          $('#totalSubmitCOD').hide()
          $('#totalSubmitStripe').hide()
          $('#stripe-payment-form').hide()
         
          if($(this).val()=="cod"){
              //SHOW COD
              $('#totalSubmitCOD').show();
          }else if($(this).val()=="stripe"){
              //SHOW STRIPE
              $('#totalSubmitStripe').show();
              $('#stripe-payment-form').show()
          }
      });
  }

window.onload = function () {

  //VUE CART
  cartContent = new Vue({
    el: '#cartList',
    data: {
      items: [],
    },
    methods: {
      remove: function (product_id) {
        removeProductIfFromCart(product_id);
      },
      incQuantity: function (product_id){
        incCart(product_id)
      },
      decQuantity: function (product_id){
        decCart(product_id)
      },
    }
  })

  //GET PAGES FOR FOOTER
  getPages();

  //Payment Method switcher
  paymentTypeSwitcher();

  //Delivery type switcher
  deliveryTypeSwitcher();

  //VUE FOOTER PAGES
  footerPages = new Vue({
      el: '#footer-pages',
      data: {
        pages: []
      }
  })

  //VUE COMPLETE ORDER TOTAL PRICE
  total = new Vue({
    el: '#totalSubmit',
    data: {
      totalPrice:0
    }
  })


  //VUE TOTAL
  cartTotal= new Vue({
    el: '#totalPrices',
    data: {
      totalPrice:0,
      minimalOrder:0,
      totalPriceFormat:""
    }
  })

  //Call to get the total price and items
  getCartContentAndTotalPrice();

  var addToCart1 =  new Vue({
    el:'#addToCart1',
    methods: {
        addToCartAct() {

            axios.post('/cart-add', {
                id: $('#modalID').text(),
                quantity: $('#quantity').val()


              })
              .then(function (response) {
                  if(response.data.status){
                    $('#productModal').modal('hide');
                    getCartContentAndTotalPrice();

                    //$('#miniCart').addClass( "open" );
                    openNav();
                  }else{
                    $('#productModal').modal('hide');
                    notify(response.data.errMsg);
                  }
              })
              .catch(function (error) {
                console.log(error);
              });
        },
    },
  });

  function notify(text){
    $.notify.addStyle('custom', {
        html: "<div><strong>Warning! </strong><span data-notify-text /></div>",
        classes: {
            base: {
                "position": "relative",
                "margin-bottom": "1rem",
                "padding": "1rem 1.5rem",
                "border": "1px solid transparent",
                "border-radius": ".375rem",

                "color": "#fff",
                "border-color": "#fc7c5f",
                "background-color": "#fc7c5f",
            },
            success: {
                "color": "#fff",
                "border-color": "#fc7c5f",
                "background-color": "#fc7c5f",
            }
        }
        });

        $.notify(text,{
            position: "bottom right",
            style: 'custom',
            className: 'success',
            autoHideDelay: 5000,
        }
    );
  }
}
