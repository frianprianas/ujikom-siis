<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IotLog extends Model
{
    use HasFactory;

    protected $table = 'iot_logs';

    protected $fillable = [
        'device_id',
        'user_id',
        'rfid_uid',
        'tipe_akses',
        'status_aksi',
        'keterangan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(IotDevice::class, 'device_id', 'device_id');
    }
}

