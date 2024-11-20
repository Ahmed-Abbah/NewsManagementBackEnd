<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Define the table name if it's not the plural form of the model name
    protected $table = 'www_terms';

    // Specify which fields can be mass-assigned
    protected $fillable = ['term_id','name',
'slug','term_group'];
}
