<?php

namespace App\Http\Controllers;

use App\Jobs\SendPdfJob;
use App\Models\StoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    public function showForm()
    {
        return view('generate_pdf_form');
    }

    // Method to handle the form submission and generate the PDF
    public function generatePdf(Request $request)
    {
        $storyRequestId = $request->input('storyRequestId');
        $storyRequest = StoryRequest::where('id', $storyRequestId)->first();
        $storyName = "story_' . $storyRequest->id";
        $pdfFilePath = 'temp/story_' . $storyRequest->id . '.pdf';

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->set_option('isFontSubsettingEnabled', true);
        $pdf->getDomPDF()->set_option("defaultFont", "Deja Vu Sans");

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
        $storyPages = $storyRequest->storyPages;
        $pdf->loadView('pdf.story', compact('storyPages'));
        $filename = 'story_' . $storyRequest->id . '.pdf';
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
        return json_encode($downloadLink);
    }}
