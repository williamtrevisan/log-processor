<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'consumer_id',
        'service_id',
        'service_name',
        'proxy_latency',
        'kong_latency',
        'request_latency',
    ];

    protected $casts = [
        'consumer_id' => 'string',
        'service_id' => 'string',
    ];
}
