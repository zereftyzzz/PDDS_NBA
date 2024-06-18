<?php
require_once 'autoload.php'; // Assuming this includes necessary libraries
include '../navbar.php';

// Initialize $players array to avoid undefined variable error
$players = [];
$uploadedFileName = null;

// Database connection configuration
$host = 'localhost'; // Change this to your MySQL host
$dbname = 'pdds_nba'; // Change this to your database name in phpMyAdmin
$username = 'root'; // Change this to your MySQL username
$password = ''; // Change this to your MySQL password

try {
    // Connect to MySQL database using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if form was submitted
    $selectedYear = isset($_POST['selectedYear']) ? (int)$_POST['selectedYear'] : 2024; // Default to 2024
    $selectedConference = isset($_POST['selectedConference']) ? $_POST['selectedConference'] : 'West'; // Default to West

    // Query to select players filtered by selected year and conference (East or West)
    $sql = "SELECT * FROM all_star WHERE 1=1";
    $params = [];
    if ($selectedYear) {
        $sql .= " AND season = :season";
        $params[':season'] = $selectedYear;
    }
    if ($selectedConference && in_array($selectedConference, ['East', 'West'])) {
        $sql .= " AND team LIKE :conference";
        $params[':conference'] = "%$selectedConference%";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

} 
catch (PDOException $e) {
    echo 'Database connection failed: ' . $e->getMessage();
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
    // Function to initialize form with selected values
    document.addEventListener("DOMContentLoaded", function() {
        // Get selected values from localStorage if available
        const selectedYear = localStorage.getItem('selectedYear');
        const selectedConference = localStorage.getItem('selectedConference');

        // Set selected values in the form if they exist
        if (selectedYear) {
            document.getElementById('yearDropdown').value = selectedYear;
        }
        if (selectedConference) {
            document.getElementById('conferenceDropdown').value = selectedConference;
        }
    });

    // Function to submit the form when dropdown selection changes
    function submitForm() {
        const selectedYear = document.getElementById('yearDropdown').value;
        const selectedConference = document.getElementById('conferenceDropdown').value;

        // Store selected values in localStorage
        localStorage.setItem('selectedYear', selectedYear);
        localStorage.setItem('selectedConference', selectedConference);

        // Submit the form
        document.getElementById("filterForm").submit();
    }
</script>

<!-- Title  -->
    <h1 style="justify-content: center; align-items: center; display: flex; margin-top:30px;">All Star Map</h1>

<!-- Filter Form -->
    <div class="container" style="margin-top: 20px; width: 46%;">
        <form id="filterForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="row g-3 align-items-center">
        
        <!-- <div class="col-auto" >
            <button class="btn btn-dark" onclick="history.back()">Back</button>
        </div> -->
    
        <!-- Year Dropdown -->
            <div class="col-auto">
                <select id="yearDropdown" class="form-control" name="selectedYear" onchange="submitForm()">
                    <option value="" disabled>Select the year</option>
                    <?php
                    // Define the range of years
                    $startYear = 1951;
                    $endYear = 2024;

                    // Loop through the range of years and create options starting from the highest
                    for ($year = $endYear; $year >= $startYear; $year--) {
                        $selected = ($year == $selectedYear) ? 'selected' : '';
                        echo "<option value=\"$year\" $selected>$year</option>";
                    }
                    ?>
                </select>
            </div>
            
            <!-- Conference Dropdown -->
            <div class="col-auto">
                <select id="conferenceDropdown" class="form-control" name="selectedConference" onchange="submitForm()">
                    <option value="" disabled>Select the conference</option>
                    <option value="East" <?php if ($selectedConference == 'East') echo 'selected'; ?>>East</option>
                    <option value="West" <?php if ($selectedConference == 'West') echo 'selected'; ?>>West</option>
                </select>
            </div>
        </form>
    </div>

<!-- Player -->
    <div class="container" style="margin-top: 20px; width: 100%;">
        <?php
        // Define player positions
        $playerPositions = [
            'p1' => ['left' => 375, 'top' => 135],
            'p2' => ['left' => 205, 'top' => 195],
            'p3' => ['left' => 375, 'top' => 258],
            'p4' => ['left' => 205, 'top' => 320],
            'p5' => ['left' => 375, 'top' => 380],
            'p6' => ['left' => 595, 'top' => 135],
            'p7' => ['left' => 765, 'top' => 195],
            'p8' => ['left' => 595, 'top' => 258],
            'p9' => ['left' => 765, 'top' => 320],
            'p10' => ['left' => 595, 'top' => 380],
        ];

        // Iterate through $players array
        if (!empty($players)) {
            $i = 1;
            foreach ($players as $player) {
                if ($i > 10) break; // Display only first 10 players
                $name = isset($player['player']) ? htmlspecialchars($player['player'], ENT_QUOTES, 'UTF-8') : 'N/A';

                // Truncate the name to 16 characters and add ellipsis if it's longer
                $truncatedName = strlen($name) > 16 ? substr($name, 0, 16) . '..' : $name;

                // Display player name at specified position
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
                foreach ($players as $player) {
                    $name = isset($player['player']) ? htmlspecialchars($player['player'], ENT_QUOTES, 'UTF-8') : 'N/A';
                    echo "<tr><td>{$name}</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
