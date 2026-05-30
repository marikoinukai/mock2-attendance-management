<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_record_id',
        'user_id',
        'requested_clock_in',
        'requested_clock_out',
        'requested_comment',
        'status',
        'approved_admin_id',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedAdmin()
    {
        return $this->belongsTo(User::class, 'approved_admin_id');
    }

    public function correctionBreaks()
    {
        return $this->hasMany(AttendanceCorrectionBreak::class, 'correction_request_id');
    }
}
