<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSubject extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $fillable = [
        'class_room_id',
        'subject_id',
        'user_id'
    ];

    function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id');
    }

    function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
