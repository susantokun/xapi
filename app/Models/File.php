<?php

namespace App\Models;

use App\Traits\HasUserTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    use HasUserTrait;

    protected $guarded = [];

    protected $casts = [
        'files' => 'array',
        'details' => 'array',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
