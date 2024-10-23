<?php
session_start();  // Start the session

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();  // End the session
    header('Location: ' . basename(__FILE__));  // Redirect to the login page
    exit;
}

// Password protection logic
$password = "demopassword";
if (isset($_POST['password'])) {
    if ($_POST['password'] !== $password) {
        $error = "Incorrect password.";
    } else {
        $_SESSION['authenticated'] = true;  // Store authentication status in session
    }
}

// Check if the user is authenticated (via session)
$authenticated = isset($_SESSION['authenticated']) && $_SESSION['authenticated'];

// Function to create a ZIP archive of all log files
function create_zip_of_logs($logDir, $zipFile) {
    $zip = new ZipArchive;
    if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
        $files = glob($logDir . '*.{log,log.*.gz}', GLOB_BRACE);
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();
        return true;
    } else {
        return false;
    }
}

// Serve the requested log file for download
if ($authenticated && isset($_GET['file'])) {
    $logDir = '/var/log/apache2/';  // Define the log directory path
    $requestedFile = basename($_GET['file']); // Get the file name
    $filePath = $logDir . $requestedFile;

    // Check if the file exists and serve it as download
    if (file_exists($filePath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $requestedFile . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;  // Stop further execution to avoid displaying the page
    } else {
        echo '<p class="text-red-500">File not found.</p>';
    }
}

// Download all logs as a zip
if ($authenticated && isset($_GET['download_all'])) {
    $logDir = '/var/log/apache2/';
    $zipFile = '/tmp/all_logs.zip';  // Temporary file for the ZIP archive

    // Create ZIP of all log files
    if (create_zip_of_logs($logDir, $zipFile)) {
        // Serve the ZIP file for download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="all_logs.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
        // Clean up the temporary file
        unlink($zipFile);
        exit;
    } else {
        echo '<p class="text-red-500">Failed to create ZIP file.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Apache Logs Viewer</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Style for line numbers and alternating line colors */
        .line-numbers {
            counter-reset: line;
        }
        .line-numbers pre span {
            display: block;
            position: relative;
            padding-left: 2.5em;
            white-space: pre-wrap;  /* Preserve formatting and line breaks */
        }
        .line-numbers pre span:before {
            counter-increment: line;
            content: counter(line);
            position: absolute;
            left: 0;
            width: 2em;
            text-align: right;
            padding-right: 0.5em;
            color: gray;
        }
        /* Alternating line colors */
        .line-numbers pre span:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .line-numbers pre span:nth-child(even) {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto relative">
        <h1 class="text-4xl font-bold mb-6 text-center">Apache Logs Viewer</h1>

        <?php if (!$authenticated) : ?>
            <!-- Password form -->
            <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow">
                <form action="" method="POST">
                    <label for="password" class="block text-lg font-medium text-gray-700 mb-2">Enter Password:</label>
                    <input type="password" name="password" id="password" class="w-full p-2 border rounded focus:outline-none focus:ring focus:border-blue-300 mb-4" required>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Submit</button>
                </form>
                <?php if (isset($error)): ?>
                    <p class="text-red-500 mt-4"><?php echo $error; ?></p>
                <?php endif; ?>
            </div>

        <?php else : ?>
            <!-- Logout button -->
            <div class="absolute top-0 right-0 mt-4 mr-4">
                <a href="?logout=true" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
            </div>

            <!-- Download all logs button -->
            <div class="mb-6 text-center">
                <a href="?download_all=true" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Download All Logs</a>
            </div>

            <!-- Download links for log files -->
            <?php
            // Define the log directory path
            $logDir = '/var/log/apache2/';
            $displayFiles = ['access.log', 'error.log'];

            // Get all log files (including .gz) and sort alphabetically
            $logFiles = glob($logDir . '*.{log,log.*.gz}', GLOB_BRACE);
            natsort($logFiles);  // Natural sort files alphabetically

            // Display download links for all log files
            echo '<h2 class="text-2xl font-semibold mt-6 mb-4">Log Files</h2>';
            echo '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">'; // Responsive grid: 1 col for small, 2 for medium, 3 for large
            $colors = ['bg-gray-200', 'bg-gray-300'];  // Alternating colors for rows
            $i = 0;  // Counter to alternate row colors
            foreach ($logFiles as $file) {
                $fileName = basename($file);
                $colorClass = $colors[$i % 2];  // Alternate colors

                echo '<div class="p-4 rounded shadow ' . $colorClass . '">';
                echo '<div class="flex justify-between items-center">';
                echo '<span class="text-blue-500 font-semibold no-underline">' . htmlspecialchars($fileName) . '</span>';
                echo '<p class="text-sm text-gray-600 mt-1">' . round(filesize($file) / 1024, 2) . ' KB</p>';
                echo '<a href="?file=' . urlencode($fileName) . '" class="text-blue-500 no-underline">';
                echo '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
                echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zM16 12l-4 4m0 0l-4-4m4 4V8" />';
                echo '</svg>';
                echo '</a>';
                echo '</div>';
                echo '</div>';
                $i++;  // Increment to alternate row colors
            }
            echo '</div>';
            ?>

            <!-- Display access.log and error.log -->
            <h2 class="text-2xl font-semibold mt-8 mb-4">Latest Access and Error Logs</h2>
            <?php
            foreach ($displayFiles as $file) {
                $filePath = $logDir . $file;
                if (file_exists($filePath)) {
                    echo "<h3 class='text-xl font-semibold mt-6 mb-2'>Displaying: $file</h3>";
                    $fileContent = htmlspecialchars(file_get_contents($filePath));
                    $lines = explode("\n", $fileContent);  // Split the file content by lines
                    echo "<div class='line-numbers'><pre class='bg-gray-100 p-4 rounded border overflow-auto max-h-96'>";
                    foreach ($lines as $line) {
                        echo "<span>" . $line . "</span>\n";  // Add each line inside a span for line numbers and alternate colors
                    }
                    echo "</pre></div>";
                }
            }
            ?>

            <footer class="mt-8 text-center">
                <p class="text-gray-600">&copy; Zafer Onay. <a href="https://github.com/zonay" class="text-blue-500 no-underline">https://github.com/zonay</a></p>
                <p class="text-gray-600 text-xs mt-4 px-16">This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License.</p>
            </footer>
        <?php endif; ?>
    </div>
</body>
</html>
