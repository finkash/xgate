<?php

namespace App\Domain\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMedia extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'post_media';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'file_path',
        'type',
        'display_order',
        'alt_text',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
