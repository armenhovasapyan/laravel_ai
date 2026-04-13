<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'image_path', 'generated_prompt', 'original_filename', 'file_size', 'mime_type'])]
class ImageGeneration extends Model
{
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
