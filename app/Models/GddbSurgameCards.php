<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbSurgameCards extends Model
{
    use HasFactory;

    protected $table    = 'gddb_surgame_cards';
    public $timestamps  = false;
    protected $fillable = [
        'name',
        'desc',
        'sprite_name',
        'card_type',
        'owner_hero_id',
        'synergy_hero_id',
        'modifiers',
        'num',
    ];

    protected $casts = [
        'name'            => 'string',
        'desc'            => 'string',
        'sprite_name'     => 'string',
        'card_type'       => 'string',
        'owner_hero_id'   => 'integer',
        'synergy_hero_id' => 'integer',
        'modifiers'       => 'string',
        'num'             => 'integer',
    ];
}
