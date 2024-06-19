<?php
include 'navbar2.php';
require_once 'autoload.php';

$client = new MongoDB\Client();
$playerCollection = $client->pdds_proyek->player;
$teamCollection = $client->pdds_proyek->tstats;

$selectedPlayerStats = [];
$averagePlayerStats = [];
$selectedTeamStats = [];
$averageTeamStats = [];
$teamDetails = null;
$year = isset($_GET['year']) ? intval($_GET['year']) : null;
$selectedTeam = isset($_GET['team']) ? $_GET['team'] : null;
function safeSum($array, $key)
{
    $sum = 0;
    foreach ($array as $item) {
        $value = isset($item[$key]) ? floatval($item[$key]) : 0;
        $sum += $value;
    }
    return $sum;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['name']) && !empty($_GET['name'])) {
    $playerName = $_GET['name'];

    $playerCursor = $playerCollection->find(['player' => $playerName, 'season' => $year]);
    $playerData = iterator_to_array($playerCursor);

    if (!empty($playerData)) {
        $teamDetails = $teamCollection->findOne(['abbreviation' => $playerData[0]['tm']]);

        $playerStats = [
            'trb_per_game' => safeSum($playerData, 'trb_per_game'),
            'ast_per_game' => safeSum($playerData, 'ast_per_game'),
            'stl_per_game' => safeSum($playerData, 'stl_per_game'),
            'blk_per_game' => safeSum($playerData, 'blk_per_game'),
            'pts_per_game' => safeSum($playerData, 'pts_per_game'),
            'count' => count($playerData)
        ];

        foreach ($playerStats as $key => $value) {
            if ($key !== 'count') {
                $selectedPlayerStats[$key] = $value / $playerStats['count'];
            }
        }
    }

    if ($year) {
        // Calculate average player stats for the selected year and position
        $selectedPlayerPosition = $playerData[0]; // Get position of selected player
        $yearCursor = $playerCollection->find(['season' => $year]);
        $yearData = iterator_to_array($yearCursor);


        if (!empty($yearData)) {
            $yearStats = [
                'trb_per_game' => safeSum($yearData, 'trb_per_game'),
                'ast_per_game' => safeSum($yearData, 'ast_per_game'),
                'stl_per_game' => safeSum($yearData, 'stl_per_game'),
                'blk_per_game' => safeSum($yearData, 'blk_per_game'),
                'pts_per_game' => safeSum($yearData, 'pts_per_game'),
                'count' => count($yearData)
            ];

            foreach ($yearStats as $key => $value) {
                if ($key !== 'count') {
                    $averagePlayerStats[$key] = $value / $yearStats['count'];
                }
            }
        }

        // Calculate average team stats for the selected year from tstats collection
        $teamStatsCursor = $teamCollection->find(['season' => $year]);
        $teamStatsData = iterator_to_array($teamStatsCursor);

        if (!empty($teamStatsData)) {
            $teamStats = [
                'trb_per_game' => safeSum($teamStatsData, 'trb_per_game'),
                'ast_per_game' => safeSum($teamStatsData, 'ast_per_game'),
                'stl_per_game' => safeSum($teamStatsData, 'stl_per_game'),
                'blk_per_game' => safeSum($teamStatsData, 'blk_per_game'),
                'pts_per_game' => safeSum($teamStatsData, 'pts_per_game'),
                'count' => count($teamStatsData)
            ];

            foreach ($teamStats as $key => $value) {
                if ($key !== 'count') {
                    $averageTeamStats[$key] = $value / $teamStats['count'];
                }
            }
        }

        // Retrieve stats for the selected team from tstats collection
        if ($selectedTeam) {
            $selectedTeamStatsCursor = $teamCollection->find(['abbreviation' => $selectedTeam, 'season' => $year]);
            $selectedTeamStatsData = iterator_to_array($selectedTeamStatsCursor);

            if (!empty($selectedTeamStatsData)) {
                $selectedTeamStats = [
                    'trb_per_game' => safeSum($selectedTeamStatsData, 'trb_per_game'),
                    'ast_per_game' => safeSum($selectedTeamStatsData, 'ast_per_game'),
                    'stl_per_game' => safeSum($selectedTeamStatsData, 'stl_per_game'),
                    'blk_per_game' => safeSum($selectedTeamStatsData, 'blk_per_game'),
                    'pts_per_game' => safeSum($selectedTeamStatsData, 'pts_per_game'),
                    'count' => count($selectedTeamStatsData)
                ];

                foreach ($selectedTeamStats as $key => $value) {
                    if ($key !== 'count') {
                        $selectedTeamStats[$key] = $value / $selectedTeamStats['count'];
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player and Team Stats Comparison</title>
    <style>
        body {
            margin: 20px;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }

        form {
            margin-bottom: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        label {
            margin-right: 10px;
            font-weight: bold;
        }

        select,
        input {
            margin-right: 10px;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .tables-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            vertical-align: middle;
            /* Ensures content is vertically centered */
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .team-details {
            margin-top: 20px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .team-details p {
            margin: 5px 0;
            font-weight: bold;
        }

        .team-details p span {
            font-weight: normal;
        }

        caption {
            caption-side: top;
            margin-bottom: 10px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .bar-container {
            width: 100%;
            height: 20px;
            background-color: lightblue;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }

        .bar {
            height: 100%;
            /* border-radius: 4px; */
        }

        .higher {
            background-color: #28a745;
            /* green */
        }

        .lower {
            background-color: #dc3545;
            /* red */
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(function() {
            $('#year, #team').change(function() {
                $('#search_form').submit();
            });
        });
    </script>
</head>

<body>
    <form id="search_form" method="GET" action="">
        <label for="year">Year:</label>
        <select id="year" name="year">
            <option value="">Select Year</option>
            <?php for ($yr = 1947; $yr <= 2024; $yr++) : ?>
                <option value="<?php echo $yr; ?>" <?php if (isset($_GET['year']) && $_GET['year'] == $yr) echo 'selected'; ?>>
                    <?php echo $yr; ?>
                </option>
            <?php endfor; ?>
        </select>


        <!-- PLAYER DROPDOWN -->

        <label for="player_name">Player Name:</label>
        <select id="player_name" name="name">
            <option value="">Select a player...</option>


            <?php
            // MongoDB connection and collection retrieval
            $collection = $client->pdds_proyek->player;

            // Query MongoDB to fetch distinct player names
            $cursor = $collection->distinct('player');

            // Iterate through the distinct player names
            foreach ($cursor as $playerName) {
                // Check if the name matches the query parameter
                $selected = (isset($_GET['name']) && $_GET['name'] == $playerName) ? 'selected' : '';

                // Output each option
                echo '<option value="' . htmlspecialchars($playerName) . '" ' . $selected . '>' . htmlspecialchars($playerName) . '</option>';
            }
            ?>

        </select>

        <!-- Include JavaScript for search functionality -->
        <script>
            // Add JavaScript for search functionality
            $(document).ready(function() {
                // Initialize select2 on the player_name element
                $('#player_name').select2({
                    placeholder: "Select a player...",
                    allowClear: true,
                    width: '100%'
                });
            });
        </script>


        <label for="team">Select Team to Transfer:</label>
        <select id="team" name="team">
            <option value="" <?php echo empty($selectedTeam) ? 'selected' : ''; ?>>Select Team</option>
            <?php foreach ($teamCollection->find() as $team) : ?>
                <option value="<?php echo htmlspecialchars($team['abbreviation']); ?>" <?php if ($selectedTeam === $team['abbreviation']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($team['team']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Search</button>
    </form>

    <?php if ($teamDetails) : ?>
        <div class="team-details">
            <p><strong><?php echo htmlspecialchars($_GET['name']); ?></strong></p>
            <p><strong>Team Name:</strong> <span><?php echo htmlspecialchars($teamDetails['team']) . " (" . htmlspecialchars($teamDetails['abbreviation']) . ")"; ?></span></p>
        </div>
    <?php endif; ?>

    <!-- PLAYER -->
    <div class="tables-container">
        <?php if (!empty($selectedPlayerStats) && !empty($averagePlayerStats)) : ?>
            <table>
                <caption>Comparison with Average Player Stats in <?php echo $year; ?></caption>
                <thead>
                    <tr>
                        <th style="width: 25%;">Statistic</th>
                        <th style="width: 75%;"><?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) . "'s Stat  vs  Average Player's Stat" : "Player's Stat  vs  Average Player's Stat"; ?></th>
                    </tr>
                </thead>
                <tbody>


                    <!-- REBOUNDS Player -->
                    <tr>
                        <td style="height: 80px;">Rebounds per Game</td>
                        <td>
                            <div style="position: relative; top:10px">
                                <?php
                                $maxRebounds = max($selectedPlayerStats['trb_per_game'], $averagePlayerStats['trb_per_game']);
                                $selectedPlayerWidth = ($selectedPlayerStats['trb_per_game'] / $maxRebounds) * 100;
                                $averagePlayerWidth = ($averagePlayerStats['trb_per_game'] / $maxRebounds) * 100;

                                // If the selected is higher echo this part (HIJAU)
                                if ($selectedPlayerStats['trb_per_game'] > $averagePlayerStats['trb_per_game']) {
                                    echo "
                                     <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar higher' style='width: {$selectedPlayerWidth}%; height: 100%; background-color: #4caf50; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar lower' style='width: {$averagePlayerWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($selectedPlayerStats['trb_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($averagePlayerWidth - 2) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($averagePlayerStats['trb_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";

                                // If the average is higher echo this part
                                } else {

                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar lower' style='width: {$averagePlayerWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar higher' style='width: {$selectedPlayerWidth}%; height: 100%; background-color: #dc3545; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($averagePlayerStats['trb_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($selectedPlayerWidth - 2) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($selectedPlayerStats['trb_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";
                                }
                                ?>

                            </div>
                        </td>
                    </tr>



                    <!-- ASSISTS Player -->
                    <tr>
                        <td style="height: 80px;">Assists per Game</td>
                        <td>
                            <div style="position: relative; top:10px">
                                <?php
                                $maxRebounds = max($selectedPlayerStats['ast_per_game'], $averagePlayerStats['ast_per_game']);
                                $selectedPlayerWidth = ($selectedPlayerStats['ast_per_game'] / $maxRebounds) * 100;
                                $averagePlayerWidth = ($averagePlayerStats['ast_per_game'] / $maxRebounds) * 100;

                                // If the selected is higher echo this part (HIJAU)
                                if ($selectedPlayerStats['ast_per_game'] > $averagePlayerStats['ast_per_game']) {
                                    echo "
                                     <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar higher' style='width: {$selectedPlayerWidth}%; height: 100%; background-color: #4caf50; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar lower' style='width: {$averagePlayerWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($selectedPlayerStats['ast_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($averagePlayerWidth - 3) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($averagePlayerStats['ast_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";

                                // If the average is higher echo this part
                                } else {

                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar lower' style='width: {$averagePlayerWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar higher' style='width: {$selectedPlayerWidth}%; height: 100%; background-color: #dc3545; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($averagePlayerStats['ast_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($selectedPlayerWidth - 3) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($selectedPlayerStats['ast_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";
                                }
                                ?>

                            </div>
                        </td>
                    </tr>


                    <!-- STEALS Player -->
                    <tr>
                        <td style="height: 80px;">Steals per Game</td>
                        <td>
                            <div style="position: relative; top:10px">
                                <?php
                                $maxRebounds = max($selectedPlayerStats['stl_per_game'], $averagePlayerStats['stl_per_game']);
                                $selectedPlayerWidth = ($selectedPlayerStats['stl_per_game'] / $maxRebounds) * 100;
                                $averagePlayerWidth = ($averagePlayerStats['stl_per_game'] / $maxRebounds) * 100;

                                // If the selected is higher echo this part (HIJAU)
                                if ($selectedPlayerStats['stl_per_game'] > $averagePlayerStats['stl_per_game']) {
                                    echo "
                                     <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar higher' style='width: {$selectedPlayerWidth}%; height: 100%; background-color: #4caf50; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar lower' style='width: {$averagePlayerWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($selectedPlayerStats['stl_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($averagePlayerWidth - 3) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($averagePlayerStats['stl_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";

                                // If the average is higher echo this part
                                } else {

                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar lower' style='width: {$averagePlayerWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar higher' style='width: {$selectedPlayerWidth}%; height: 100%; background-color: #dc3545; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($averagePlayerStats['stl_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($selectedPlayerWidth - 3) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($selectedPlayerStats['stl_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";
                                }
                                ?>

                            </div>
                        </td>
                    </tr>


                   <!-- BLOCKS Player -->
                   <tr>
                        <td style="height: 80px;">Blocks per Game</td>
                        <td>
                            <div style="position: relative; top:10px">
                                <?php
                                $maxRebounds = max($selectedPlayerStats['blk_per_game'], $averagePlayerStats['blk_per_game']);
                                $selectedPlayerWidth = ($selectedPlayerStats['blk_per_game'] / $maxRebounds) * 100;
                                $averagePlayerWidth = ($averagePlayerStats['blk_per_game'] / $maxRebounds) * 100;

                                // If the selected is higher echo this part (HIJAU)
                                if ($selectedPlayerStats['blk_per_game'] > $averagePlayerStats['blk_per_game']) {
                                    echo "
                                     <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar higher' style='width: {$selectedPlayerWidth}%; height: 100%; background-color: #4caf50; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar lower' style='width: {$averagePlayerWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($selectedPlayerStats['blk_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($averagePlayerWidth - 2) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($averagePlayerStats['blk_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";

                                // If the average is higher echo this part
                                } else {

                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar lower' style='width: {$averagePlayerWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar higher' style='width: {$selectedPlayerWidth}%; height: 100%; background-color: #dc3545; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($averagePlayerStats['blk_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($selectedPlayerWidth - 2) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($selectedPlayerStats['blk_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";
                                }
                                ?>

                            </div>
                        </td>
                    </tr>


                    <!-- POINTS Player -->
                   <tr>
                        <td style="height: 80px;">Points per Game</td>
                        <td>
                            <div style="position: relative; top:10px">
                                <?php
                                $maxRebounds = max($selectedPlayerStats['pts_per_game'], $averagePlayerStats['pts_per_game']);
                                $selectedPlayerWidth = ($selectedPlayerStats['pts_per_game'] / $maxRebounds) * 100;
                                $averagePlayerWidth = ($averagePlayerStats['pts_per_game'] / $maxRebounds) * 100;

                                // If the selected is higher echo this part (HIJAU)
                                if ($selectedPlayerStats['pts_per_game'] > $averagePlayerStats['pts_per_game']) {
                                    echo "
                                     <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar higher' style='width: {$selectedPlayerWidth}%; height: 100%; background-color: #4caf50; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar lower' style='width: {$averagePlayerWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($selectedPlayerStats['pts_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($averagePlayerWidth - 2) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($averagePlayerStats['pts_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";

                                // If the average is higher echo this part
                                } else {

                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar lower' style='width: {$averagePlayerWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar higher' style='width: {$selectedPlayerWidth}%; height: 100%; background-color: #dc3545; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($averagePlayerStats['pts_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($selectedPlayerWidth - 2) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($selectedPlayerStats['pts_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";
                                }
                                ?>

                            </div>
                        </td>
                    </tr>


                </tbody>
            </table>
        <?php endif; ?>

        <br>

        <!-- TEAM -->
        <?php if (!empty($selectedTeamStats) && !empty($averageTeamStats)) : ?>
            <table>
                <caption>Comparison with Average Team Stats in <?php echo $year; ?></caption>
                <thead>
                    <tr>
                        <th style="width: 25%;">Statistic</th>
                        <th style="width: 75%;"><?php echo isset($_GET['team']) ? htmlspecialchars($_GET['team']) . "'s Stat  vs  Average Team's Stat" : "Team's Value  vs  Average Team's Stat"; ?></th>
                    </tr>
                </thead>
                <tbody>

                    <!-- REBOUNDS TEAM -->
                    <tr>
                        <td style="height: 80px;">Rebounds per Game</td>
                        <td>
                            <div style="position: relative; top:10px">
                                <?php
                                $maxRebounds = max($selectedTeamStats['trb_per_game'], $averageTeamStats['trb_per_game']);
                                $selectedTeamWidth = ($selectedTeamStats['trb_per_game'] / $maxRebounds) * 100;
                                $averageTeamWidth = ($averageTeamStats['trb_per_game'] / $maxRebounds) * 100;

                                // If the selected is higher echo this part (HIJAU)
                                if ($selectedTeamStats['trb_per_game'] > $averageTeamStats['trb_per_game']) {
                                    echo "
                                     <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar higher' style='width: {$selectedTeamWidth}%; height: 100%; background-color: #4caf50; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar lower' style='width: {$averageTeamWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($selectedTeamStats['trb_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($averageTeamWidth - 5) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($averageTeamStats['trb_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";

                                // If the average is higher echo this part
                                } else {

                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar lower' style='width: {$averageTeamWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar higher' style='width: {$selectedTeamWidth}%; height: 100%; background-color: #dc3545; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($averageTeamStats['trb_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($selectedTeamWidth - 5) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($selectedTeamStats['trb_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";
                                }
                                ?>

                            </div>
                        </td>
                    </tr>



                    <!-- ASSISTS TEAM -->
                    <tr>
                        <td style="height: 80px;">Assists per Game</td>
                        <td>
                            <div style="position: relative; top:10px">
                                <?php
                                $maxRebounds = max($selectedTeamStats['ast_per_game'], $averageTeamStats['ast_per_game']);
                                $selectedTeamWidth = ($selectedTeamStats['ast_per_game'] / $maxRebounds) * 100;
                                $averageTeamWidth = ($averageTeamStats['ast_per_game'] / $maxRebounds) * 100;

                                // If the selected is higher echo this part
                                if ($selectedTeamStats['ast_per_game'] > $averageTeamStats['ast_per_game']) {
                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar higher' style='width: {$selectedTeamWidth}%; height: 100%; background-color: #4caf50; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar lower' style='width: {$averageTeamWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($selectedTeamStats['ast_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($averageTeamWidth - 5) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($averageTeamStats['ast_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";

                                // If the average is higher echo this part
                                } else {

                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar lower' style='width: {$averageTeamWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar higher' style='width: {$selectedTeamWidth}%; height: 100%; background-color: #dc3545; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($averageTeamStats['ast_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($selectedTeamWidth - 5) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($selectedTeamStats['ast_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";
                                }
                                ?>

                            </div>
                        </td>
                    </tr>

                    <!-- STEALS TEAM -->
                    <tr>
                        <td style="height: 80px;">Steals per Game</td>
                        <td>
                            <div style="position: relative; top:10px">
                                <?php
                                $maxRebounds = max($selectedTeamStats['stl_per_game'], $averageTeamStats['stl_per_game']);
                                $selectedTeamWidth = ($selectedTeamStats['stl_per_game'] / $maxRebounds) * 100;
                                $averageTeamWidth = ($averageTeamStats['stl_per_game'] / $maxRebounds) * 100;

                                // If the selected is higher echo this part (SALAH)
                                if ($selectedTeamStats['stl_per_game'] > $averageTeamStats['stl_per_game']) {
                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar higher' style='width: {$selectedTeamWidth}%; height: 100%; background-color: #4caf50; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar lower' style='width: {$averageTeamWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($selectedTeamStats['stl_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($averageTeamWidth - 4) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($averageTeamStats['stl_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";

                                    // If the average is higher echo this part (BENAR)
                                } else {

                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar lower' style='width: {$averageTeamWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar higher' style='width: {$selectedTeamWidth}%; height: 100%; background-color: #dc3545; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($averageTeamStats['stl_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($selectedTeamWidth - 4) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($selectedTeamStats['stl_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";
                                }
                                ?>

                            </div>
                        </td>
                    </tr>


                    <!-- BLOCKS TEAM -->
                    <tr>
                        <td style="height: 80px;">Blocks per Game</td>
                        <td>
                            <div style="position: relative; top:10px">
                                <?php
                                $maxRebounds = max($selectedTeamStats['blk_per_game'], $averageTeamStats['blk_per_game']);
                                $selectedTeamWidth = ($selectedTeamStats['blk_per_game'] / $maxRebounds) * 100;
                                $averageTeamWidth = ($averageTeamStats['blk_per_game'] / $maxRebounds) * 100;

                                // If the selected is higher echo this part (HIJAU)
                                if ($selectedTeamStats['blk_per_game'] > $averageTeamStats['blk_per_game']) {
                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar higher' style='width: {$selectedTeamWidth}%; height: 100%; background-color: #4caf50; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar lower' style='width: {$averageTeamWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($selectedTeamStats['blk_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($averageTeamWidth - 4) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($averageTeamStats['blk_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";

                                // If the average is higher echo this part
                                } else {

                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar lower' style='width: {$averageTeamWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar higher' style='width: {$selectedTeamWidth}%; height: 100%; background-color: #dc3545; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($averageTeamStats['blk_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($selectedTeamWidth - 4) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($selectedTeamStats['blk_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";
                                }
                                ?>

                            </div>
                        </td>
                    </tr>


                    <!-- POINTS TEAM -->
                    <tr>
                        <td style="height: 80px;">Points per Game</td>
                        <td>
                            <div style="position: relative; top:10px">
                                <?php
                                $maxRebounds = max($selectedTeamStats['pts_per_game'], $averageTeamStats['pts_per_game']);
                                $selectedTeamWidth = ($selectedTeamStats['pts_per_game'] / $maxRebounds) * 100;
                                $averageTeamWidth = ($averageTeamStats['pts_per_game'] / $maxRebounds) * 100;

                                // If the selected is higher echo this part (HIJAU)
                                if ($selectedTeamStats['pts_per_game'] > $averageTeamStats['pts_per_game']) {
                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar higher' style='width: {$selectedTeamWidth}%; height: 100%; background-color: #4caf50; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar lower' style='width: {$averageTeamWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($selectedTeamStats['pts_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($averageTeamWidth - 6) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($averageTeamStats['pts_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";

                                // If the average is higher echo this part
                                } else {

                                    echo "
                                    <div style='display: flex; align-items: center; position: relative;'>
                                        <div class='bar-container' style='position: relative; height: 20px; width: 100%; background-color: #f1f1f1; overflow: hidden; margin-right: 10px;'>
                                            <div class='bar lower' style='width: {$averageTeamWidth}%; height: 100%; background-color: #2196F3; position: absolute; top: 0; left: 0;'></div>
                                            <div class='bar higher' style='width: {$selectedTeamWidth}%; height: 100%; background-color: #dc3545; position: absolute; top: 0; left: 0;'></div>
                                        </div>

                                        <span style='color: black;'>
                                            " . number_format($averageTeamStats['pts_per_game'], 2) . "
                                        </span>
                                    </div>

                                    <div style='position: absolute; top: -25px; left: " . ($selectedTeamWidth - 6) . "%;'>
                                        <span style='color: black;'>
                                            " . number_format($selectedTeamStats['pts_per_game'], 2) . "
                                        </span>
                                    </div>
                                    ";
                                }
                                ?>

                            </div>
                        </td>
                    </tr>


                </tbody>
            </table>
        <?php endif; ?>
    </div>
    </div>
</body>

</html>