<?php

namespace Preetender\Uploader\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryFile extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fat_gallery_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'filename',
        'size',
        'width',
        'height',
    ];

    /**
     * @return BelongsTo
     */
    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }
}
