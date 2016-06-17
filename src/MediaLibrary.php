<?php namespace Inoplate\Media;

use Roseffendi\Authis\Resource;
use Roseffendi\Authis\User;
use Illuminate\Database\Eloquent\Model;

class MediaLibrary extends Model implements Resource
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'media_libraries';

    /**
     * Determine if auto increment is disabled
     * 
     * @var boolean
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'name'];

    /**
     * Define user relation
     * 
     * @return Model
     */
    public function user()
    {
        return $this->belongsTo('Inoplate\Account\User');
    }

    /**
     * Define many to many user relation
     * 
     * @return Model
     */
    public function users()
    {
        return $this->belongsToMany('Inoplate\Account\User', 'media_libraries_shares', 'media_id', 'user_id');
    }

    /**
     * Determine if resource belongs to user
     * 
     * @param  User    $user
     * @return boolean       [description]
     */
    public function isBelongsTo(User $user)
    {
        return $user->id() === $this->user_id;
    }
}