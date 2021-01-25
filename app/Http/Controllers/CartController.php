<?php

namespace App\Http\Controllers;

use Cart;
use App\Items;
use App\Restorant;
use App\Order;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Akaunting\Money\Currency;
use Akaunting\Money\Money;

class CartController extends Controller
{
    public function add(Request $request){
        $item = Items::find($request->id);
        $restID=$item->category->restorant->id;

        //Check if added item is from the same restorant as previus items in cart
        $canAdd = false;
        if(Cart::getContent()->isEmpty()){
            $canAdd = true;
        }else{
            $canAdd = true;
            foreach (Cart::getContent() as $key => $cartItem) {
                if($cartItem->attributes->restorant_id."" != $restID.""){
                    $canAdd = false;
                    break;
                }
            }
        }

        //TODO - check if cart contains, if so, check if restorant is same as pervios one

       // Cart::clear();
        if($item && $canAdd){
            Cart::add($item->id, $item->name, $item->price, $request->quantity, array('restorant_id'=>$restID,'image'=>$item->icon,'friendly_price'=>$item->itemprice));
        }

        if($item && $canAdd){
            return response()->json([
                'status' => true,
                'errMsg' => ''
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errMsg' => "You can't add items from other restaurant!"
            ]);
            //], 401);
        }
    }

    public function getContent(){
        //Cart::clear();
        return response()->json([
            'data' => Cart::getContent(),
            'total' => Cart::getSubTotal(),
            'totalFormat' => Money(Cart::getSubTotal(), env('CASHIER_CURRENCY','usd'),true)->format(),
            'withDelivery' => Cart::getSubTotal()+config('global.delivery'),
            'withDeliveryFormat' => Money(Cart::getSubTotal()+config('global.delivery'), env('CASHIER_CURRENCY','usd'),true)->format(),
            'status' => true,
            'errMsg' => ''
        ]);
    }

    public function minutesToHours($numMun){
        $h =(int) ($numMun/60);
        $min=$numMun%60;
        if($min<10){
            $min="0".$min;
        }

        $time=$h.":".$min;
        if(env('TIME_FORMAT',"24hours")=="AM/PM"){
            $time=date("g:i A", strtotime($time));
        }
        return $time;
    }


    /*"0_from" => "09:00"
  "0_to" => "20:00"
  "1_from" => "09:00"
  "1_to" => "20:00"
  "2_from" => "09:00"
  "2_to" => "20:00"
  "3_from" => "09:00"
  "3_to" => "20:00"
  "4_from" => "09:00"
  "4_to" => "20:00"
  "5_from" => "09:00"
  "5_to" => "17:00"
  "6_from" => "09:00"
  "6_to" => "17:00"*/

  /*
    "0_from" => "9:00 AM"
  "0_to" => "8:10 PM"
  "1_from" => "9:00 AM"
  "1_to" => "8:00 PM"
  "2_from" => "9:00 AM"
  "2_to" => "8:00 PM"
  "3_from" => "9:00 AM"
  "3_to" => "8:00 PM"
  "4_from" => "9:00 AM"
  "4_to" => "8:00 PM"
  "5_from" => "9:00 AM"
  "5_to" => "5:00 PM"
  "6_from" => "9:00 AM"
  "6_to" => "5:00 PM"
   */

    public function getMinutes($time){
        $parts=explode(':',$time);
        return ((int)$parts[0])*60+(int)$parts[1];
    }



    public function getTimieSlots($hours){

        $ourDateOfWeek=[6,0,1,2,3,4,5][date('w')];
        $restaurantOppeningTime=$this->getMinutes(date("G:i", strtotime($hours[$ourDateOfWeek."_from"])));
        $restaurantClosingTime=$this->getMinutes(date("G:i", strtotime($hours[$ourDateOfWeek."_to"])));


        //Interval
        $intervalInMinutes=env('DELIVERY_INTERVAL_IN_MINUTES',30);

        //Generate thintervals from
        $currentTimeInMinutes= Carbon::now()->diffInMinutes(Carbon::today());
        $from= $currentTimeInMinutes>$restaurantOppeningTime?$currentTimeInMinutes:$restaurantOppeningTime;//Workgin time of the restaurant or current time,



        //print_r('now: '.$from);
        //To have clear interval
        $missingInterval=$intervalInMinutes-($from%$intervalInMinutes); //21

        //print_r('<br />missing: '.$missingInterval);

        //Time to prepare the order in minutes
        $timeToPrepare=30; //30

        //First interval
        $from+= $timeToPrepare<=$missingInterval?$missingInterval:($intervalInMinutes-(($from+$timeToPrepare)%$intervalInMinutes))+$timeToPrepare;

        //$from+=$missingInterval;

        //Generate thintervals to
        $to= $restaurantClosingTime;//Closing time of the restaurant or current time


        $timeElements=[];
        for ($i=$from; $i <= $to ; $i+=$intervalInMinutes) {
            array_push($timeElements,$i);
        }
        //print_r("<br />");
        //print_r($timeElements);



        $slots=[];
        for ($i=0; $i < count($timeElements)-1 ; $i++) {
            array_push($slots,[$timeElements[$i],$timeElements[$i+1]]);
        }

        //print_r("<br />SLOTS");
        //print_r($slots);


        //INTERVALS TO TIME
        $formatedSlots=[];
        for ($i=0; $i < count($slots) ; $i++) {
            $key=$slots[$i][0]."_".$slots[$i][1];
            $value=$this->minutesToHours($slots[$i][0])." - ".$this->minutesToHours($slots[$i][1]);
            $formatedSlots[$key]=$value;
            //array_push($formatedSlots,[$key=>$value]);
        }



        return($formatedSlots);


    }

    public function getRestorantHours($restorantID){
          //Create all the time slots
          //The restaurant
          $restaurant=Restorant::findOrFail($restorantID);
          
          $timeSlots=$restaurant->hours?$this->getTimieSlots($restaurant->hours->toArray()):[];

          //Modified time slots for app
          $timeSlotsForApp=[];
          foreach ($timeSlots as $key => $timeSlotsTitle) {
             array_push($timeSlotsForApp,array('id'=>$key,'title'=>$timeSlotsTitle));
          }

          //Working hours
          $ourDateOfWeek=[6,0,1,2,3,4,5][date('w')];
  
          $format="G:i";
          if(env('TIME_FORMAT',"24hours")=="AM/PM"){
              $format="g:i A";
          }
  
  
          $openingTime=date($format, strtotime($restaurant->hours[$ourDateOfWeek."_from"]));
          $closingTime=date($format, strtotime( $restaurant->hours[$ourDateOfWeek."_to"]));

          $params = [
            'restorant' => $restaurant,
            'timeSlots' => $timeSlotsForApp,
            'openingTime' => $restaurant->hours&&$restaurant->hours[$ourDateOfWeek."_from"]?$openingTime:null,
            'closingTime' => $restaurant->hours&&$restaurant->hours[$ourDateOfWeek."_to"]?$closingTime:null,
         ];

         if($restaurant){
            return response()->json([
                'data' => $params,
                'status' => true,
                'errMsg' => ''
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errMsg' => 'Restorants not found!'
            ]);
        }

    }

    public function cart(){
        $restorantID=null;
        foreach (Cart::getContent() as $key => $cartItem) {
            $restorantID=$cartItem->attributes->restorant_id;
            break;
        }

        //The restaurant
        $restaurant=Restorant::findOrFail($restorantID);

        //Create all the time slots
        $timeSlots=$restaurant->hours?$this->getTimieSlots($restaurant->hours->toArray()):[];

        //Working hours
        $ourDateOfWeek=[6,0,1,2,3,4,5][date('w')];

        $format="G:i";
        if(env('TIME_FORMAT',"24hours")=="AM/PM"){
            $format="g:i A";
        }


        $openingTime=date($format, strtotime($restaurant->hours[$ourDateOfWeek."_from"]));
        $closingTime=date($format, strtotime( $restaurant->hours[$ourDateOfWeek."_to"]));

        $params = [
            'title' => 'Shopping Cart Checkout',
            'restorant' => $restaurant,
            'timeSlots' => $timeSlots,
            'openingTime' => $restaurant->hours&&$restaurant->hours[$ourDateOfWeek."_from"]?$openingTime:null,
            'closingTime' => $restaurant->hours&&$restaurant->hours[$ourDateOfWeek."_to"]?$closingTime:null,
        ];






        //Open for all
        return view('cart')->with($params);
    }

    public function clear(Request $request){

        //Get the client_id from address_id

        $oreder = new Order;
        $oreder->address_id = strip_tags($request->addressID);
        $oreder->restorant_id = strip_tags($request->restID);
        $oreder->client_id = auth()->user()->id;
        $oreder->driver_id = 2;
        $oreder->delivery_price = 3.00;
        $oreder->order_price = strip_tags($request->orderPrice);
        $oreder->comment = strip_tags($request->comment);
        $oreder->save();

        foreach (Cart::getContent() as $key => $item) {
            $oreder->items()->attach($item->id);
        }

        //Find first status id,
        ///$oreder->stauts()->attach($status->id,['user_id'=>auth()->user()->id]);
        Cart::clear();
        return redirect()->route('front')->withStatus(__('Cart clear.'));
        //return back()->with('success',"The shopping cart has successfully beed added to the shopping cart!");;
    }


    /**

     * Create a new controller instance.

     *

     * @return void

     */

    public function remove(Request $request){
        $item = Items::find($request->id);
            Cart::remove($request->id);


            if($item){
                return response()->json([

                    'status' => true,
                    'errMsg' => ''
                ]);}
                else{
                    return response()->json([
                        'errMsg' => "Item can't be found!",
                        'status' => false
                    ], 401);
                }
    }

    /**
     * Makes general api resonse
     */
    private function generalApiResponse(){
        return response()->json([
            'status' => true,
            'errMsg' => ''
        ]);
    }

    /**
     * Updates cart
     */
    private function updateCartQty($howMuch,$item_id){
        Cart::update($item_id, array('quantity' => $howMuch));
        return $this->generalApiResponse();
    }


    /**
     * Increase cart
     */
    public function increase(Items $item){
       return $this->updateCartQty(1,$item->id);
    }

    /**
     * Decrese cart
     */
    public function decrease(Items $item){
        return $this->updateCartQty(-1,$item->id);
    }

}

