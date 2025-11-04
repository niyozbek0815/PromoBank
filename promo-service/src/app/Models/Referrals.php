<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Referral Model
 *
 * Maqsad:
 * - Kim kimni taklif qilganini saqlaydi
 * - Holatlarni (started → registered → activated) boshqaradi
 * - Ball (EncouragementPoint) bilan bog‘laydi
 *
 * @property int $id
 * @property int $referrer_id
 * @property int|null $referred_user_id
 * @property string $referrer_chat_id
 * @property string|null $referred_chat_id
 * @property string|null $referred_username
 * @property string $status
 * @property int $awarded_points
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Referrals extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'referrer_chat_id',
        'referred_chat_id',
        'referred_username',
        'status',
        'awarded_points',
    ];

    protected $casts = [
        'referrer_id' => 'integer',
        'referred_user_id' => 'integer',
        'awarded_points' => 'integer',
    ];

    // --- Holatlar
    public const STATUS_STARTED = 'started';
    public const STATUS_REGISTERED = 'registered';
    public const STATUS_ACTIVATED = 'activated';

    /* -------------------------------------------------
       RELATIONLAR
    --------------------------------------------------*/

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function encouragementPoint()
    {
        return $this->hasOne(EncouragementPoint::class, 'scope_id')
            ->where('scope_type', self::class);
    }

    /* -------------------------------------------------
       ASOSIY FUNKSIYALAR
    --------------------------------------------------*/

    /**
     * /start bosilganda yangi referral yozuvini yaratadi.
     * @return static
     */
    public static function recordStart(
        int $referrerId,
        string $referrerChatId,
        string $referredChatId,
        ?string $referredUsername = null
    ) {
        if ($referrerChatId === $referredChatId) {
            throw new \InvalidArgumentException('Foydalanuvchi o‘zini taklif qila olmaydi.');
        }

        return self::firstOrCreate(
            [
                'referrer_id' => $referrerId,
                'referred_chat_id' => $referredChatId,
            ],
            [
                'referrer_chat_id' => $referrerChatId,
                'referred_username' => $referredUsername,
                'status' => self::STATUS_STARTED,
            ]
        );
    }
    public function givePointsWithoutActivation(int $points, string $type = 'referral_start')
    {
        return DB::transaction(function () use ($points, $type) {
            // Encourage point yaratish
            $ep = EncouragementPoint::create([
                'user_id' => $this->referrer_id,
                'scope_type' => self::class,
                'scope_id' => $this->id,
                'type' => $type,
                'points' => $points,
            ]);

            // referral statusini o'zgartirmaymiz
            return $ep;
        });
    }
public function registerWithPoints(int $userId, string $username, int $points)
{
    return DB::transaction(function () use ($userId, $username, $points) {
        // 1️⃣ statusni START yoki REGISTER ga update qilamiz
        if ($this->status === self::STATUS_STARTED) {
            $this->update([
                'referred_user_id' => $userId,
                'referred_username' => $username,
                'status' => self::STATUS_REGISTERED,
            ]);
        }

        // 2️⃣ Referrer balance ga ball qo‘shamiz
        $balance = UserPointBalance::firstOrCreate(
            ['user_id' => $this->referrer_id],
            ['balance' => 0]
        );
        $balance->increment('balance', $points);

        // 3️⃣ Encourage point yaratamiz (har register chaqirilganda alohida)
        $encouragement = EncouragementPoint::create([
            'user_id' => $this->referrer_id,
            'scope_type' => self::class,
            'scope_id' => $this->id,
            'type' => 'referral_register',
            'points' => $points,
        ]);

        return compact('balance');
    });
}
    /**
     * Foydalanuvchi ro‘yxatdan o‘tganda holatni yangilaydi.
     */
    public function register(int $userId, string $username)
    {
        if ($this->status !== self::STATUS_STARTED) {
            return;
        }

        $this->update([
            'referred_user_id' => $userId,
            'referred_username'=>$username,
            'status' => self::STATUS_REGISTERED,
        ]);
    }

    /**
     * Mukofot (promobal) berilgan holatni belgilaydi.
     * Transaction ichida EncouragementPoint yozadi.
     */
    public function activate(int $points, string $type = 'referral_start')
    {
        if ($this->status === self::STATUS_ACTIVATED) {
            Log::warning('Referral already activated', ['id' => $this->id]);
            return $this->encouragementPoint;
        }

        return DB::transaction(function () use ($points, $type) {
            $ep = EncouragementPoint::create([
                'user_id' => $this->referrer_id,
                'scope_type' => self::class,
                'scope_id' => $this->id,
                'type' => $type,
                'points' => $points,
            ]);

            $this->update([
                'status' => self::STATUS_ACTIVATED,
                'awarded_points' => $points,
            ]);

            return $ep;
        });
    }

    /**
     * Qulay helper: referal qaysi bosqichda ekanini tekshirish.
     */
    public function isStarted()
    {
        return $this->status === self::STATUS_STARTED;
    }

    public function isRegistered()
    {
        return $this->status === self::STATUS_REGISTERED;
    }

    public function isActivated()
    {
        return $this->status === self::STATUS_ACTIVATED;
    }
}
