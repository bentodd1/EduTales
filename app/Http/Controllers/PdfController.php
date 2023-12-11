<?php

namespace App\Http\Controllers;

use App\Jobs\SendPdfJob;
use App\Models\StoryRequest;
use Illuminate\Http\Request;

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
        dispatch(new SendPdfJob($storyRequest));
        return view('generate_pdf_form');
//        $storyPages = StoryPage::where('story_request_id', $storyRequestId)->get();
//
//        $pdf = app('dompdf.wrapper');
//        $context = stream_context_create([
//            'ssl' => [
//                'verify_peer' => FALSE,
//                'verify_peer_name' => FALSE,
//                'allow_self_signed' => TRUE
//            ]
//        ]);
//        $pdf->getDomPDF()->setHttpContext($context);
//
//        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
//
//        $pdf->loadView('pdf.story', compact('storyPages'));
//        dispatch(new SendPdfJob($storyRequest));
//
//        return $pdf->download('story.pdf');
    }}
