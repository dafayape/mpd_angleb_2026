<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuleDocument extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'rule_documents';

    protected $fillable = [
        'file_name',
        'original_name',
        'file_path',
        'file_size',
        'uploaded_by',
    ];

    /**
     * Manual relationship to User model (cross-database).
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
