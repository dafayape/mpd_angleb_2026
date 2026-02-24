<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Node extends Model
{
    protected $table = 'ref_transport_nodes';

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'category',
        'sub_category',
        'location',
        'radius',
    ];

    /**
     * Scope for easy coordinate access (PostGIS geometry)
     */
    public function scopeWithCoordinates($query)
    {
        return $query->select('*',
            DB::raw('ST_Y(location::geometry) as lat'),
            DB::raw('ST_X(location::geometry) as lng')
        );
    }
}
