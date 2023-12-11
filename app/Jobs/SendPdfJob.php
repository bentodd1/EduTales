<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\StoryRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendPdfJob implements ShouldQueue
{
    protected $storyRequest;

    public function __construct(StoryRequest $storyRequest)
    {
        $this->storyRequest = $storyRequest;
    }

    public function handle()
    {
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
}

