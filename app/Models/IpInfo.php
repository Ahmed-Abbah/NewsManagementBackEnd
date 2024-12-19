<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpInfo extends Model
{
 

    // Define the table name
    protected $table = 'ip_info';

    // Define the fillable attributes
    protected $fillable = [
        'status',
        'country',
        'countryCode',
        'region',
        'regionName',
        'city',
        'isp',
        'lat',
        'lon',
        'org',
        'query',
        'timezone',
        'zip'
    ];

    // Set timestamps to false if you don't have created_at and updated_at fields
    public $timestamps = true;
}
