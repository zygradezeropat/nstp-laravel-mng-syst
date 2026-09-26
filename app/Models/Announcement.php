<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'announcements';

    public $timestamps = true;

    protected $fillable = [
        'title',
        'content',
        'source',
        'is_pinned',
        'target_role',
        'component',
        'scheduled_date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_pinned'      => 'boolean',
        'scheduled_date' => 'date',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];
}
