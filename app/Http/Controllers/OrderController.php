<?php

namespace App\Http\Controllers;
use App\Order;
use App\Status;
use App\Restorant;
use App\User;
use App\Address;
use Illuminate\Http\Request;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\DB;
use Cart;
use App\Notifications\OrderNotification;
use Carbon\Carbon;
use Akaunting\Money\Currency;
use Akaunting\Money\Money;

use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;

use Laravel\Cashier\Exceptions\PaymentActionRequired;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $restorants = Restorant::where(['active'=>1])->get();
        $drivers = User::role('driver')->where(['active'=>1])->get();
        $clients = User::role('client')->where(['active'=>1])->get();

        $orders = Order::orderBy('created_at','desc');

        //Get client's orders
        if(auth()->user()->hasRole('client')){
            $orders = $orders->where(['client_id'=>auth()->user()->id]);
        ////Get driver's orders
        }else if(auth()->user()->hasRole('driver')){
            $orders = $orders->where(['driver_id'=>auth()->user()->id]);
        //Get owner's restorant orders
        }else if(auth()->user()->hasRole('owner')){
            $orders = $orders->where(['restorant_id'=>auth()->user()->restorant->id]);
        }

        //FILTER BT RESTORANT
        if(isset($_GET['restorant_id'])){
            $orders =$orders->where(['restorant_id'=>$_GET['restorant_id']]);
        }
        //If restorant owner, get his restorant orders only
        if(auth()->user()->hasRole('owner')){
            //Current restorant id
            $restorant_id = auth()->user()->restorant->id;
            $orders =$orders->where(['restorant_id'=>$restorant_id]);
        }

        //BY CLIENT
        if(isset($_GET['client_id'])){
            $orders =$orders->where(['client_id'=>$_GET['client_id']]);
        }

        //BY DRIVER
        if(isset($_GET['driver_id'])){
            $orders =$orders->where(['driver_id'=>$_GET['driver_id']]);
        }

        //BY DATE FROM
        if(isset($_GET['fromDate'])&&strlen($_GET['fromDate'])>3){
            //$start = Carbon::parse($_GET['fromDate']);
            $orders =$orders->whereDate('created_at','>=',$_GET['fromDate']);
        }

        //BY DATE TO
        if(isset($_GET['toDate'])&&strlen($_GET['toDate'])>3){
            //$end = Carbon::parse($_GET['toDate']);
            $orders =$orders->whereDate('created_at','<=',$_GET['toDate']);
        }

        //With downloaod
        if(isset($_GET['report'])){
            $items=array();
            foreach ($orders->get() as $key => $order) {
                $item=array(
                    "order_id"=>$order->id,
                    "restaurant_name"=>$order->restorant->name,
                    "restaurant_id"=>$order->restorant_id,
                    "created"=>$order->created_at,
                    "last_status"=>$order->status->pluck('alias')->last(),
                    "client_name"=>$order->client->name,
                    "client_id"=>$order->client_id,
                    "address"=>$order->address->address,
                    "address_id"=>$order->address_id,
                    "driver_name"=>$order->driver?$order->driver->name:"",
                    "driver_id"=>$order->driver_id,
                    "order_value"=>$order->order_price,
                    "order_delivery"=>$order->delivery_price,
                    "order_total"=>$order->delivery_price+$order->order_price,
                    'payment_method'=>$order->payment_method,
                    'srtipe_payment_id'=>$order->srtipe_payment_id
                  );
                array_push($items,$item);
            }

            return Excel::download(new OrdersExport($items), 'orders_'.time().'.xlsx');
        }

        $orders = $orders->paginate(10);



        return view('orders.index',['orders' => $orders,'restorants'=>$restorants,'drivers'=>$drivers,'clients'=>$clients,'parameters'=>count($_GET)!=0 ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $restorant_id=null;
        foreach (Cart::getContent() as $key => $item) {
            $restorant_id=$item->attributes->restorant_id;
        }
        $orderPrice=Cart::getSubTotal();

        //Check if deliveryType exeist
        if($request->exists('deliveryType')){
            $isDelivery=$request->deliveryType=="delivery";
        }else{
            //Defauls is delivery
            $isDelivery=true;
        }






        //Stripe payment
        if($request->paymentType=="stripe"){
            //Make the payment

            $total_price=(int)(($orderPrice)*100);
            if($isDelivery){
                $total_price=(int)(($orderPrice+config('global.delivery'))*100);
            }

            try {
                $payment_stripe = auth()->user()->charge($total_price, $request->stripePaymentId);
            } catch (PaymentActionRequired $e) {
                return redirect()->route('cart.checkout')->withError('The payment attempt failed because additional action is required before it can be completed.')->withInput();
            }
        }

        $restorant_fee = Restorant::select('fee', 'static_fee')->where(['id'=>$restorant_id])->get()->first();
        //Commision fee
        //$restorant_fee = Restorant::select('fee')->where(['id'=>$restorant_id])->value('fee');
        $order_fee = ($restorant_fee->fee / 100) * $orderPrice;

        //Create order
        $order = new Order;
        if($isDelivery){
            $order->address_id = $request->addressID;
        }

        $order->restorant_id = $restorant_id;
        $order->client_id = auth()->user()->id;
        $order->delivery_price = $isDelivery?config('global.delivery'):0;
        $order->order_price = $orderPrice;
        $order->comment = $request->comment ? strip_tags($request->comment."") : "";
        $order->payment_method = $request->paymentType;
        $order->srtipe_payment_id = $request->paymentType=="stripe"?$payment_stripe->id:null;
        $order->payment_status = $request->paymentType=="stripe"?'paid':'unpaid';
        $order->fee = $restorant_fee->fee;
        $order->fee_value = $order_fee;
        $order->static_fee = $restorant_fee->static_fee;
        $order->delivery_method=$isDelivery?1:2;  //1- delivery 2 - pickup
        $order->delivery_pickup_interval=$request->timeslot;
        $order->save();


        //Create status
        $status = Status::find(1);
        $order->status()->attach($status->id,['user_id'=>auth()->user()->id,'comment'=>""]);

        //If approve directly
        if(config('app.order_approve_directly')){
            $status = Status::find(2);
            $order->status()->attach($status->id,['user_id'=>1,'comment'=>__('Automatically apprved by admin')]);

             //Notify Owner
            //Find owner
            $restorant = Restorant::findOrFail($restorant_id);
            $restorant->user->notify((new OrderNotification($order))->locale(strtolower(env('APP_LOCALE','EN'))));

        }

        //TODO - Create items
        foreach (Cart::getContent() as $key => $item) {
            $order->items()->attach($item->id,['qty'=>$item->quantity]);
        }

        //Clear cart
        if($request['pay_methods'] != "payment"){
            Cart::clear();
        }

       return redirect()->route('orders.index')->withStatus(__('Order created.'));

    }

    public function orderLocationAPI(Order $order)
    {
        if($order->status->pluck('alias')->last() == "picked_up"){
            return response()->json(
                array(
                    'status'=>"tracing",
                    'lat'=>$order->lat,
                    'lng'=>$order->lng,
                    )
            );
        }else{
            //return null
            return response()->json(array('status'=>"not_tracing"));
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $drivers = User::role('driver')->get();

        $array_user_names = [];
        foreach($order->status as $key=>$value){
            $user_name = User::where('id',$value->pivot->user_id)->value('name');
            array_push($array_user_names, $user_name);
        }

        if(auth()->user()->hasRole('client') && auth()->user()->id == $order->client_id ||
            auth()->user()->hasRole('owner') && auth()->user()->id == $order->restorant->user->id ||
                auth()->user()->hasRole('driver') && auth()->user()->id == $order->driver_id || auth()->user()->hasRole('admin')
            ){
            return view('orders.show',['order'=>$order, 'userNames'=>$array_user_names, 'drivers'=>$drivers]);
        } else return redirect()->route('orders.index')->withStatus(__('No Access.'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function liveapi(){

        //TODO - Method not allowed for client or driver
        if(auth()->user()->hasRole('client')){
           dd("Not allowed as client");
        }

        //Today only
        $orders = Order::where('created_at', '>=', Carbon::today())->orderBy('created_at','desc');

        //If owner, only from his restorant
        if(auth()->user()->hasRole('owner')){
            $orders = $orders->where(['restorant_id'=>auth()->user()->restorant->id]);
        }
        $orders=$orders->with(['status','client','restorant'])->get()->toArray();


        $newOrders=array();
        $acceptedOrders=array();
        $doneOrders=array();

        $items=[];
        foreach ($orders as $key => $order) {
            array_push($items,array(
                'id'=>$order['id'],
                'restaurant_name'=>$order['restorant']['name'],
                'last_status'=>$order['status'][count($order['status'])-1]['name'],
                'last_status_id'=>$order['status'][count($order['status'])-1]['pivot']['status_id'],
                'time'=>$order['created_at'],
                'client'=>$order['client']['name'],
                'link'=>"/orders/".$order['id'],
                'price'=>money($order['order_price'], env('CASHIER_CURRENCY','usd'),true).""
            ));
        }

        //dd($items);

        /**
         *
{"id":"1","name":"Just created","alias":"just_created"},
{"id":"2","name":"Accepted by admin","alias":"accepted_by_admin"},
{"id":"3","name":"Accepted by restaurant","alias":"accepted_by_restaurant"},
{"id":"4","name":"Assigned to driver","alias":"assigned_to_driver"},
{"id":"5","name":"Prepared","alias":"prepared"},
{"id":"6","name":"Picked up","alias":"picked_up"},
{"id":"7","name":"Delivered","alias":"delivered"},
{"id":"8","name":"Rejected by admin","alias":"rejected_by_admin"},
{"id":"9","name":"Rejected by restaurant","alias":"rejected_by_restaurant"}
         */

        //----- ADMIN ------
        if(auth()->user()->hasRole('admin')){
            foreach ($items as $key => $item) {
                //Box 1 - New Orders
                    //Today orders that are just created ( Needs approvment or rejection )
                //Box 2 - Accepted
                    //Today orders approved by Restaurant , or by admin( Needs assign to driver )
                //Box 3 - Done
                    //Today orders assigned with driver, or rejected
                if($item['last_status_id']==1){
                    $item['pulse']='blob green';
                    array_push($newOrders,$item);
                }else if($item['last_status_id']==2||$item['last_status_id']==3){
                    $item['pulse']='blob orangestatic';
                    if($item['last_status_id']==3){
                        $item['pulse']='blob orange';
                    }
                    array_push($acceptedOrders,$item);
                }else if($item['last_status_id']>3){
                    $item['pulse']='blob greenstatic';
                    if($item['last_status_id']==9||$item['last_status_id']==8){
                        $item['pulse']='blob redstatic';
                    }
                    array_push($doneOrders,$item);
                }
            }
        }

        //----- Restaurant ------
        if(auth()->user()->hasRole('owner')){
            foreach ($items as $key => $item) {
               //Box 1 - New Orders
                    //Today orders that are approved by admin ( Needs approvment or rejection )
                //Box 2 - Accepted
                    //Today orders approved by Restaurant ( Needs change of status to done )
                //Box 3 - Done
                    //Today completed or rejected
                    $last_status=$item['last_status_id'];
                if($last_status==2){
                    $item['pulse']='blob green';
                    array_push($newOrders,$item);
                }else if($last_status==3||$last_status==4||$last_status==5){
                    $item['pulse']='blob orangestatic';
                    if($last_status==3){
                        $item['pulse']='blob orange';
                    }
                    array_push($acceptedOrders,$item);
                }else if($last_status>5&&$last_status!=8){
                    $item['pulse']='blob greenstatic';
                    if($last_status==9||$last_status==8){
                        $item['pulse']='blob redstatic';
                    }
                    array_push($doneOrders,$item);
                }
            }
        }


            $toRespond=array(
                'neworders'=>$newOrders,
                'accepted'=>$acceptedOrders,
                'done'=>$doneOrders
            );

            return response()->json($toRespond);
    }

    public function live()
    {
        return view('orders.live');
    }

    public function updateStatus($alias, Order $order)
    {
        if(isset($_GET['driver'])){
            $order->driver_id = $_GET['driver'];
            $order->update();
        }

        $status_id_to_attach = Status::where('alias',$alias)->value('id');

        //Check access before updating
        /**
         * 1 - Super Admin 
         * accepted_by_admin
         * assigned_to_driver
         * rejected_by_admin
         * 
         * 2 - Restaurant
         * accepted_by_restaurant
         * prepared
         * rejected_by_restaurant
         * picked_up
         * delivered
         * 
         * 3 - Driver
         * picked_up
         * delivered
         */
        //

        $rolesNeeded=[
            'accepted_by_admin'=>"admin",
            'assigned_to_driver'=>"admin",
            'rejected_by_admin'=>"admin",
            'accepted_by_restaurant'=>"owner",
            'prepared'=>"owner",
            'rejected_by_restaurant'=>"owner",
            'picked_up'=>["driver","owner"],
            'delivered'=>["driver","owner"]
        ];

        if(!auth()->user()->hasRole($rolesNeeded[$alias])){
            abort(403, 'Unauthorized action. You do not have the appropriate role');
        }

        //For owner - make sure this is his order
        if(auth()->user()->hasRole('owner')){
            //This user is owner, but we must check if this is order from his restaurant
            if(auth()->user()->id!=$order->restorant->user_id){
                abort(403, 'Unauthorized action. You are not owner of this order restaurant');
            }
        }

        //For driver - make sure he is assigned to this order
        if(auth()->user()->hasRole('driver')){
            //This user is owner, but we must check if this is order from his restaurant
            if(auth()->user()->id!=$order->driver->id){
                abort(403, 'Unauthorized action. You are not driver of this order');
            }
        }


              



        /**
         * IF status
         * Accept  - 3
         * Prepared  - 5
         * Rejected - 9
         */
       // dd($status_id_to_attach."");
        if($status_id_to_attach.""=="3"||$status_id_to_attach.""=="5"||$status_id_to_attach.""=="9"){
            $order->client->notify(new OrderNotification($order,$status_id_to_attach));
        }

        //Picked up - start tracing
        if($status_id_to_attach.""=="6"){
            $order->lat=$order->restorant->lat;
            $order->lng=$order->restorant->lng;
            $order->update();
        }

        if($alias.""=="delivered"){
            $order->payment_status='paid';
            $order->update();
        }



        //$order->status()->attach([$status->id => ['comment'=>"",'user_id' => auth()->user()->id]]);
        $order->status()->attach([$status_id_to_attach => ['comment'=>"",'user_id' => auth()->user()->id]]);
        return redirect()->route('orders.index')->withStatus(__('Order status succesfully changed.'));
    }

    public function modalshow()
    {
        /*$order = Order::find($id);
        if($order){

            $array_user_names = [];
            $array_status_times = [];
            foreach($order->status as $key=>$value){
                $user_name = User::where('id',$value->pivot->user_id)->value('name');
                array_push($array_user_names, $user_name);
                array_push($array_status_times, $value->pivot->created_at->format('d M Y h:m'));
            }

            $array_items = [];
            foreach($order->items as $key=>$value){
                if(array_key_exists($value->id, $array_items)) {
                    $array_items[$value->id]->count += 1;
                    $array_items[$value->id]->price += $value->price;
                }else{
                    $item = (object) [];
                    $item->name = $value->name;
                    $item->price = $value->price;
                    $item->count = 1;

                    $array_items[$value->id] = (object)$item;
                }
            }

            //dd($order->status[0]->pivot->user_id);
            return response()->json([
                'data' => [
                    'order_id'=>$order->id,
                    'order_created_at'=>$order->created_at->format('d M Y h:m'),
                    'order_restorant'=>$order->restorant,
                    'order_restorant_owner'=>$order->restorant->user,
                    'order_delivery_price'=>$order->delivery_price,
                    'order_total_price'=>$order->delivery_price + $order->order_price,
                    'order_client'=>$order->client,
                    'order_status'=>$order->status,
                    'order_status_usernames'=>$array_user_names,
                    'order_status_times'=>$array_status_times,
                    'order_items'=> $array_items,
                    'order_status_last'=>$order->status->pluck('alias')->last()
                ],
                'status' => true,
                'errMsg' => ''
            ]);
        }else{
            return response()->json([
                'errMsg' => "Order can't be found!",
                'status' => false
            ], 401);
        }*/
    }

}
