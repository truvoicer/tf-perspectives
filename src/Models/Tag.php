<?php
// packages/truvoicer/tf-perspectives/src/Models/Tag.php

namespace Truvoicer\TfPerspectives\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected $table = 'tags';

    public function perspectives(): BelongsToMany
    {
        return $this->belongsToMany(Perspective::class, 'perspective_tag');
    }
}
