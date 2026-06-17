<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorEmail extends Model
{
    protected $table = 'visitor_emails';

    protected $fillable = [
        'email',
        'visit_count',
        'first_visited_at',
        'last_visited_at',
        'ip_address',
        'user_agent'
    ];
}