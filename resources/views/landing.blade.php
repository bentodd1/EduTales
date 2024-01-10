<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Story App</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .hero { text-align: center; padding: 50px; }
        .hero h1 { font-size: 2em; }
        .hero p { font-size: 1.2em; }
        .gallery { display: flex; justify-content: center; padding: 20px; }
        .gallery img { margin: 0 10px; width: 200px; height: auto; }
        .footer { background-color: #f3f3f3; text-align: center; padding: 20px; }
        .pdf-container {
            position: relative;
            padding-top: 56.25%; /* Aspect ratio (16:9) */
            width: 100%;
            height: 0;
        }

        .pdf-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>

<div class="hero">
    <h1>Welcome to Edu Tales</h1>
    <p>Generate custom stories to help kids learn!</p>
    <a href="/story-request">Create Your Story</a>
    <p>Example story below</p>
</div>

<div class="pdf-container">
<iframe src="https://story-images.nyc3.digitaloceanspaces.com/story_110.pdf" height="600px">
    This browser does not support PDFs. Please download the PDF to view it: <a href="https://story-images.nyc3.cdn.digitaloceanspaces.com/story_33.pdf">Download PDF</a>.
</iframe>
</div>

<!-- Add more sections (How It Works, Testimonials) here -->
<div class="footer">
    <p>Contact Us | Follow Us on Social Media</p>
</div>

</body>
</html>
