<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCategoryMapping extends Model
{
    use HasFactory;

    // Define the table explicitly if it doesn't follow Laravel's naming convention
    protected $table = 'post_category_mapping';

    // Define fillable fields for mass assignment
    protected $fillable = [
        'PostTitle',
        'PostId',
        'CategoryName',
        'CategoryId',
    ];

    // Disable timestamps if not used in the table
    public $timestamps = false;
}
