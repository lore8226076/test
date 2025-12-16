<?php
namespace App\Models;

use App\Models\MapFavorite;
use App\Models\MapLike;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UserMaps extends BaseModel
{
    use SoftDeletes;
    // protected $connection = 'connection-name';
    protected $table = 'user_maps';
    // protected $primaryKey = 'id';
    // use SoftDeletes;

    protected $hidden = [
        'deleted_at',
        'play_time',
        'player_num',
    ];

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'publish_at' => 'timestamp',
        'map_tags'   => 'array',
    ];

    protected $appends = ['map_data', 'photo_url'];

    protected $_virtual = ['map_data', 'photo_url'];

    public function getMapDataAttribute()
    {
        if (isset($this->load_map_data) && $this->load_map_data == false) {
            return '';
        }
        $disk = config('services.filesystem.disk');
        if ($this->map_file_path && $this->map_file_name) {
            $filePath = $this->map_file_path . $this->map_file_name;
            if (Storage::disk($disk)->exists($filePath)) {
                // 如果存在，讀取檔案內容並回傳
                return Storage::disk($disk)->get($filePath);
            }
        }
        return '';
    }

    public function getPhotoUrlAttribute()
    {
        $disk = config('services.filesystem.disk');
        if ($this->photo_file_path) {
            // return Storage::disk($disk)->url($this->photo_file_path);
            $url = config('services.filesystem.R2_API_PUBLIC_URL') . $this->photo_file_path;
            if (! $url) {
                return $this->photo_file_path;
            }
            return $url;
        }
        return $this->photo_file_path;
    }

    protected static function boot()
    {
        parent::boot();
        // creating, created, saving, saved, deleting, deleted

        static::creating(function ($entity) {
            // creating event
        });

        static::saving(function ($entity) {
            $disk = config('services.filesystem.disk');

            // 刪除舊的 map_file（只要 map_file_name 有變更，就刪舊檔）
            if ($entity->isDirty('map_file_name') && $entity->getOriginal('map_file_path') && $entity->getOriginal('map_file_name')) {
                $filePath = $entity->getOriginal('map_file_path') . $entity->getOriginal('map_file_name');
                if (Storage::disk($disk)->exists($filePath)) {
                    Storage::disk($disk)->delete($filePath);
                }
            }

            // 刪除舊的 photo_file（只要 photo_file_path 有變更，就刪舊檔）
            if ($entity->isDirty('photo_file_path') && $entity->getOriginal('photo_file_path')) {
                $filePath = $entity->getOriginal('photo_file_path');
                if (Storage::disk($disk)->exists($filePath)) {
                    Storage::disk($disk)->delete($filePath);
                }
            }

            // 首次公開時 設定 publish_at 時間
            if ($entity->isDirty('is_publish') && $entity->getOriginal('is_publish') == 0 && $entity->is_publish == 1) {
                $entity->publish_at = Carbon::now()->format('Y-m-d H:i:s');
            }

            // 設定為家園時 把其他地圖的 is_home 清除
            if ($entity->isDirty('is_home') && $entity->getOriginal('is_home') == 0 && $entity->is_home == 1) {
                UserMaps::where('user_id', $entity->user_id)->update(['is_home' => 0]);
            }
        });

        static::saved(function ($entity) {
        });

        static::deleting(function ($entity) {
            // 有設定 skipDeleteFile 直接跳過刪除
            if (isset($entity->skipDeleteFile) && $entity->skipDeleteFile) {
                return;
            }

            $disk = config('services.filesystem.disk');

            if (! empty($entity->map_file_path) && ! empty($entity->map_file_name)) {
                $filePath = $entity->map_file_path . $entity->map_file_name;
                if (Storage::disk($disk)->exists($filePath)) {
                    Storage::disk($disk)->delete($filePath);
                }
            }
            // 刪除地圖照片
            if (! empty($entity->photo_file_path)) {
                if (Storage::disk($disk)->exists($entity->photo_file_path)) {
                    Storage::disk($disk)->delete($entity->photo_file_path);
                }
            }

            // 刪除地圖按讚, 收藏
            MapLike::where('map_id', $entity->id)->delete();
            MapFavorite::where('map_id', $entity->id)->delete();
        });
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Users', 'user_id');
    }

    public function draft()
    {
        return $this->belongsTo('App\Models\UserMaps', 'draft_id', 'id');
    }

    public function publish()
    {
        return $this->belongsTo('App\Models\UserMaps', 'id', 'draft_id');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Models\MapTag', 'map_tag_maps', 'map_id', 'tag_id');
    }

    public function favorites()
    {
        return $this->hasMany('App\Models\MapFavorite', 'map_id', 'id');
    }
    public function likes()
    {
        return $this->hasMany('App\Models\MapLike', 'map_id', 'id');
    }
}
