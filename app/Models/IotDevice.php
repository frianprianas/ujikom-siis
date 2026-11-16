<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IotDevice extends Model
{
    use HasFactory;

    protected $table = 'iot_devices';

    protected $fillable = [
        'device_id',
        'nama_device',
        'api_key',
        'status',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function logs(): HasMany
    {
        return $this->hasMany(IotLog::class, 'device_id', 'device_id');
    }
}

