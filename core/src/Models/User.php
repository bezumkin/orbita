<?php

namespace App\Models;

use App\Services\Mail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use RuntimeException;
use Vesp\Helpers\Jwt;

/**
 * @property int $id
 * @property int $role_id
 * @property ?int $avatar_id
 * @property string $username
 * @property string $password
 * @property ?string $fullname
 * @property ?string $email
 * @property ?string $phone
 * @property bool $active
 * @property bool $blocked
 * @property bool $notify
 * @property ?string $lang
 * @property ?string $reset_password
 * @property ?Carbon $reset_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $active_at
 *
 * @property-read File $avatar
 * @property-read UserToken[] $tokens
 * @property-read VideoUser[] $userVideos
 * @property-read TopicView[] $views
 * @property-read UserNotification[] $notifications
 * @property-read Subscription[] $subscriptions
 * @property-read Subscription $currentSubscription
 * @property-read Payment[] $payments
 */
class User extends \Vesp\Models\User
{
    protected $fillable = [
        'role_id',
        'username',
        'password',
        'fullname',
        'email',
        'phone',
        'active',
        'blocked',
        'notify',
        'lang',
    ];
    protected $hidden = ['password', 'reset_password', 'reset_at'];
    protected $casts = [
        'active' => 'bool',
        'blocked' => 'bool',
        'notify' => 'bool',
        'reset_at' => 'datetime',
        'active_at' => 'datetime',
    ];

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(UserToken::class);
    }

    public function userVideos(): HasMany
    {
        return $this->hasMany(VideoUser::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(TopicView::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('active', true)
            ->where('active_until', '>', time());
    }

    public function setAttribute($key, $value)
    {
        if ($key === 'reset_password' && $value !== null) {
            $value = password_hash($value, PASSWORD_DEFAULT);
        }

        return parent::setAttribute($key, $value);
    }

    public function resetPassword($length = 20): string
    {
        $tmp = bin2hex(random_bytes($length));
        $this->reset_password = $tmp;
        $this->reset_at = time();
        $this->timestamps = false;
        $this->save();

        return $tmp;
    }

    public function activatePassword(string $password): bool
    {
        if (!password_verify($password, $this->reset_password)) {
            return false;
        }
        $this->active = true;
        $this->reset_password = null;
        $this->reset_at = null;
        $this->timestamps = false;
        $this->save();

        return true;
    }

    public function createToken(?string $ip = null): UserToken
    {
        /** @var UserToken $token */
        $token = $this->tokens()->create([
            'token' => Jwt::makeToken($this->id),
            'valid_till' => date('Y-m-d H:i:s', time() + getenv('JWT_EXPIRE')),
            'ip' => $ip,
        ]);

        // Limit active tokens
        if ($max = getenv('JWT_MAX')) {
            $c = $this->tokens()->where('active', true);
            if ($c->count() > $max && $res = $c->orderBy('updated_at')->orderBy('created_at')->first()) {
                $res->update(['active' => false]);
            }
        }

        return $token;
    }

    public function fillData($data): User
    {
        array_walk($data, static function (&$val) {
            if (is_string($val)) {
                $val = trim($val);
                if (empty($val)) {
                    $val = null;
                }
            }
        });

        if (empty($data['username'])) {
            throw new RuntimeException('errors.user.no_username');
        }
        if (empty($data['fullname'])) {
            throw new RuntimeException('errors.user.no_fullname');
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('errors.user.no_email');
        }

        $data['username'] = filter_var($data['username'], FILTER_SANITIZE_EMAIL) ?: md5($data['email']);
        $data['fullname'] = filter_var($data['fullname'], FILTER_SANITIZE_STRING);

        $c = self::query();
        if ($this->id) {
            $c->where('id', '!=', $this->id);
        }

        if ((clone $c)->where('username', $data['username'])->count()) {
            throw new RuntimeException('errors.user.username_exists');
        }

        if ((clone $c)->where('email', $data['email'])->count()) {
            throw new RuntimeException('errors.user.email_exists');
        }

        $this->fill($data);

        return $this;
    }


    public static function createUser(array $properties): User
    {
        $user = new User();
        $properties['active'] = false;
        $properties['role_id'] = getenv('REGISTER_ROLE_ID') ?: 3;
        if (empty($properties['password'])) {
            $properties['password'] = bin2hex(random_bytes(20));
        }
        $user->fillData($properties);
        $user->save();

        return $user;
    }

    public function sendEmail(string $subject, string $tpl, ?array $data = []): ?string
    {
        return (new Mail())->send($this->email, $subject, $tpl, $data);
    }
}
