<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate PDF</title>
</head>
<body>
<form action="{{ url('/generate-pdf') }}" method="post">
    @csrf
    <label for="storyRequestId">Story Request ID:</label>
    <input type="text" name="storyRequestId" id="storyRequestId" required>
    <button type="submit">Generate PDF</button>
</form>
</body>
</html>
