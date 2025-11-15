<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class EncouragementPoint extends Model
{
    protected $table = 'encouragement_points';

    protected $fillable = [
        'user_id',
        'scope_type',  // morph type
        'scope_id',    // morph id
        'type',
        'points',
    ];

    protected $casts = [
        'points' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Supported types
     */
    public const TYPE_SCANNER = 'scanner';
    public const TYPE_GAME = 'game';
    public const TYPE_REFERRAL_START = 'referral_start';
    public const TYPE_REFERRAL_REGISTER = 'referral_register';
    public const TYPE_SECRET_NUMBER = 'secret_number';

    /**
     * Type helpers
     */
    public function isScanner(): bool
    {
        return $this->type === self::TYPE_SCANNER;
    }

    public function isGame(): bool
    {
        return $this->type === self::TYPE_GAME;
    }

    public function isReferral(): bool
    {
        return in_array($this->type, [self::TYPE_REFERRAL_START, self::TYPE_REFERRAL_REGISTER], true);
    }

    public function isSecretNumber(): bool
    {
        return $this->type === self::TYPE_SECRET_NUMBER;
    }

    /**
     * Morph relation generic accessor
     */
    public function scope(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Specific receipt morph relation (if needed)
     */
    public function receipt(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'scope_type', 'scope_id');
    }

    /**
     * User relation
     */
    public function user()
    {
        return $this->belongsTo(UsersCache::class, 'user_id', 'user_id');
    }

    /**
     * Auto-set user_id from morph relation if missing
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (!$model->user_id && $model->scope && property_exists($model->scope, 'user_id')) {
                $model->user_id = $model->scope->user_id;
            }
        });
    }
    /**
     * Berilgan user_id va type(s) bo'yicha:
     * - userning jami ballari (integer)
     * - users_cache.dan name (fallback bilan)
     * - shu filtrlarga binoan reytingdagi o'rni (rank, 1 = eng yuqori)
     *
     * @param int $userId
     * @param string|array|null $types  Single type or array of types. Null = hammasi.
     * @param string $nameFallback
     * @return array
     */
    public static function getUserTotalAndRank(
        int $userId,
        $types = null,
        string $nameFallback = "Noma'lum user",
        ?string $from = null,
        ?string $to = null
    ): array {
        // 1️⃣ Typelarni normalize qilish
        if ($types !== null) {
            $types = (array) $types;
            $allowed = [
                'scanner',
                'game',
                'referral_start',
                'referral_register',
                'secret_number',
            ];
            $types = array_map('strtolower', $types);
            $types = array_intersect($types, $allowed);
            if (empty($types)) {
                $types = null;
            }
        }

        // 2️⃣ Vaqt filtrlari tayyorlash
        $fromTime = $from ? now()->parse($from) : null;
        $toTime = $to ? now()->parse($to) : null;

        // 3️⃣ Foydalanuvchining jami balli
        $userTotal = (int) DB::table('encouragement_points')
            ->when($types, fn($q) => $q->whereIn('scope_type', $types))
            ->when($fromTime, fn($q) => $q->where('created_at', '>=', $fromTime))
            ->when($toTime, fn($q) => $q->where('created_at', '<=', $toTime))
            ->where('user_id', $userId)
            ->sum('points');

        // 4️⃣ Aggregatsiya: barcha foydalanuvchilar bo‘yicha total points
        $agg = DB::table('encouragement_points')
            ->select('user_id', DB::raw('SUM(points) as total_points'))
            ->when($types, fn($q) => $q->whereIn('scope_type', $types))
            ->when($fromTime, fn($q) => $q->where('created_at', '>=', $fromTime))
            ->when($toTime, fn($q) => $q->where('created_at', '<=', $toTime))
            ->groupBy('user_id');

        // 5️⃣ Rank va total_users hisoblash
        $rankData = DB::table(DB::raw("({$agg->toSql()}) as t"))
            ->mergeBindings($agg)
            ->selectRaw('
            COUNT(*) as total_users,
            SUM(CASE WHEN t.total_points > ? THEN 1 ELSE 0 END) as higher_count
        ', [$userTotal])
            ->first();

        // Agar foydalanuvchining balli 0 bo‘lsa, rank = ball to‘plaganlar soni + 1
        $rank = ($userTotal === 0)
            ? ($rankData->higher_count + 1)  // balli foydalanuvchilar soni + 1
            : ($rankData->higher_count + 1);

        return [
            'user_id' => $userId,
            'name' => UsersCache::where('user_id', $userId)->value('name') ?? $nameFallback,
            'total_points' => $userTotal,
            'rank' => $rankData->total_users > 0 ? $rank : null,
            'total_users' => $rankData->total_users,
        ];
    }

    public static function getUserTotalPoints(int $userId, $types = null, string $nameFallback = "Noma'lum user")
    {
        // 1️⃣ Typelarni normalize qilish
        if ($types !== null) {
            $types = (array) $types;
            $allowed = [
                'scanner',
                'game',
                'referral_start',
                'referral_register',
                'secret_number',
            ];
            $types = array_map('strtolower', $types);
            $types = array_intersect($types, $allowed);
            if (empty($types)) {
                $types = null;
            }
        }

        // 2️⃣ Foydalanuvchining jami ballarini olish
        $totalPoints = (int) DB::table('encouragement_points')
            ->when($types, fn($q) => $q->whereIn('scope_type', $types))
            ->where('user_id', $userId)
            ->sum('points');

        // 3️⃣ Natijani qaytarish
        return $totalPoints;
    }


    public static function getTopUsersWithRank(
        $types = null,
        ?string $from = null,
        ?string $to = null,
        int $limit = 100
    ): array {
        // 1️⃣ Typelarni normalize qilish
        if ($types !== null) {
            $types = (array) $types;
            $allowed = [
                'scanner',
                'game',
                'referral_start',
                'referral_register',
                'secret_number',
            ];
            $types = array_map('strtolower', $types);
            $types = array_intersect($types, $allowed);
            if (empty($types)) {
                $types = null;
            }
        }

        // 2️⃣ Vaqt filtrlari tayyorlash
        $fromTime = $from ? now()->parse($from) : null;
        $toTime = $to ? now()->parse($to) : null;

        // 3️⃣ Aggregatsiya: userlar bo‘yicha total points
        $agg = DB::table('encouragement_points')
            ->select('user_id', DB::raw('SUM(points) as total_points'))
            ->when($types, fn($q) => $q->whereIn('scope_type', $types))
            ->when($fromTime, fn($q) => $q->where('created_at', '>=', $fromTime))
            ->when($toTime, fn($q) => $q->where('created_at', '<=', $toTime))
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->limit($limit);

        $topUsers = DB::table(DB::raw("({$agg->toSql()}) as t"))
            ->mergeBindings($agg)
            ->select('t.user_id', 't.total_points')
            ->get()
            ->map(function ($row, $index) {
                $row->rank = $index + 1; // 1-dan boshlab rank
                $row->name = UsersCache::where('user_id', $row->user_id)->value('name') ?? "Noma'lum user";
                return $row;
            });

        return $topUsers->toArray();
    }
}
