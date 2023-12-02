<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryPage extends Model
{
    use HasFactory;


    protected $fillable = ['story_request_id', 'page_number', 'content', 'image_url', 'spaces_image_url'];

    public function storyRequest()
    {
        return $this->belongsTo(StoryRequest::class);
    }
}
