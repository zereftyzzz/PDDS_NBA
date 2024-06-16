<?php
require_once 'autoload.php'; // Assuming this includes necessary libraries
include '../navbar.php';

// Initialize $players array to avoid undefined variable error
$players = [];
$uploadedFileName = null;

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {
    $selectedYear = isset($_POST['selectedYear']) ? (int)$_POST['selectedYear'] : null;

    // Validate and process the uploaded file
    $uploadDir = './uploads/';
    $uploadFile = $uploadDir . basename($_FILES['file']['name']);
    $uploadedFileName = basename($_FILES['file']['name']); // Store the uploaded file name for display

    // Check if the uploads directory exists and is writable
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create the directory recursively if it doesn't exist
    }

    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        // File uploaded successfully, now parse CSV and filter by selected year
        if (($handle = fopen($uploadFile, 'r')) !== false) {
            // Read the headers to determine column positions
            $headers = fgetcsv($handle, 1000, ',');
            
            // Find the index of 'player' and 'season' in the headers array
            $playerIndex = array_search('player', $headers);
            $seasonIndex = array_search('season', $headers);

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Access the player and season values based on their column indices
                $player = trim($data[$playerIndex]);
                $season = (int)trim($data[$seasonIndex]);

                // Filter by selected year if matches
                if ($selectedYear && $season === $selectedYear) {
                    $players[] = ['player' => $player, 'season' => $season];
                }
            }
            fclose($handle);
        }
    } else {
        echo 'Error uploading file. Ensure the uploads directory exists and is writable.';
    }
}
?>

<style>
    .image-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 80%; /* Make container full width */
        height: 500px; /* Fixed height */
        margin-top: 20px;
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto;
    }

    .centered-image {
        max-height: 100%; /* Ensure the image scales down proportionally */
        max-width: 100%; /* Ensure the image scales down proportionally */
    }
</style>

<script>
    function goBack() {
        window.history.back();
    }
    
    // Function to submit the form when dropdown selection changes
    function submitForm() {
        document.getElementById("yearForm").submit();
    }
</script>

<!-- Upload Form -->
<div class="container" style="margin-top: 20px; width: 46%;">
    <form id="uploadForm" method="post" enctype="multipart/form-data">
        <h1 for="file">Upload your file</h1>
        <br>
        <div class="mb-3 d-flex align-items-center">
            <input class="form-control me-2" type="file" name="file" id="file" accept=".csv" required>
            <button type="submit" class="btn btn-dark" id="butt_upload" style="margin-left: 20px;">Upload</button>
        </div>
    </form>
</div>

<!-- Display uploaded file name if available -->
<?php if ($uploadedFileName) : ?>
    <div class="container" style="margin-top: 20px; width: 46%;">
        <p>Uploaded File: <?php echo htmlspecialchars($uploadedFileName); ?></p>
    </div>
<?php endif; ?>

<!-- Dropdown Tahun -->
<div class="container" style="margin-top: 20px; width: 46%;">
    <form id="yearForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <select id="yearDropdown" class="form-control" name="selectedYear" onchange="submitForm()">
            <option value="" selected disabled>Select the year</option>
            <?php
            // Define the range of years
            $startYear = 1951;
            $endYear = 2024;

            // Loop through the range of years and create options
            for ($year = $startYear; $year <= $endYear; $year++) {
                echo "<option value=\"$year\">$year</option>";
            }
            ?>
        </select>
    </form>
</div>

<!-- Player -->
<div class="container" style="margin-top: 20px; width: 100%;">
    <?php
    // Define player positions
    $playerPositions = [
        'p1' => ['left' => 775, 'top' => 135],
        'p2' => ['left' => 605, 'top' => 195],
        'p3' => ['left' => 775, 'top' => 258],
        'p4' => ['left' => 605, 'top' => 320],
        'p5' => ['left' => 775, 'top' => 380],
        'p6' => ['left' => 995, 'top' => 135],
        'p7' => ['left' => 1165, 'top' => 195],
        'p8' => ['left' => 995, 'top' => 258],
        'p9' => ['left' => 1165, 'top' => 320],
        'p10' => ['left' => 995, 'top' => 380],
    ];

    // Initialize $players array to avoid undefined variable error
    $players = [];

    // Iterate through $players array only if it's not empty
    if (!empty($players)) {
        $i = 1;
        foreach ($players as $player) {
            if ($i > 10) break;
            $name = isset($player['player']) ? htmlspecialchars($player['player'], ENT_QUOTES, 'UTF-8') : 'N/A';

            // Truncate the name to 12 characters and add ellipsis if it's longer
            $truncatedName = strlen($name) > 16 ? substr($name, 0, 16) . '..' : $name;

            $left = $playerPositions['p' . $i]['left'];
            $top = $playerPositions['p' . $i]['top'];
            echo "<p id='p$i' style='position:absolute; margin-left:{$left}px; margin-top:{$top}px; font-weight:bold;'>{$truncatedName}</p>";
            $i++;
        }
    } else {
        echo "<p>No players to display.</p>";
    }
    ?>
</div>


<!-- Gambar Lapangan -->
<div class="image-container" style="margin-top: 40px;">
    <img src="../assets/bg_home.png" class="centered-image" style="height: 500px">
</div>

<!-- Reserve -->
<div class="container" style="margin-top: 20px; width: 46%;">
    <h3 style="margin-bottom: 10px;">Reserve</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Player Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Print remaining players in a table
            for ($j = 0; $j < count($players); $j++) {
                $name = isset($players[$j]['player']) ? htmlspecialchars($players[$j]['player'], ENT_QUOTES, 'UTF-8') : 'N/A';
                echo "<tr><td>{$name}</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
