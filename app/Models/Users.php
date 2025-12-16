<?php
namespace App\Models;

use App\Models\Follows;
use App\Models\UserCharacter;
use App\Models\UserEquipments;
use App\Models\UserEquipmentSession;
use App\Models\UserInboxEntries;
use App\Models\UserItems;
use App\Models\UserMaps;
use App\Models\UserPatrolReward;
use App\Models\UserPayOrders;
use App\Models\UserPet;
use App\Models\UserSlotEquipment;
use App\Models\UserSurGameFunc;
use App\Models\UserSurGameInfo;
use App\Service\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Users extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, softDeletes;

    protected $table    = 'users';
    protected $fillable = [
        'uid',
        'mac_id',
        'email',
        'account',
        'password',
        'name',
        'gender',
        'cellphone',
        'map_limit',
        'draft_map_limit',
        'introduce',
        'is_active',
        'is_admin',
        'new_give',
        'teaching_square',
        'teaching_level',
        'teaching_name',
        'teaching_task',
        'teaching_mapeditor',
        'teaching_pet',
        'teaching_levelselector',
        'teaching_maplobby',
        'teaching_gacha',
        'teaching_surgame_intro',
        'teaching_main_stage2',
        'teaching_formation_intro',
        'teaching_rank_intro',
        'teaching_paperdoll_intro',
        'teaching_received_costume_coupon',
        'teaching_performed_costume_gacha',
        'teaching_is_role_upgraded',
        'has_edit_map_opening_anim_played',
        'firebase_name',
        'firebase_uid',
        'firebase_provider_id',
        'firebase_access_token',
        'firebase_photo_url',
        'sort',
        'remember_token',
        'updated_name',
        'created_at',
        'updated_at',
        'deleted_at',
        'last_login_time',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password'        => 'hashed',
        'last_login_time' => 'timestamp',
    ];

    protected $appends = [];

    protected $_virtual = [];

    protected static function boot()
    {
        parent::boot();
        // creating, created, saving, saved, deleting, deleted
        static::creating(function ($entity) {
            $entity->uid = \Carbon\Carbon::now()->timestamp;

            $existingEntity = static::where('uid', $entity->uid)->first();
            $suffix         = 1;
            while ($existingEntity) {
                $entity->uid    = \Carbon\Carbon::now()->timestamp + $suffix;
                $existingEntity = static::where('uid', $entity->uid)->first();
                $suffix++;
            }
        });
        static::created(function ($entity) {
            $userEquipment          = new UserEquipments;
            $userEquipment->user_id = $entity->id;
            $userEquipment->save();

            UserService::init_create_give($entity->id);
        });
        static::saving(function ($entity) {
        });
        static::saved(function ($entity) {
        });
        static::deleting(function ($entity) {
        });
    }

    public function userEquipment()
    {
        return $this->hasOne('App\Models\UserEquipments', 'user_id');
    }

    public function userItems()
    {
        return $this->hasMany('App\Models\UserItems', 'user_id');
    }

    public function userMaps()
    {
        return $this->hasMany('App\Models\UserMaps', 'user_id');
    }
    // 追蹤
    public function followers()
    {
        return $this->hasMany(Follows::class, 'following_uid', 'uid');
    }

    public function following()
    {
        return $this->hasMany(Follows::class, 'follower_uid', 'uid');
    }

    // 寵物
    public function pets()
    {
        return $this->hasMany(UserPet::class, 'uid', 'uid');
    }

    // 訂單
    public function orders()
    {
        return $this->hasMany(UserPayOrders::class, 'user_id', 'id');
    }

    // 人物巡邏
    public function patorlRewards()
    {
        return $this->hasOne(UserPatrolReward::class, 'uid', 'uid');
    }

    // surgame 資訊
    public function surgameUserInfo()
    {
        return $this->hasOne(UserSurGameInfo::class, 'uid', 'uid');
    }

    // 使用者功能
    public function surgameUserFuncs()
    {
        return $this->hasMany(UserSurGameFunc::class, 'uid', 'uid');
    }

    // 擁有的裝備
    public function equipmentSessions()
    {
        return $this->hasMany(UserEquipmentSession::class, 'uid', 'uid');
    }

    // 使用者陣位裝備資料
    public function slotEquipments()
    {
        return $this->hasMany(UserSlotEquipment::class, 'uid', 'uid');
    }

    /**
     * 玩家章節記錄
     */
    public function journeyRecords()
    {
        return $this->hasMany(UserJourneyRecord::class, 'uid', 'uid');
    }

    /**
     * 玩家的章節星級挑戰記錄
     */
    public function journeyStarChallenges()
    {
        return $this->hasMany(UserJourneyStarChallenge::class, 'uid', 'uid');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function userCharacters()
    {
        return $this->hasMany(UserCharacter::class, 'uid', 'uid');
    }
    public function userInboxEntries()
    {
        return $this->hasMany(UserInboxEntries::class, 'uid', 'uid');
    }
}
