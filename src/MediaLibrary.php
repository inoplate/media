<?php namespace Inoplate\Media;

use Illuminate\Database\Eloquent\Model;

class MediaLibrary extends Model
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
        return $this->belongsToMany('Inoplate\Account\User', 'media_libraries_shares', 'user_id', 'media_id');
    }
}