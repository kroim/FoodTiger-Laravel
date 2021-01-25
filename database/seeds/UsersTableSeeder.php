<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => env('ADMIN_NAME', "Admin Admin"),
            'email' =>  env('ADMIN_EMAIL', "admin@example.com"),
            'password' => Hash::make( env('ADMIN_PASSWORD', "secret")),
            'api_token' => Str::random(80),
            'email_verified_at' => now(),
            'phone' =>  "",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        //Settings
        DB::table('settings')->insert([
            'site_name' => 'FoodTiger',
            'site_logo' =>  '/default/logo.png',
            'restorant_details_image' => '/default/restaurant_large.jpg',
            'restorant_details_cover_image' => '/default/cover.jpg',
            'search' => '/default/cover.jpg',
            'description' => 'Food Delivery from best restaurants',
            'header_title' => 'Food Delivery from<br /><b>New York’s</b> Best Restaurants',
            'header_subtitle' => 'The meals you love, delivered with care',
            'created_at' => now(),
            'updated_at' => now(),
            'delivery'=> 0,
            'maps_api_key' => 'AIzaSyCZhq0g1x1ttXPa1QB3ylcDQPTAzp_KUgA',
            'mobile_info_title' => 'Download the food you love',
            'mobile_info_subtitle' => 'It`s all at your fingertips - the restaurants you love. Find the right food to suit your mood, and make the first bite last. Go ahead, download us.'
        ]);
    }
}
