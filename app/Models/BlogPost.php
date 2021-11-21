<?php

namespace App\Models;

use App\Jobs\CreateOgImageJob;
use Illuminate\Support\Str;
use App\Models\Enums\BlogPostStatus;
use App\Exceptions\AlreadyPublished;
use Spatie\Feed\{FeedItem, Feedable};
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BlogPostController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Builder, Model, Relations\HasMany};

class BlogPost extends Model implements Feedable
{
    use HasFactory;

    protected $casts = [
        'date' => 'datetime',
        'likes' => 'integer',
        'status' => BlogPostStatus::class,
    ];

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        self::creating(function (BlogPost $post) {
            if (! $post->slug) {
                $post->slug = Str::slug($post->title);
            }
        });

        self::saved(function (BlogPost $post) {
            if ($post->wasRecentlyCreated || $post->wasChanged('title')) {
//                CreateOgImageJob::dispatch($post);
                return;
            }
        });
    }

    public function postLikes(): HasMany
    {
        return $this->hasMany(BlogPostLike::class);
    }

    public function publish(): self
    {
        if ($this->status->equals(BlogPostStatus::PUBLISHED())) {
            throw AlreadyPublished::new();
        }

        $this->update([
            'status' => BlogPostStatus::PUBLISHED(),
        ]);

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->status->equals(BlogPostStatus::PUBLISHED());
    }

    public function isLikedBy(?string $likerUuid): bool
    {
        if ($likerUuid === null) {
            return false;
        }

        return BlogPostLike::query()
            ->where('liker_uuid', $likerUuid)
            ->where('blog_post_id', $this->id)
            ->exists();
    }

    public function addLikeBy(string $likerUuid): void
    {
        BlogPostLike::create([
            'blog_post_id' => $this->id,
            'liker_uuid' => $likerUuid,
        ]);

        $this->likes += 1;

        $this->save();
    }

    public function removeLikeBy(string $likerUuid): void
    {
        BlogPostLike::where([
            'blog_post_id' => $this->id,
            'liker_uuid' => $likerUuid,
        ])->delete();

        $this->likes -= 1;

        $this->save();
    }

    public function toFeedItem(): FeedItem
    {
        return FeedItem::create()
            ->id($this->id)
            ->title($this->title)
            ->updated($this->updated_at)
            ->link(action([BlogPostController::class, 'show'], $this->slug))
            ->summary($this->title)
            ->authorName($this->author);
    }

    public static function getFeedItems()
    {
        return self::all();
    }

    public function scopeWherePublished(Builder $builder): void
    {
        $builder
            ->where('status', BlogPostStatus::PUBLISHED())
            ->whereDate('date', '<', now()->addDay());
    }

    public function ogImagePath(): string
    {
        return "blog/{$this->slug}.png";
    }

    public function ogImageUrl(): string
    {
        return Storage::disk('public')->url($this->ogImagePath());
    }

    public function saveOgImage(string $file)
    {
        Storage::disk('public')->put($this->ogImagePath(), $file);
    }
}
