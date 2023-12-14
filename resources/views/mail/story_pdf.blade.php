<!DOCTYPE html>
<html>
<head>
    <title>Your Story PDF</title>
    <style>
        /* Email-specific styles */
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }
        .header {
            /* Styles for header */
        }
        .content {
            /* Styles for content */
        }
        .footer {
            /* Styles for footer */
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Your Story PDF is Ready!</h1>
</div>

<div class="content">
    <p>Hi there!</p>
    <p>We are excited to share your personalized story with you. Please find your story attached to this email.</p>

    {{-- Include dynamic content if needed --}}
    <p>Story Title: {{ $storyTitle }}</p>
    <a href="{{ $downloadLink }}">Download your PDF</a>

    <p>Thank you for using our service!</p>
    <p>Please take some time to fill out our survey and share if you enjoyed!</p>
    <a href="https://docs.google.com/forms/d/e/1FAIpQLSc3-a120_Lxuyz5eYmXPBVmQNcZW4AGq92hytaJXir6S0geCg/viewform">Fill out our survey</a>

</div>

<div class="footer">
    <p>Best regards,</p>
    <p>Your Team at EduTales</p>
</div>
</body>
</html>
