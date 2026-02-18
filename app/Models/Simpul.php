<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Simpul extends Model
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
    ];

    // Accessor for Lat/Lng from PostGIS Geometry
    public function getCoordinatesAttribute()
    {
        if (!$this->location) return null;
        
        // This expects the query to select ST_X and ST_Y or ST_AsGeoJSON
        // But if we access the raw binary, we can't parse it easily without a library.
        // Best approach is to use a scope or raw selection.
        return null; 
    }

    public function scopeWithCoordinates($query)
    {
        return $query->select('*', 
            DB::raw('ST_Y(location::geometry) as lat'), 
            DB::raw('ST_X(location::geometry) as lng')
        );
    }
}
