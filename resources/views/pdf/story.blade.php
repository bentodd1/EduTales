<!-- resources/views/pdf/story.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Story PDF</title>
    <style>
        /* Your styles here */
        .page {
            page-break-after: always;
        }
        img {
            width: 100%; /* or your desired styling */
        }
    </style>
</head>
<body>
@foreach ($storyPages as $page)
    <div class="page">
        <img src="{{ $page->spaces_image_url }}" alt="Story Image for Page {{ $page->page_number }}">
        <p>{{ $page->content }}</p>
    </div>
@endforeach
</body>
</html>
