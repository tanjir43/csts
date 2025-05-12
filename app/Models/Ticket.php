<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'subject',
        'description',
        'category',
        'priority',
        'status',
        'user_id',
    ];

    protected $casts = [
        'category'  => TicketCategory::class,
        'priority'  => TicketPriority::class,
        'status'    => TicketStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf', 'text/plain', 'application/jpg'])
            ->singleFile()
            ->withResponsiveImages();
    }
}
