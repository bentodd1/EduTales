<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Include SweetAlert CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Story Request</title>
    <!-- Add some basic styling -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .spinner {
            border: 4px solid rgba(0,0,0,.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left: 4px solid #007bff;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Create Story Request</h1>
    <form id="storyRequestForm" action="/story-request" method="POST" >
        @csrf

        <!-- Grade Level Dropdown (Assuming you have a $gradeLevels variable passed from the controller) -->
        <div class="form-group">
            <label for="grade_level">Grade Level:</label>
            <select name="grade_level_id" id="grade_level">
                @foreach ($gradeLevels as $gradeLevel)
                    <option value="{{ $gradeLevel->id }}">{{ $gradeLevel->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="subject">Subject:</label>
            <input type="text" name="subject" id="subject" required>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea name="description" id="description" required></textarea>
        </div>

        <!-- Dynamic Input for Sight Words -->
        <div class="form-group">
            <label for="sight_words">Sight Words (comma-separated):</label>
            <input type="text" name="sight_words" id="sight_words">
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="page_number">Number of Pages:</label>
            <select name="page_number" id="page_number">
                <option value="10">10</option>
                <option value="20">20</option>
            </select>
        </div>

        <button type="submit">Submit</button>
        <div id="loadingSpinner" style="display: none;">
            This may take a few minutes to generate the story
            <div class="spinner"></div>
        </div>
    </form>

</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.js"></script>
<script>
    window.onload = function() {
        @if(session('success'))
        Swal.fire({
            title: 'Success!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'Cool'
        });
        @endif
    };
</script>

</body>
</html>
