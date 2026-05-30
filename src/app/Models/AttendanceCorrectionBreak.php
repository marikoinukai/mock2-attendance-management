<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_request_id',
        'requested_break_start',
        'requested_break_end',
    ];

    public function correctionRequest()
    {
        return $this->belongsTo(AttendanceCorrectionRequest::class, 'correction_request_id');
    }
}
