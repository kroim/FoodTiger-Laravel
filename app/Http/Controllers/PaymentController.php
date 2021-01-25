<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//require __DIR__ . '/../bootstrap.php';
/*use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;*/
use App\User;
use App\Payment;
use Cart;
use Akaunting\Money\Currency;
use Akaunting\Money\Money;

class PaymentController extends Controller
{
    function __construct()
    {
       // $this->middleware('auth');
    }

    public function view(Request $request)
    {
        $total = Money(Cart::getSubTotal(), env('CASHIER_CURRENCY','usd'),true)->format();

        //Clear cart
        Cart::clear();

        return view('payment.payment',[
            'total' => $total
        ]);
    }

    public function payment(Request $request)
    {
        try {
            $payment_stripe = auth()->user()->charge(100, $request->payment_method);

            $name = $payment_stripe->charges->data[0]->billing_details->name;
            $country = $payment_stripe->charges->data[0]->payment_method_details->card->country;

            $payment = new Payment;
            $payment->user_id = auth()->user()->id;
            $payment->name = $name != null ? $name : "";
            $payment->stripe_id = $payment_stripe->customer != null ? $payment_stripe->customer : null;
            $payment->amount = $payment_stripe->amount != null ? $payment_stripe->amount : 0.0;
            $payment->currency = $payment_stripe->currency != null ? $payment_stripe->currency : "";
            $payment->country = $country != null ? $country : "";
            $payment->provider = "stripe";

            $payment->save();

            return response()->json([
                'status' => true,
                'success_url' => redirect()->intended('/')->getTargetUrl(),
                'msg' => 'Payment submitted succesfully!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false
            ]);
        }
    }

    public function viewPaypal(Request $request)
    {
        return view('payment.paypal');
    }

    public function createPaymentPayPal(Request $request)
    {
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AVJBLrcQsQPGjpOzpNnEsT0GYDQ6I_Czub6hk_7nVhL6uB2oVYOErZNS25jzqhLCgy19nn5DmoZBvZrH',     // ClientID
                'ELj_lakQgGRGPJo7gHV35XmTu_OwaN3LbbvXz-xMF5AvgdBxda_i9peT5T-NNuXKP1dGgfoaDoXPIyNH'     // ClientSecret
            )
        );

        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $item1 = new Item();
        $item1->setName('Ground Coffee 40 oz')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setSku("123123") // Similar to `item_number` in Classic API
            ->setPrice(7.5);
        $item2 = new Item();
        $item2->setName('Granola bars')
            ->setCurrency('USD')
            ->setQuantity(5)
            ->setSku("321321") // Similar to `item_number` in Classic API
            ->setPrice(2);

        $itemList = new ItemList();
        $itemList->setItems(array($item1, $item2));


        $details = new Details();
        $details->setShipping(1.2)
            ->setTax(1.3)
            ->setSubtotal(17.50);

        $amount = new Amount();
        $amount->setCurrency("USD")
            ->setTotal(20)
            ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Payment description")
            ->setInvoiceNumber(uniqid());

        $baseUrl = getBaseUrl();
        $redirectUrls = new RedirectUrls();
        /*$redirectUrls->setReturnUrl("$baseUrl/ExecutePayment.php?success=true")
            ->setCancelUrl("$baseUrl/ExecutePayment.php?success=false");*/

        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        $request = clone $payment;

        try {
            $payment->create($apiContext);
        } catch (Exception $ex) {

            esultPrinter::printError("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", null, $request, $ex);
            exit(1);
        }

        $approvalUrl = $payment->getApprovalLink();

        ResultPrinter::printResult("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", "<a href='$approvalUrl' >$approvalUrl</a>", $request, $payment);

        return $payment;
    }

    public function executePaymentPayPal()
    {

    }
}
