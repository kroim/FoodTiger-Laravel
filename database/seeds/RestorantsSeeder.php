<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RestorantsTableSeeder extends Seeder
{

    public function shuffle_assoc(&$array) {
        $keys = array_keys($array);

        shuffle($keys);

        foreach($keys as $key) {
            $new[$key] = $array[$key];
        }

        $array = $new;

        return true;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /*$pizza = json_decode(File::get('database/seeds/json/pizza_res.json'),true);
        foreach (json_decode($pizza,true) as $key => $value) {
           print_r($key."\n");
           print_r(json_encode($value)."\n");
        }*/


        //Restorant owner
         DB::table('users')->insert([
            'name' => "Demo Owner",
            'email' =>  "owner@example.com",
            'password' => Hash::make("secret"),
            'api_token' => Str::random(80),
            'email_verified_at' => now(),
            'phone' =>  "",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        //Assign owner role
        DB::table('model_has_roles')->insert([
            'role_id' => 2,
            'model_type' =>  'App\User',
            'model_id'=> 2
        ]);

        $pizza=json_decode(File::get(base_path('database/seeds/json/pizza_res.json')),true);
        $mex=json_decode(File::get(base_path('database/seeds/json/mexican_res.json')),true);
        $burg=json_decode(File::get(base_path('database/seeds/json/burger_res.json')),true);
        $reg=json_decode(File::get(base_path('database/seeds/json/regular_res.json')),true);

        $restorants=array(
            array('items'=>$pizza,'name'=>"Pizza Napoli",'description'=>"italian, pasta, pizza","image"=>"https://foodtiger.mobidonia.com/uploads/restorants/9d180742-9fb3-4b46-8563-8c24c9004fd3"),
            array('items'=>$burg,'name'=>"Burger King",'description'=>"burgers, drinks, best chicken","image"=>"https://foodtiger.mobidonia.com/uploads/restorants/c8d27bcc-54da-4c18-b8e6-f1414c71612c"),
            array('items'=>$mex,'name'=>"Mexican Restorant",'description'=>"yummy taco, wraps, fast food","image"=>"https://foodtiger.mobidonia.com/uploads/restorants/3e571ad8-e161-4245-91d9-88b47d6d6770"),
            array('items'=>$reg,'name'=>"Restoran Bonimi",'description'=>"drinks, lunch, bbq",'image'=>"https://foodtiger.mobidonia.com/uploads/restorants/6fa5233f-00f3-4f52-950c-5a1705583dfc")
        );

        $this->shuffle_assoc($pizza);
        $this->shuffle_assoc($mex);
        $this->shuffle_assoc($burg);
        $this->shuffle_assoc($reg);

        array_push($restorants,array('items'=>$pizza,'name'=>"Veneto Italian Restorant",'description'=>"italian, pasta, pizza","image"=>"https://foodtiger.mobidonia.com/uploads/restorants/4a2067cb-f39c-4b26-83ef-9097512d3328"));
        array_push($restorants,array('items'=>$mex,'name'=>"Fresh Taco",'description'=>"yummy taco, wraps, fast food","image"=>"https://foodtiger.mobidonia.com/uploads/restorants/6f9e8892-4a28-4c99-ab24-57179a1424b9"));
        array_push($restorants,array('items'=>$burg,'name'=>"Burger 2Go",'description'=>"burgers, drinks, best chicken","image"=>"https://foodtiger.mobidonia.com/uploads/restorants/80a49037-07e9-4e28-b23e-66fd641c1c77"));
        array_push($restorants,array('items'=>$reg,'name'=>"Giovani Bar & Grill",'description'=>"drinks, lunch, bbq","image"=>"https://foodtiger.mobidonia.com/uploads/restorants/56e90ea7-5321-4cfd-8b2c-918ccd3c3f77"));

        $this->shuffle_assoc($pizza);
        $this->shuffle_assoc($mex);
        $this->shuffle_assoc($burg);
        $this->shuffle_assoc($reg);

        array_push($restorants,array('items'=>$pizza,'name'=>"Pizza Venecia",'description'=>"italian, international, pasta","image"=>"https://foodtiger.mobidonia.com/uploads/restorants/0102bebe-b6c4-46b0-9195-ee06bca71a37"));
        array_push($restorants,array('items'=>$mex,'name'=>"Burito Yum",'description'=>"tacos, wraps, Quesadilla","image"=>"https://foodtiger.mobidonia.com/uploads/restorants/4384df9b-9656-49d1-bfc1-9b5e85e1193a"));
        array_push($restorants,array('items'=>$burg,'name'=>"Burger House",'description'=>"drinks, beef burgers","image"=>"https://foodtiger.mobidonia.com/uploads/restorants/5757558a-94d7-4ba9-b39c-2e258701f051"));
        array_push($restorants,array('items'=>$reg,'name'=>"DaVinci Restorant",'description'=>"drinks, lunch, bbq","image"=>"https://foodtiger.mobidonia.com/uploads/restorants/a2b5b612-9fec-4e28-bb7d-88a06d97bda6"));




        $id=1;
        $catId=1;
        foreach ($restorants as $key => $restorant) {
            DB::table('restorants')->insert([
                'name'=>$restorant['name'],
                'logo'=>$restorant['image'],
                'subdomain'=>strtolower(preg_replace('/[^A-Za-z0-9]/', '', $restorant['name'])),
                'user_id'=>2,
                'created_at' => now(),
                'updated_at' => now(),
                'lat' => 42.005,
                'lng' => 21.44,
                'address' => '6 Yukon Drive Raeford, NC 28376',
                'phone' => '(530) 625-9694',
                'description'=>$restorant['description'],
                'minimum'=>10,
            ]);

            DB::table('hours')->insert([
                'restorant_id' => $id,
                '0_from' => '05:00',
                '0_to' => '23:00',
                '1_from' => '05:00',
                '1_to' => '23:00',
                '2_from' => '05:00',
                '2_to' => '23:00',
                '3_from' => '05:00',
                '3_to' => '23:00',
                '4_from' => '05:00',
                '4_to' => '23:00',
                '5_from' => '05:00',
                '5_to' => '23:00',
                '6_from' => '05:00',
                '6_to' => '23:00',
            ]);

            foreach ($restorant['items'] as $category => $categoryData) {
                DB::table('categories')->insert([
                    'name'=>$category,
                    'restorant_id'=>$id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                foreach ($categoryData as $key => $menuItem) {
                    DB::table('items')->insert([
                        'name'=>isset($menuItem['title'])?$menuItem['title']:"",
                        'description'=>isset($menuItem['description'])?$menuItem['description']:"",
                        'image'=>isset($menuItem['image'])?$menuItem['image']:"",
                        'price'=>isset($menuItem['price'])?$menuItem['price']:"",
                        'category_id'=>$catId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                $catId++;
            }

            $id++;
        }

    }
}
