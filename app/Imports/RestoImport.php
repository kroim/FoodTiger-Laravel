<?php

namespace App\Imports;

use App\Restorant;
use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RestoImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        //Create the user
        $owner = new User;
        $owner->name = $row['owner_name'];
        $owner->email = $row['owner_email'];
        $owner->phone = $row['owner_phone'];
        $owner->api_token = Str::random(80);

        $owner->password =  Hash::make($row['owner_password']);
        $owner->save();

        //Assign role
        $owner->assignRole('owner');

        return new Restorant([
            'name' => $row['name'],
            'description' => $row['description']."",
            'subdomain' => strtolower(preg_replace('/[^A-Za-z0-9]/', '', $row['name']))."",
            'user_id' => $owner->id,
            'lat' => 42.005,
            'lng' => 21.44,
            'address' => $row['address'],
            'phone' => $row['restaurant_phone'],
            'logo' => $row['logo']
            //'minimum' => $row[4]
        ]);
    }
}
