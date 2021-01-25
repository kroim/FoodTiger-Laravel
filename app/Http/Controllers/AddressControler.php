<?php

namespace App\Http\Controllers;

use App\Address;
use Illuminate\Http\Request;
use Spatie\Geocoder\Geocoder;

class AddressControler extends Controller
{
    protected $autocomepleteEndpoint = "https://maps.googleapis.com/maps/api/place/autocomplete/json";
    protected $detailsEndpoint = "https://maps.googleapis.com/maps/api/place/details/json";

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(auth()->user()->hasRole('client')){
            return view('addresses.index',['addresses' =>Address::where(['user_id'=>auth()->user()->id])->where(['active'=>1])->get()]);
        }else{
            return redirect()->route('orders.index')->withStatus(__('No Access'));
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $address = new Address;
        $address->address = strip_tags($request->new_address);
        $address->user_id = auth()->user()->id;
        $address->lat = $request->lat;
        $address->lng = $request->lng;
        $address->apartment = $request->apartment ?? $request->apartment;
        $address->intercom = $request->intercom ?? $request->intercom;
        $address->floor =  $request->floor ?? $request->floor;
        $address->entry = $request->entry ?? $request->entry;
        $address->save();

        //return redirect()->route('/cart-checkout')->withStatus(__('Address successfully added.'));

        return response()->json([
            'status' => true,
            'success_url' => redirect()->intended('/cart-checkout')->getTargetUrl(),
            'msg' => 'Address successfully added!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function show(Address $address)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function edit(Address $address)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Address $address)
    {
        /*$client = new \GuzzleHttp\Client();

        $geocoder = new Geocoder($client);

        $geocoder->setApiKey(env('GOOGLE_MAPS_GEOCODING_API_KEY',"));

        $res = $geocoder->getAddressForCoordinates($request->lat, $request->lng);*/

        $address->lat = $request->lat;
        $address->lng = $request->lng;

        $address->update();

        return response()->json([
            'status' => true,
            'success_url' => redirect()->intended('/cart-checkout')->getTargetUrl(),
            'msg' => 'Address successfully updated!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function destroy(Address $address)
    {
        $address->active=0;
        $address->save();

        return redirect()->route('addresses.index')->withStatus(__('Address successfully deactivated.'));
    }

    public function orderAddress(Request $request){

        $address = Address::where(['id' => $request->addressID])->get()->first();

        return response()->json([
            'status' => true,
            'data' => [
                'lat' => $address->lat,
                'lng' => $address->lng,
                'address' => $address->address,
            ],
            'msg' => ''
        ]);
    }

    public function newAddressAutocomplete()
    {
        if(!isset($_GET['term'])){
            return response()->json(array('results'=>[]));
        }

        $term = $_GET["term"];

        if(strlen($term)<2){
            return response()->json(array('results'=>[]));
        }

        $client=new \GuzzleHttp\Client();

        $payload=[
            'query' => array(
                'key' => env('GOOGLE_MAPS_API_KEY'),
                'input' => $term
            )
        ];
        $response= $client->request('GET', $this->autocomepleteEndpoint, $payload);

        if ($response->getStatusCode() !== 200) {
            return response()->json(array('results'=>[]));
        }

        $responseDecoded = json_decode($response->getBody());

        $matches=array();
        if(isset($responseDecoded->predictions)){

            foreach ($responseDecoded->predictions as $key => $prediction) {
                array_push($matches,array('id' => $prediction->place_id, 'text' => $prediction->description));
            }
        }

        $data = array('results'=>$matches);
        return response()->json($data);
    }

    public function newAdressPlaceDetails(Request $request)
    {
        $itemToReturn=null;
        $client=new \GuzzleHttp\Client();

        $payload=[
            'query' => array(
                'key' => env('GOOGLE_MAPS_API_KEY'),
                'place_id' => $request->place_id
            )
        ];

        $client=new \GuzzleHttp\Client();
        $response= $client->request('GET', $this->detailsEndpoint, $payload);

        if ($response->getStatusCode() !== 200) {
            //return $itemToReturn;
            return response()->json(array(
                'status' => false,
                'result' => []
            ));
        }

        $responseDecoded = json_decode($response->getBody());

        if(isset($responseDecoded->result)){

            $itemToReturn['google_place_id'] = $request->place_id;

            //Find the lat and lng
            $itemToReturn['lat'] = $responseDecoded->result->geometry->location->lat;
            $itemToReturn['lng'] = $responseDecoded->result->geometry->location->lng;

            //name
            $itemToReturn['name'] = $responseDecoded->result->name;

            //return $itemToReturn;
            return response()->json(array(
                'status' => true,
                'result' => $itemToReturn
            ));

        }else{
            //return null;
            return response()->json(array(
                'status' => false,
                'result' => []
            ));
        }
    }
}
