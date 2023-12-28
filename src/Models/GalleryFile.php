<?php

namespace Preetender\Uploader\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Preetender\Uploader\Processor;

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
     * @return Attribute
     */
    public function url(): Attribute
    {
        return Attribute::get(
            get: fn () => Processor::disk($this->gallery->disk)->url(
                sprintf('%s/%s', $this->gallery->folder, $this->filename)
            )
        );
    }

    /**
     * @return BelongsTo
     */
    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }
}
