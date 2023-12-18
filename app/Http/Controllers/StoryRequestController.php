<?php

namespace App\Http\Controllers;

use App\Jobs\SendPdfJob;
use App\Models\GradeLevel;
use App\Models\SightWord;
use App\Models\StoryRequest;
use Illuminate\Http\Request;

class StoryRequestController extends Controller
{
    //
    public function create()
    {
        $gradeLevels = GradeLevel::all();
        return view('create_story_request', compact('gradeLevels'));
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'email' => 'required|email',
            'page_number' => 'required|numeric',
            'sight_words' => 'nullable|string',
             'language' => 'nullable|string'
        ]);

        // Create a new StoryRequest instance and save
        $storyRequest = new StoryRequest([
            'grade_level_id' => $validatedData['grade_level_id'],
            'subject' => $validatedData['subject'],
            'description' => $validatedData['description'],
            'email' => $validatedData['email'],
            'page_number' => $validatedData['page_number'],
            'language' => $validatedData['language']
        ]);
        $storyRequest->save();
        // Handle sight words if provided
        if (!empty($validatedData['sight_words'])) {
            $sightWords = explode(',', $validatedData['sight_words']);
            foreach ($sightWords as $word) {
                $sightWord = new SightWord(['word' => trim($word)]);
                $storyRequest->sightWords()->save($sightWord);
            }
        }

        dispatch(new SendPdfJob($storyRequest));

        session()->flash('success', 'Story request submitted successfully!
        You should receive an email within 10 minutes with your story.');

        return redirect('/story-request')->with('success', 'Story request submitted successfully! This may take up 10 minutes to send the email.');
    }


}
