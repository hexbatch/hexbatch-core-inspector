<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @property int id
 * @property int flagged_user_id
 * @property string flag_name
 * @property string created_at
 * @property string updated_at
 */
class UserFlag extends Model
{

    protected $table = 'user_flags';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'flagged_user_id',
        'flag_name',
    ];

    const FLAG_ADMIN = 'admin';
}
