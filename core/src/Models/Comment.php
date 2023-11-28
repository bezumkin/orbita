<?php

namespace App\Models;

use App\Models\Traits\ContentFilesTrait;
use App\Services\Socket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $topic_id
 * @property ?int $parent_id
 * @property array $content
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read User $user
 * @property-read Topic $topic
 * @property-read ?Comment $parent
 * @property-read Comment[] $children
 * @property-read CommentFile[] $contentFiles
 */
class Comment extends Model
{
    use ContentFilesTrait;

    protected $fillable = ['content', 'active'];
    protected $casts = [
        'active' => 'boolean',
        'content' => 'array'
    ];

    protected static function booted(): void
    {
        static::created(
            static function (self $record) {
                if ($record->active) {
                    $record->topic->last_comment_id = $record->id;
                    $record->topic->comments_count = $record->topic->comments()->count();
                    $record->topic->save();

                    Socket::send('comments-latest');
                }
            }
        );

        static::saved(
            static function (self $record) {
                $needUpdate = ($record->active && $record->id > $record->topic->last_comment_id) ||
                    (!$record->active && $record->id === $record->topic->last_comment_id);
                if ($needUpdate) {
                    $record->topic->last_comment_id = $record->topic->getLastCommentId();
                    $record->topic->save();

                    Socket::send('comments-latest');
                }
            }
        );

        static::deleting(
            static function (self $record) {
                /** @var CommentFile $commentFile */
                foreach ($record->contentFiles()->cursor() as $commentFile) {
                    $count = CommentFile::query()
                        ->where('file_id', $commentFile->file_id)->where('comment_id', '!=', $record->id)
                        ->count();
                    if (!$count) {
                        $commentFile->file->delete();
                    }
                }
            }
        );

        static::deleted(
            static function (self $record) {
                $record->topic->last_comment_id = $record->topic->getLastCommentId();
                $record->topic->comments_count = $record->topic->comments()->count();
                $record->topic->save();

                Socket::send('comments-latest');
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function contentFiles(): HasMany
    {
        return $this->hasMany(CommentFile::class, 'comment_id');
    }

    public function getLink(): string
    {
        return $this->topic->getLink() . '#comment-' . $this->id;
    }

    public function prepareOutput(?User $user): array
    {
        $array = $this->toArray();
        /*if (isset($array['user']['payments'])) {
            $array['user']['paid'] = !empty($array['user']['payments']);
            unset($array['user']['payments']);
        }*/
        if (!$this->active && (!$user || !$user->hasScope('comments'))) {
            $array['user_id'] = null;
            $array['user'] = [];
            $array['content'] = '';
        }

        return $array;
    }
}