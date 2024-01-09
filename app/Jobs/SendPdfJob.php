<?php

namespace App\Jobs;

use App\Models\StoryPage;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\StoryRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use OpenAI;

class SendPdfJob implements ShouldQueue
{
    protected $storyRequest;

    public function __construct(StoryRequest $storyRequest)
    {
        $this->storyRequest = $storyRequest;
    }

    public function handle()
    {
        $prompt = $this->preparePrompt($this->storyRequest);

        // Call ChatGPT API
        $responseText = $this->callChatGptApi($prompt);

        $this->storyRequest->chatgpt_response = $responseText;
        $this->storyRequest->save();
        $this->storyRequest = $this->extractPagesFromResponse($this->storyRequest);
        $this->storyRequest = $this->generateImages($this->storyRequest);

        $storyName = "story_' . $this->storyRequest->id";
        $pdfFilePath = 'temp/story_' . $this->storyRequest->id . '.pdf';

        $pdf = app('dompdf.wrapper');

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => FALSE,
                'verify_peer_name' => FALSE,
                'allow_self_signed' => TRUE
            ]
        ]);
        $pdf->getDomPDF()->setHttpContext($context);
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        // Assuming storyPages is a relationship on storyRequest
        $storyPages = $this->storyRequest->storyPages;
        $pdf->loadView('pdf.story', compact('storyPages'));
        $filename = 'story_' . $this->storyRequest->id . '.pdf';
        // Save PDF to a file or attach to email as needed
        Storage::put($pdfFilePath, $pdf->output());
        try {
            Storage::disk('do_spaces')->put($filename, $pdf->output(), 'public');
        } catch (\Exception $e) {
            \Log::error('Error uploading to DigitalOcean Spaces: ' . $e->getMessage());
        }
        Storage::disk('do_spaces')->put("$storyName.pdf", $pdf->output());

        // Generate a downloadable link
        $downloadLink = Storage::disk('do_spaces')->url($filename);

        // Send the download link via email
        $email = new StoryPdfMail($storyName, $downloadLink);
        Mail::to($this->storyRequest->email)->send($email);

        // Clean up and delete the local PDF file
        Storage::disk('local')->delete($pdfFilePath);
    }

    private function preparePrompt(StoryRequest $storyRequest)
    {
        $pageNumbers = $storyRequest->page_number;
        $gradeLevel = $storyRequest->gradeLevel->name; // Assuming GradeLevel has a 'name' field
        $subject = $storyRequest->subject;
        $description = $storyRequest->description;
        $language = $storyRequest->language;

        // Retrieve sight words from the relationship
        $sightWordsString = $storyRequest->sightWords->pluck('word')->implode(', ');

        return "Write a {$pageNumbers}-page {$gradeLevel} level reading story in $language that includes {$subject} and {$description}. The output should just be Page Number: followed by text and nothing else. The first page should be the title page.  Please include the words {$sightWordsString}.";

    }

    public function extractPagesFromResponse(StoryRequest $storyRequest) {
        $responseText = $storyRequest->chatgpt_response;
        $pages = explode("Page ", $responseText);
        array_shift($pages);
        $grade_level = $storyRequest->gradeLevel->name;
        foreach ($pages as $page) {
            if (preg_match('/^(\d+)\D?(.*?)$/s', $page, $matches)) {
                $pageNumber = $matches[1];
                $pageContent = trim($matches[2]);
            } else {
                // Handle cases where the pattern does not match
                $pageNumber = null;
                $pageContent = trim($page); // Default to the entire page content
            }
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
        $style = $storyRequest->style;
        foreach ($storyRequest->storyPages as $page) {
            try {
                $content = $page->content;
                $prompt = "in the style of $style. $content";
                $imageResponse = $openai->images()->create([
                    'model' => "dall-e-3",
                    'prompt' => $prompt,
                    'size' => "1024x1024",
                    'quality' => "standard",
                    'n' => 1,
                ]);
                $imageUrl = $imageResponse->data[0]->url; // Adjust based on actual API response
                $page->image_url = $imageUrl;
                $page->save();
                $url = $this->downloadAndUploadImage($page);
                //  $page->spaces_image_url = Storage::disk('do_spaces')->url($name);
                $page->spaces_image_url = $url;
                $page->save();
            }
            catch (\Exception $e) {
                Log::error('DALL-E API Call Failure: ' . $e->getMessage());
            }
        }
        return $storyRequest;
    }

    public function downloadAndUploadImage(StoryPage $page):string
    {
        $client = new Client();
        $response = $client->get($page->image_url);
        $requestId = $page->story_request_id;
        if ($response->getStatusCode() == 200) {
            $imageContent = $response->getBody()->getContents();

            // Generate a unique name for the image file
            $imageName = "$requestId/" . uniqid() . '.png';

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

}

