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
        'total_rows', 'processed_rows', 'skipped_rows', 'data_lost',
        'error_message', 'metadata',
        'file_size', 'status_file', 'status_etl', 'etl_progress',
    ];

    protected $casts = [
        'metadata'       => 'array',
        'progress'       => 'integer',
        'total_rows'     => 'integer',
        'processed_rows' => 'integer',
        'skipped_rows'   => 'integer',
        'data_lost'      => 'integer',
        'file_size'      => 'integer',
        'etl_progress'   => 'integer',
        'tanggal_data'   => 'date',
    ];
}
