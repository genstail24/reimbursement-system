<?php

namespace App\Models;

use App\Enums\ReimbursementStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Reimbursement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'amount',
        'status',
        'approval_reason',
        'attachment_path',
        'submitted_at',
        'approved_at'
    ];

    protected $casts = [
        'status' => ReimbursementStatusEnum::class,
    ];

    protected $appends = [
        'attachment_url',
    ];

    public function getAttachmentUrlAttribute()
    {
        if (!$this->attachment_path) {
            return null;
        }

        if (str_starts_with($this->attachment_path, 'http://') || str_starts_with($this->attachment_path, 'https://')) {
            return $this->attachment_path;
        }

        return url(Storage::url($this->attachment_path));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
