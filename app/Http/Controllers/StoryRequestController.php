<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use App\Models\SightWord;
use App\Models\StoryPage;
use App\Models\StoryRequest;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenAI;

use Barryvdh\DomPDF\PDF;
use function PHPUnit\Framework\throwException;


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
            'sight_words' => 'nullable|string'
        ]);

        // Create a new StoryRequest instance and save
        $storyRequest = new StoryRequest([
            'grade_level_id' => $validatedData['grade_level_id'],
            'subject' => $validatedData['subject'],
            'description' => $validatedData['description'],
            'email' => $validatedData['email'],
            'page_number' => $validatedData['page_number']
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

        $prompt = $this->preparePrompt($storyRequest);

        // Call ChatGPT API
        $responseText = $this->callChatGptApi($prompt);

        $storyRequest->chatgpt_response = $responseText;
        $storyRequest->save();
        $storyRequest = $this->extractPagesFromResponse($storyRequest);
        $storyRequest = $this->generateImages($storyRequest);
        return $this->generatePdf($storyRequest);

        // Redirect or return response
       // return redirect('/story-request')->with('success', 'Story request submitted successfully!');
    }

    private function preparePrompt($storyRequest)
    {
        $pageNumbers = $storyRequest->page_number;
        $gradeLevel = $storyRequest->gradeLevel->name; // Assuming GradeLevel has a 'name' field
        $subject = $storyRequest->subject;
        $description = $storyRequest->description;

        // Retrieve sight words from the relationship
        $sightWordsString = $storyRequest->sightWords->pluck('word')->implode(', ');

        return "Write a {$pageNumbers}-page {$gradeLevel} level reading story that includes {$subject} and {$description}. The output should just be Page Number: followed by text and nothing else. The first page should be the title page.  Please include the words {$sightWordsString}.";

    }

    private function callChatGptApi($prompt)
    {
        $openai =  OpenAI::client(env('OPENAI_API_KEY'));

        $messages[] = ['role' => 'user', 'content' => $prompt];

        $response = $openai->chat()->create([
            "model" => "gpt-4-1106-preview",
            "messages" => $messages
        ]);

        return $response->choices[0]->message->content ?? 'No response';
    }

    public function extractPagesFromResponse(StoryRequest $storyRequest) {
        $responseText = $storyRequest->chatgpt_response;
        $pages = explode("Page ", $responseText);
        array_shift($pages); // Remove the first element which is the intro text

        foreach ($pages as $page) {
            preg_match('/^(\d+):(.*)$/s', $page, $matches);
            $pageNumber = $matches[1] ?? null;
            $pageContent = trim($matches[2] ?? '');

            if ($pageNumber && $pageContent) {
                $storyPage = new StoryPage([
                    'story_request_id' => $storyRequest->id,
                    'page_number' => $pageNumber,
                    'content' => $pageContent
                ]);
                $storyPage->save();
            }
        }
        return $storyRequest;
    }

    public function generateImages(StoryRequest $storyRequest) {
        $openai =  OpenAI::client(env('OPENAI_API_KEY'));
        foreach ($storyRequest->storyPages as $page) {
                $imageResponse = $openai->images()->create([
                    'model' => "dall-e-3",
                    'prompt' => $page->content,
                    'size' => "1024x1024",
                    'quality' => "standard",
                    'n' => 1,
                ]);
                $imageUrl = $imageResponse->data[0]->url; // Adjust based on actual API response
                $page->image_url = $imageUrl;
                $page->save();
                $url = $this->downloadAndUploadImage($page->image_url);
              //  $page->spaces_image_url = Storage::disk('do_spaces')->url($name);
                $page->spaces_image_url = $url;
                $page->save();
        }
        return $storyRequest;
    }

    public function downloadAndUploadImage($imageUrl):string
    {
        $client = new Client();
        $response = $client->get($imageUrl);

        if ($response->getStatusCode() == 200) {
            $imageContent = $response->getBody()->getContents();

            // Generate a unique name for the image file
            $imageName = 'images/' . uniqid() . '.png';

            // Save the image to DigitalOcean Spaces
            Storage::disk('do_spaces')->put($imageName, $imageContent);

            // Construct the URL
            $spacesUrl = config('filesystems.disks.do_spaces.url');
            $fullImageUrl = rtrim($spacesUrl, '/') . '/' . ltrim($imageName, '/');


            return $fullImageUrl; // Returns the path of the image in the Spaces bucket
        }
        else {
            throw new \Exception('Can not save');
        }
        // Handle the error or throw an exception
    }

    public function generatePdf(StoryRequest $storyRequest)
    {
        $storyPages = StoryPage::where('story_request_id', $storyRequest->id)->get();

        $pdf = app('dompdf.wrapper');

        $pdf = $pdf->loadView('pdf.story', compact('storyPages'));

        return $pdf->download('story.pdf');
    }
}
