<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper: catat aktivitas dari controller mana saja
     */
    public static function log(string $action, ?string $subject = null, string $status = 'Success', ?string $description = null): self
    {
        return static::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'subject'    => $subject,
            'description'=> $description,
            'status'     => $status,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}