<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CronLog extends Model
{
    public $timestamps = false;
    protected $table = 'cn_cron_log';
    protected $fillable = [
        'order_id',
        'xendit_external_id',
        'xendit_id',
        'log'
    ];
}
