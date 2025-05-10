<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Source extends Model
{
    use SoftDeletes;
    protected $table = 'sources';

    protected $fillable = [
        'title', 'url', 'source', 'processed_at', 'fail_count'
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'fail_count' => 'integer'
    ];
}
