<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SightWord extends Model
{
    use HasFactory;

    protected $fillable = ['story_request_id', 'word'];

    public function storyRequest()
    {
        return $this->belongsTo(StoryRequest::class);
    }
}
