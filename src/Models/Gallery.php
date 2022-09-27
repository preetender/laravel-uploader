<?php

namespace Preetender\Uploader\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Gallery extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fat_galleries';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'folder',
        'disk'
    ];

    /**
     * @return Attribute
     */
    protected function size()
    {
        return Attribute::make(
            get: fn () => $this->files()->sum('size')
        );
    }

    /**
     * @return HasMany
     */
    public function files()
    {
        return $this->hasMany(GalleryFile::class);
    }

    /**
     * @return MorphTo
     */
    public function origin()
    {
        return $this->morphTo();
    }
}
