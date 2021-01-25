<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Restorant extends Model
{

    protected $fillable = ['name','subdomain', 'user_id', 'lat','lng','address','phone','logo','description'];
    protected $appends = ['alias','logom','icon','coverm'];
    protected $imagePath='/uploads/restorants/';

    protected function getImge($imageValue,$default,$version="_large.jpg"){
        if($imageValue==""||$imageValue==null){
            //No image
            return $default;
        }else{
            if(strpos($imageValue, 'http') !== false){
                //Have http
                if(strpos($imageValue, '.jpg') !== false||strpos($imageValue, '.jpeg') !== false||strpos($imageValue, '.png') !== false){
                    //Has extension
                    return $imageValue;
                }else{
                    //No extension
                    return $imageValue.$version;
                }
            }else{
                //Local image
                return ($this->imagePath.$imageValue).$version;
            }
        }
    }

    /**
     * Get the user that owns the restorant.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getAliasAttribute()
    {
        return $this->subdomain;
    }


    public function getLogomAttribute()
    {
        return $this->getImge($this->logo,config('global.restorant_details_image'));
    }
    public function getIconAttribute()
    {
        return $this->getImge($this->logo,str_replace("_large.jpg","_thumbnail.jpg",config('global.restorant_details_image')),"_thumbnail.jpg");
    }

    public function getCovermAttribute()
    {
        return $this->getImge($this->cover,config('global.restorant_details_cover_image'),"_cover.jpg");
    }

    public function categories()
    {
        return $this->hasMany('App\Categories','restorant_id','id')->where(['categories.active' => 1]);
    }

    public function hours()
    {
        return $this->hasOne('App\Hours','restorant_id','id');
    }
}
