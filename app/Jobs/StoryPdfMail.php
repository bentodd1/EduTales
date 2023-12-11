<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StoryPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $pdfFilepath;

    public function __construct($pdfFilepath, private string $downloadLink)
    {
        $this->pdfFilepath = $pdfFilepath;
    }

    public function build()
    {
        return $this->view('mail.story_pdf', ['storyTitle' => 'my title', 'downloadLink' => $this->downloadLink])
            ->subject('Your Story PDF');
    }
}
