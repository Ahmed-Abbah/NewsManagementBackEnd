<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCategoryMapping extends Model
{
    use HasFactory;

    // Define the table explicitly if it doesn't follow Laravel's naming convention
    protected $table = 'post_category_mapping';
    protected $primaryKey = 'CategoryId';       // Define the primary key if it's not `id`
    public $timestamps = false;
    // Define fillable fields for mass assignment
    protected $fillable = [
        'PostTitle',
        'PostId',
        'CategoryName',
        'CategoryId',
    ];
}
