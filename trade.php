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

        <label for="player_name">Player Name:</label>
        <input type="text" id="player_name" name="name" value="<?php echo isset($_GET['name']) ? $_GET['name'] : ''; ?>" />

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

                
                    <!-- REBOUNDS -->
                    <tr>
    <td>Rebounds per Game</td>
    <td>
        <div class="bar-container" style="position: relative;">
            <?php
            // Calculate total rebounds to determine maximum width of bars
            $maxRebounds = max($selectedPlayerStats['trb_per_game'], $averagePlayerStats['trb_per_game']);
            $selectedPlayerWidth = ($selectedPlayerStats['trb_per_game'] / $maxRebounds) * 100;
            $averagePlayerWidth = ($averagePlayerStats['trb_per_game'] / $maxRebounds) * 100;
            ?>
            <div class="bar higher" style="width: <?php echo $selectedPlayerWidth; ?>%;"></div>
            <div class="bar lower" style="width: <?php echo $averagePlayerWidth; ?>%;"></div>
            
            <!-- Display values outside the bars -->
            <span style="position: absolute; top: 50%; left: <?php echo $selectedPlayerWidth; ?>%; transform: translate(-50%, -50%);"><?php echo number_format($selectedPlayerStats['trb_per_game'], 2); ?></span>
            <span style="position: absolute; top: 50%; right: <?php echo (100 - $averagePlayerWidth); ?>%; transform: translate(50%, -50%);"><?php echo number_format($averagePlayerStats['trb_per_game'], 2); ?></span>
        </div>
    </td>
</tr>



                    <!-- ASSISTS -->
                    <tr>
                        <td>Assists per Game</td>
                        <td>
                            <div style="margin-bottom: 10px;">
                                <span style="float: left;">
                                    <?php echo number_format($selectedPlayerStats['ast_per_game'], 2); ?>
                                </span>
                                <span style="float: right;">
                                    <?php echo number_format($averagePlayerStats['ast_per_game'], 2); ?>
                                </span>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="bar-container">
                                <?php
                                $totalAssists = $selectedPlayerStats['ast_per_game'] + $averagePlayerStats['ast_per_game'];
                                $selectedPlayerWidth = ($selectedPlayerStats['ast_per_game'] / $totalAssists) * 100;
                                $averagePlayerWidth = ($averagePlayerStats['ast_per_game'] / $totalAssists) * 100;
                                ?>
                                <div class="bar higher" style="width: <?php echo $selectedPlayerWidth; ?>%;"></div>
                                <div class="bar lower" style="width: <?php echo $averagePlayerWidth; ?>%;"></div>
                            </div>
                        </td>
                    </tr>

                    <!-- STEALS -->
                    <tr>
                        <td>Steals per Game</td>
                        <td>
                            <div style="margin-bottom: 10px;">
                                <span style="float: left;">
                                    <?php echo number_format($selectedPlayerStats['stl_per_game'], 2); ?>
                                </span>
                                <span style="float: right;">
                                    <?php echo number_format($averagePlayerStats['stl_per_game'], 2); ?>
                                </span>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="bar-container">
                                <?php
                                $totalSteals = $selectedPlayerStats['stl_per_game'] + $averagePlayerStats['stl_per_game'];
                                $selectedPlayerWidth = ($selectedPlayerStats['stl_per_game'] / $totalSteals) * 100;
                                $averagePlayerWidth = ($averagePlayerStats['stl_per_game'] / $totalSteals) * 100;
                                ?>
                                <div class="bar higher" style="width: <?php echo $selectedPlayerWidth; ?>%;"></div>
                                <div class="bar lower" style="width: <?php echo $averagePlayerWidth; ?>%;"></div>
                            </div>
                        </td>
                    </tr>

                    <!-- BLOCKS -->
                    <tr>
                        <td>Blocks per Game</td>
                        <td>
                            <div style="margin-bottom: 10px;">
                                <span style="float: left;">
                                    <?php echo number_format($selectedPlayerStats['blk_per_game'], 2); ?>
                                </span>
                                <span style="float: right;">
                                    <?php echo number_format($averagePlayerStats['blk_per_game'], 2); ?>
                                </span>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="bar-container">
                                <?php
                                $totalBlocks = $selectedPlayerStats['blk_per_game'] + $averagePlayerStats['blk_per_game'];
                                $selectedPlayerWidth = ($selectedPlayerStats['blk_per_game'] / $totalBlocks) * 100;
                                $averagePlayerWidth = ($averagePlayerStats['blk_per_game'] / $totalBlocks) * 100;
                                ?>
                                <div class="bar higher" style="width: <?php echo $selectedPlayerWidth; ?>%;"></div>
                                <div class="bar lower" style="width: <?php echo $averagePlayerWidth; ?>%;"></div>
                            </div>
                        </td>
                    </tr>

                    <!-- POINTS -->
                    <tr>
                        <td>Points per Game</td>
                        <td>
                            <div style="margin-bottom: 10px;">
                                <span style="float: left;">
                                    <?php echo number_format($selectedPlayerStats['pts_per_game'], 2); ?>
                                </span>
                                <span style="float: right;">
                                    <?php echo number_format($averagePlayerStats['pts_per_game'], 2); ?>
                                </span>
                                <div style="clear: both;"></div>
                            </div>

                            <div class="bar-container">
                                <?php
                                $totalPoints = $selectedPlayerStats['pts_per_game'] + $averagePlayerStats['pts_per_game'];
                                $selectedPlayerWidth = ($selectedPlayerStats['pts_per_game'] / $totalPoints) * 100;
                                $averagePlayerWidth = ($averagePlayerStats['pts_per_game'] / $totalPoints) * 100;
                                ?>
                                <div class="bar higher" style="width: <?php echo $selectedPlayerWidth; ?>%;"></div>
                                <div class="bar lower" style="width: <?php echo $averagePlayerWidth; ?>%;"></div>
                            </div>

                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>


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
                    
                    <!-- REBOUNDS -->
                    <tr>
                        <td>Rebounds per Game</td>
                        <td>
                            <div style="margin-bottom: 10px;">
                                <span style="float: left;">
                                    <?php echo number_format($selectedTeamStats['trb_per_game'], 2); ?>
                                </span>
                                <span style="float: right;">
                                    <?php echo number_format($averageTeamStats['trb_per_game'], 2); ?>
                                </span>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="bar-container">
                                <?php
                                $totalRebounds = $selectedTeamStats['trb_per_game'] + $averageTeamStats['trb_per_game'];
                                $selectedTeamWidth = ($selectedTeamStats['trb_per_game'] / $totalRebounds) * 100;
                                $averageTeamWidth = ($averageTeamStats['trb_per_game'] / $totalRebounds) * 100;
                                ?>
                                <div class="bar <?php echo ($selectedTeamStats['trb_per_game'] >= $averageTeamStats['trb_per_game']) ? 'higher' : 'lower'; ?>" style="width: <?php echo $selectedTeamWidth; ?>%; "></div>
                                <div class="bar <?php echo ($selectedTeamStats['trb_per_game'] < $averageTeamStats['trb_per_game']) ? 'higher' : 'lower'; ?>" style="width: <?php echo $averageTeamWidth; ?>%;"></div>
                            </div>
                        </td>
                    </tr>



                    <!-- ASSISTS -->
                    <tr>
                        <td>Assists per Game</td>
                        <td>
                            <div style="margin-bottom: 10px;">
                                <span style="float: left;">
                                    <?php echo number_format($selectedTeamStats['ast_per_game'], 2); ?>
                                </span>
                                <span style="float: right;">
                                    <?php echo number_format($averageTeamStats['ast_per_game'], 2); ?>
                                </span>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="bar-container">
                                <?php
                                $totalRebounds = $selectedTeamStats['ast_per_game'] + $averageTeamStats['ast_per_game'];
                                $selectedTeamWidth = ($selectedTeamStats['ast_per_game'] / $totalRebounds) * 100;
                                $averageTeamWidth = ($averageTeamStats['ast_per_game'] / $totalRebounds) * 100;
                                ?>
                                <div class="bar <?php echo ($selectedTeamStats['ast_per_game'] >= $averageTeamStats['ast_per_game']) ? 'higher' : 'lower'; ?>" style="width: <?php echo $selectedTeamWidth; ?>%; "></div>
                                <div class="bar <?php echo ($selectedTeamStats['ast_per_game'] < $averageTeamStats['ast_per_game']) ? 'higher' : 'lower'; ?>" style="width: <?php echo $averageTeamWidth; ?>%;"></div>
                            </div>
                        </td>
                    </tr>

                    <!-- STEALS -->
                    <tr>
                        <td>Steals per Game</td>
                        <td>
                            <div style="margin-bottom: 10px;">
                                <span style="float: left;">
                                    <?php echo number_format($selectedTeamStats['stl_per_game'], 2); ?>
                                </span>
                                <span style="float: right;">
                                    <?php echo number_format($averageTeamStats['stl_per_game'], 2); ?>
                                </span>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="bar-container">
                                <?php
                                $totalRebounds = $selectedTeamStats['stl_per_game'] + $averageTeamStats['stl_per_game'];
                                $selectedTeamWidth = ($selectedTeamStats['stl_per_game'] / $totalRebounds) * 100;
                                $averageTeamWidth = ($averageTeamStats['stl_per_game'] / $totalRebounds) * 100;
                                ?>
                                <div class="bar <?php echo ($selectedTeamStats['stl_per_game'] >= $averageTeamStats['stl_per_game']) ? 'higher' : 'lower'; ?>" style="width: <?php echo $selectedTeamWidth; ?>%; "></div>
                                <div class="bar <?php echo ($selectedTeamStats['stl_per_game'] < $averageTeamStats['stl_per_game']) ? 'higher' : 'lower'; ?>" style="width: <?php echo $averageTeamWidth; ?>%;"></div>
                            </div>
                        </td>
                    </tr>


                    <!-- BLOCKS -->
                    <tr>
                        <td>Blocks per Game</td>
                        <td>
                            <div style="margin-bottom: 10px;">
                                <span style="float: left;">
                                    <?php echo number_format($selectedTeamStats['blk_per_game'], 2); ?>
                                </span>
                                <span style="float: right;">
                                    <?php echo number_format($averageTeamStats['blk_per_game'], 2); ?>
                                </span>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="bar-container">
                                <?php
                                $totalRebounds = $selectedTeamStats['blk_per_game'] + $averageTeamStats['blk_per_game'];
                                $selectedTeamWidth = ($selectedTeamStats['blk_per_game'] / $totalRebounds) * 100;
                                $averageTeamWidth = ($averageTeamStats['blk_per_game'] / $totalRebounds) * 100;
                                ?>
                                <div class="bar <?php echo ($selectedTeamStats['blk_per_game'] >= $averageTeamStats['blk_per_game']) ? 'higher' : 'lower'; ?>" style="width: <?php echo $selectedTeamWidth; ?>%; "></div>
                                <div class="bar <?php echo ($selectedTeamStats['blk_per_game'] < $averageTeamStats['blk_per_game']) ? 'higher' : 'lower'; ?>" style="width: <?php echo $averageTeamWidth; ?>%;"></div>
                            </div>
                        </td>
                    </tr>


                    <!-- POINTS -->
                    <tr>
                        <td>Points per Game</td>
                        <td>
                            <div style="margin-bottom: 10px;">
                                <span style="float: left;">
                                    <?php echo number_format($selectedTeamStats['pts_per_game'], 2); ?>
                                </span>
                                <span style="float: right;">
                                    <?php echo number_format($averageTeamStats['pts_per_game'], 2); ?>
                                </span>
                                <div style="clear: both;"></div>
                            </div>
                            <div class="bar-container">
                                <?php
                                $totalRebounds = $selectedTeamStats['pts_per_game'] + $averageTeamStats['pts_per_game'];
                                $selectedTeamWidth = ($selectedTeamStats['pts_per_game'] / $totalRebounds) * 100;
                                $averageTeamWidth = ($averageTeamStats['pts_per_game'] / $totalRebounds) * 100;
                                ?>
                                <div class="bar <?php echo ($selectedTeamStats['pts_per_game'] >= $averageTeamStats['pts_per_game']) ? 'higher' : 'lower'; ?>" style="width: <?php echo $selectedTeamWidth; ?>%; "></div>
                                <div class="bar <?php echo ($selectedTeamStats['pts_per_game'] < $averageTeamStats['pts_per_game']) ? 'higher' : 'lower'; ?>" style="width: <?php echo $averageTeamWidth; ?>%;"></div>
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