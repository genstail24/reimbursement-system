<?php

namespace App\Models;

use App\Enums\ReimbursementStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'attachment_path',
        'submitted_at',
        'approved_at'
    ];

    protected $casts = [
        'status' => ReimbursementStatusEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
