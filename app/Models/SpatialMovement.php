<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpatialMovement extends Model
{
    use HasFactory, \App\Traits\FilterableMovement;

    protected $table = 'spatial_movements';

    protected $guarded = ['id']; // Using composite keys basically, so just guarding id to allow mass assignment

    public $timestamps = true;

    // Casting created_at/updated_at is default behavior
    protected $casts = [
        'tanggal' => 'date',
        'total' => 'integer',
        'distance_meters' => 'float',
    ];

    // Disable primary key management since we use composite unique keys for upserts mainly
    // Ideally we might want a composite primary key package or just treat it as read-heavy model usually
    // For standard Eloquent read, standard ID assumption might fail if we don't have one.
    // The migration didn't add a serial ID. It used a unique constraint.
    // So we should set primaryKey to null or composite if we use a trait.
    // simpler: define generic access
    public $incrementing = false;
    protected $primaryKey = null;
}
