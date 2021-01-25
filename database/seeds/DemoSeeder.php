<?php

use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
            * Restorant
            * Categories
            * Menu Items
            * Drivers
            * Clients
            * Orders
         */

         //Restorants
         $this->call(RestorantsTableSeeder::class);


        //Driver
        DB::table('users')->insert([
            'name' => "Demo Driver",
            'email' =>  "driver@example.com",
            'password' => Hash::make("secret"),
            'api_token' => Str::random(80),
            'email_verified_at' => now(),
            'phone' =>  "",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        //Assign driver role
        DB::table('model_has_roles')->insert([
            'role_id' => 3,
            'model_type' =>  'App\User',
            'model_id'=> 3
        ]);

         //Client 1
         DB::table('users')->insert([
            'name' => "Demo Client 1",
            'email' =>  "client@example.com",
            'password' => Hash::make("secret"),
            'api_token' => Str::random(80),
            'email_verified_at' => now(),
            'phone' =>  "",
            'created_at' => now(),
            'updated_at' => now()
        ]);
        //Assign driver role
        DB::table('model_has_roles')->insert([
            'role_id' => 4,
            'model_type' =>  'App\User',
            'model_id'=> 4
        ]);

        //Client 1
        DB::table('users')->insert([
            'name' => "Demo Client 2",
            'email' =>  "client2@example.com",
            'password' => Hash::make("secret"),
            'api_token' => Str::random(80),
            'email_verified_at' => now(),
            'phone' =>  "",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        //Assign driver role
        DB::table('model_has_roles')->insert([
            'role_id' => 4,
            'model_type' =>  'App\User',
            'model_id'=> 5
        ]);


        //Addresses
        $clientAddress = factory(App\Address::class, 5)->create();

        //Orders
        $demoOrders = factory(App\Order::class, 500)->create();

        $demoOrdersLast3Days = factory(App\Order::class, 100)->states('recent')->create();


        //Order has status initial
        foreach ($demoOrders as $key => $order) {
            DB::table('order_has_status')->insert([
                'order_id' => $order->id,
                'status_id' =>  1,
                'user_id' => 4,
                'comment' => 'Initial comment',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        //In the last 3 days
        foreach ($demoOrdersLast3Days as $key => $order) {
            //JUST created
            DB::table('order_has_status')->insert([
                'order_id' => $order->id,
                'status_id' =>  1,
                'user_id' => 4,
                'comment' => 'New order created',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            //Every 10th rejected by admin
            if($order->id%10==0){
                DB::table('order_has_status')->insert([
                    'order_id' => $order->id,
                    'status_id' =>  8,
                    'user_id' => 1,
                    'comment' => 'Rejected by admin',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }else{
                //Move on
                //one in 10 - wait for user
                if($order->id%9!=0){
                    DB::table('order_has_status')->insert([
                        'order_id' => $order->id,
                        'status_id' =>  2,
                        'user_id' => 1,
                        'comment' => 'Accepted by admin',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    //Every 24th rejected by admin
                    if($order->id%24==0){
                        DB::table('order_has_status')->insert([
                            'order_id' => $order->id,
                            'status_id' =>  9,
                            'user_id' => 1,
                            'comment' => 'Rejected by restaurant',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }else{
                        if($order->id%2==0){
                            DB::table('order_has_status')->insert([
                                'order_id' => $order->id,
                                'status_id' =>  3,
                                'user_id' => 1,
                                'comment' => 'Accepted by restaurant',
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            if($order->id%4==0){
                                DB::table('order_has_status')->insert([
                                    'order_id' => $order->id,
                                    'status_id' =>  4,
                                    'user_id' => 1,
                                    'comment' => 'Assigned to driver',
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                                $order->driver_id=3;
                                $order->update();
                                DB::table('order_has_status')->insert([
                                    'order_id' => $order->id,
                                    'status_id' =>  5,
                                    'user_id' => 1,
                                    'comment' => 'Prepared by restaurant',
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                                if($order->id%8==0){
                                    DB::table('order_has_status')->insert([
                                        'order_id' => $order->id,
                                        'status_id' =>  6,
                                        'user_id' => 1,
                                        'comment' => 'Picked up',
                                        'created_at' => now(),
                                        'updated_at' => now()
                                    ]);
                                    $order->lat=$order->restorant->lat;
                                    $order->lng=$order->restorant->lng;
                                    $order->update();
                                    if($order->id%16==0){
                                        DB::table('order_has_status')->insert([
                                            'order_id' => $order->id,
                                            'status_id' =>  7,
                                            'user_id' => 1,
                                            'comment' => 'Delivered',
                                            'created_at' => now(),
                                            'updated_at' => now()
                                        ]);
                                    }
                                }
                            }
                        }


                    }

                }
            }

            //$statuses=["Just created","Accepted by admin","Accepted by restaurant","Assigned to driver","Prepared","Picked up","Delivered","Rejected by admin","Rejected by restaurant"];
        }




        
    }
}
