<!-- resources/views/pdf/story.blade.php -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Story PDF</title>
    <style>
        @font-face {
            font-family: 'Firefly';
            font-style: normal;
            font-weight: normal;
            src: url(http://example.com/fonts/firefly.ttf) format('truetype');
        }

        /* Your styles here */
        p {
            font-family: firefly, DejaVu Sans, sans-serif;;
        }
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
