<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryRequest extends Model
{
    use HasFactory;
    protected $fillable = ['grade_level_id', 'subject', 'description', 'page_number', 'email', 'chatgpt_response', 'language'];

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function sightWords()
    {
        return $this->hasMany(SightWord::class);
    }

    public function storyPages()
    {
        return $this->hasMany(StoryPage::class);
    }

}
