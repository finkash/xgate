<?php

namespace App\Domain\IdentityAndAccess\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'bio',
        'avatar_path',
        'cover_path',
        'location',
        'website',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
