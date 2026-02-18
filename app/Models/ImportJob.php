<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename', 'original_filename', 'opsel', 'kategori',
        'tanggal_data', 'user_id', 'status', 'progress',
        'total_rows', 'processed_rows', 'error_message', 'metadata',
    ];

    protected $casts = [
        'metadata'       => 'array',
        'progress'       => 'integer',
        'total_rows'     => 'integer',
        'processed_rows' => 'integer',
        'tanggal_data'   => 'date',
    ];
}
