<?php

namespace App\Models;

use App\Services\Mail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

/**
 * @property string $id
 * @property int $user_id
 * @property int $topic_id
 * @property ?int $comment_id
 * @property string $type
 * @property bool $active
 * @property bool $sent
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $sent_at
 *
 * @property-read User $user
 * @property-read Topic $topic
 * @property-read Comment $comment
 */
class UserNotification extends Model
{
    protected $keyType = 'string';
    protected $guarded = ['created_at', 'updated_at'];
    protected $casts = [
        'active' => 'boolean',
        'sent' => 'boolean',
        'sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(
            static function (self $record) {
                if (!$record->id) {
                    $record->id = Uuid::uuid4();
                }
            }
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function sendEmail(): ?string
    {
        if (!$this->user->notify) {
            return null;
        }

        $mail = new Mail();
        $data = [
            'lang' => $this->user->lang ?? 'ru',
            'author' => $this->topic->user->toArray(),
            'topic' => $this->topic->toArray(),
        ];
        $data['topic']['link'] = $this->topic->getLink();

        if ($this->comment) {
            $data['user'] = $this->comment->user->toArray();
            $data['comment'] = $this->comment->toArray();
            $data['comment']['link'] = $this->comment->getLink();
        }

        $subject = match ($this->type) {
            'comment-new' => getenv('EMAIL_COMMENT_NEW_' . strtoupper($data['lang'])),
            'comment-reply' => getenv('EMAIL_COMMENT_REPLY_' . strtoupper($data['lang'])),
            'topic-new' => getenv('EMAIL_TOPIC_NEW_' . strtoupper($data['lang'])),
            default => getenv('SITE_NAME'),
        };
        if ($err = $mail->send($this->user->email, $subject, $this->type, $data)) {
            return $err;
        }
        $this->sent = true;
        $this->sent_at = time();
        $this->save();

        return null;
    }
}