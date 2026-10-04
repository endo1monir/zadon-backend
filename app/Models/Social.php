<?php

namespace App\Models;

use Database\Factories\SocialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name_ar', 'name_en', 'icon', 'link'])]
class Social extends Model
{
    /** @use HasFactory<SocialFactory> */
    use HasFactory;

    /**
     * The public URL of the uploaded icon, or null when none was set.
     */
    public function getIconUrlAttribute(): ?string
    {
        return api_image($this->icon);
    }
}
