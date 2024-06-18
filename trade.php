<?php
include 'navbar2.php';
require_once 'autoload.php';

$client = new MongoDB\Client();
$collection = $client->pdds_proyek->player;
$teamCollection = $client->pdds_proyek->team;

$selectedPlayerStats = [];
$averagePlayerStats = [];
$selectedTeamStats = [];
$averageTeamStats = [];
$teamDetails = null;
$year = isset($_GET['year']) ? intval($_GET['year']) : null;
$selectedTeam = isset($_GET['team']) ? $_GET['team'] : null;

function safeSum($array, $key) {
    $sum = 0;
    foreach ($array as $item) {
        $value = isset($item[$key]) ? floatval($item[$key]) : 0;
        $sum += $value;
    }
    return $sum;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['name']) && !empty($_GET['name'])) {
    $playerName = $_GET['name'];
    
    $playerCursor = $collection->find(['player' => $playerName, 'season' => $year]);
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
        // Calculate average player stats for the selected year
        $yearCursor = $collection->find(['season' => $year]);
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

        // Calculate average team stats for the selected year
        $totalTeamStats = [
            'trb_per_game' => safeSum($yearData, 'trb_per_game'),
            'ast_per_game' => safeSum($yearData, 'ast_per_game'),
            'stl_per_game' => safeSum($yearData, 'stl_per_game'),
            'blk_per_game' => safeSum($yearData, 'blk_per_game'),
            'pts_per_game' => safeSum($yearData, 'pts_per_game'),
            'count' => count($yearData)
        ];

        foreach ($totalTeamStats as $key => $value) {
            if ($key !== 'count') {
                $averageTeamStats[$key] = $value / $totalTeamStats['count'];
            }
        }

        // Retrieve stats for the selected team
        if ($selectedTeam) {
            $teamStatsCursor = $collection->find(['tm' => $selectedTeam, 'season' => $year]);
            $teamStatsData = iterator_to_array($teamStatsCursor);

            if (!empty($teamStatsData)) {
                $selectedTeamStats = [
                    'trb_per_game' => safeSum($teamStatsData, 'trb_per_game'),
                    'ast_per_game' => safeSum($teamStatsData, 'ast_per_game'),
                    'stl_per_game' => safeSum($teamStatsData, 'stl_per_game'),
                    'blk_per_game' => safeSum($teamStatsData, 'blk_per_game'),
                    'pts_per_game' => safeSum($teamStatsData, 'pts_per_game'),
                    'count' => count($teamStatsData)
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

        select, input {
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

        th, td {
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
            background-color: #f2f2f2;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }

        .bar {
            height: 100%;
            border-radius: 4px;
        }

        .higher {
            background-color: #28a745; /* green */
        }

        .lower {
            background-color: #dc3545; /* red */
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
                <option value="<?php echo $yr; ?>" <?php if(isset($_GET['year']) && $_GET['year'] == $yr) echo 'selected'; ?>>
                    <?php echo $yr; ?>
                </option>
            <?php endfor; ?>
        </select>

        <label for="player_name">Player Name:</label>
        <input type="text" id="player_name" name="name" value="<?php echo isset($_GET['name']) ? $_GET['name'] : ''; ?>" />

        <label for="team">Select Team:</label>
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
            <p><strong>Team Name:</strong> <span><?php echo htmlspecialchars($teamDetails['team']); ?></span></p>
            <p><strong>Abbreviation:</strong> <span><?php echo htmlspecialchars($teamDetails['abbreviation']); ?></span></p>
        </div>
    <?php endif; ?>

    <div class="tables-container">
        <?php if (!empty($selectedPlayerStats) && !empty($averagePlayerStats)) : ?>
            <table>
                <caption>Comparison with Average Player Stats in <?php echo $year; ?></caption>
                <thead>
                    <tr>
                        <th>Statistic</th>
                        <th><?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) . "'s Stat" : "Player's Stat"; ?></th>
                        <th>Average Player's Stat</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Rebounds per Game</td>
                        <td>
                            <?php echo number_format($selectedPlayerStats['trb_per_game'], 2); ?>
                            <div class="bar-container">
                                <div class="bar <?php echo $selectedPlayerStats['trb_per_game'] > $averagePlayerStats['trb_per_game'] ? 'higher' : 'lower'; ?>" style="width: <?php echo abs(($selectedPlayerStats['trb_per_game'] / $averagePlayerStats['trb_per_game']) * 100); ?>%;"></div>
                            </div>
                        </td>
                        <td><?php echo number_format($averagePlayerStats['trb_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Assists per Game</td>
                        <td>
                            <?php echo number_format($selectedPlayerStats['ast_per_game'], 2); ?>
                            <div class="bar-container">
                                <div class="bar <?php echo $selectedPlayerStats['ast_per_game'] > $averagePlayerStats['ast_per_game'] ? 'higher' : 'lower'; ?>" style="width: <?php echo abs(($selectedPlayerStats['ast_per_game'] / $averagePlayerStats['ast_per_game']) * 100); ?>%;"></div>
                            </div>
                        </td>
                        <td><?php echo number_format($averagePlayerStats['ast_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Steals per Game</td>
                        <td>
                            <?php echo number_format($selectedPlayerStats['stl_per_game'], 2); ?>
                            <div class="bar-container">
                                <div class="bar <?php echo $selectedPlayerStats['stl_per_game'] > $averagePlayerStats['stl_per_game'] ? 'higher' : 'lower'; ?>" style="width: <?php echo abs(($selectedPlayerStats['stl_per_game'] / $averagePlayerStats['stl_per_game']) * 100); ?>%;"></div>
                            </div>
                        </td>
                        <td><?php echo number_format($averagePlayerStats['stl_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Blocks per Game</td>
                        <td>
                            <?php echo number_format($selectedPlayerStats['blk_per_game'], 2); ?>
                            <div class="bar-container">
                                <div class="bar <?php echo $selectedPlayerStats['blk_per_game'] > $averagePlayerStats['blk_per_game'] ? 'higher' : 'lower'; ?>" style="width: <?php echo abs(($selectedPlayerStats['blk_per_game'] / $averagePlayerStats['blk_per_game']) * 100); ?>%;"></div>
                            </div>
                        </td>
                        <td><?php echo number_format($averagePlayerStats['blk_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Points per Game</td>
                        <td>
                            <?php echo number_format($selectedPlayerStats['pts_per_game'], 2); ?>
                            <div class="bar-container">
                                <div class="bar <?php echo $selectedPlayerStats['pts_per_game'] > $averagePlayerStats['pts_per_game'] ? 'higher' : 'lower'; ?>" style="width: <?php echo abs(($selectedPlayerStats['pts_per_game'] / $averagePlayerStats['pts_per_game']) * 100); ?>%;"></div>
                            </div>
                        </td>
                        <td><?php echo number_format($averagePlayerStats['pts_per_game'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if (!empty($selectedTeamStats) && !empty($averageTeamStats)) : ?>
            <table>
                <caption>Comparison with Average Team Stats in <?php echo $year; ?></caption>
                <thead>
                    <tr>
                        <th>Statistic</th>
                        <th><?php echo isset($_GET['team']) ? htmlspecialchars($_GET['team']) . "'s Stat" : "Team's Value"; ?></th>
                        <th>Average Team's Stat</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Rebounds per Game</td>
                        <td>
                            <?php echo number_format($selectedTeamStats['trb_per_game'], 2); ?>
                            <div class="bar-container">
                                <div class="bar" style="width: <?php echo abs(($selectedTeamStats['trb_per_game'] / $averageTeamStats['trb_per_game']) * 100); ?>%;"></div>
                            </div>
                        </td>
                        <td><?php echo number_format($averageTeamStats['trb_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Assists per Game</td>
                        <td>
                            <?php echo number_format($selectedTeamStats['ast_per_game'], 2); ?>
                            <div class="bar-container">
                                <div class="bar" style="width: <?php echo abs(($selectedTeamStats['ast_per_game'] / $averageTeamStats['ast_per_game']) * 100); ?>%;"></div>
                            </div>
                        </td>
                        <td><?php echo number_format($averageTeamStats['ast_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Steals per Game</td>
                        <td>
                            <?php echo number_format($selectedTeamStats['stl_per_game'], 2); ?>
                            <div class="bar-container">
                                <div class="bar" style="width: <?php echo abs(($selectedTeamStats['stl_per_game'] / $averageTeamStats['stl_per_game']) * 100); ?>%;"></div>
                            </div>
                        </td>
                        <td><?php echo number_format($averageTeamStats['stl_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Blocks per Game</td>
                        <td>
                            <?php echo number_format($selectedTeamStats['blk_per_game'], 2); ?>
                            <div class="bar-container">
                                <div class="bar" style="width: <?php echo abs(($selectedTeamStats['blk_per_game'] / $averageTeamStats['blk_per_game']) * 100); ?>%;"></div>
                            </div>
                        </td>
                        <td><?php echo number_format($averageTeamStats['blk_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Points per Game</td>
                        <td>
                            <?php echo number_format($selectedTeamStats['pts_per_game'], 2); ?>
                            <div class="bar-container">
                                <div class="bar" style="width: <?php echo abs(($selectedTeamStats['pts_per_game'] / $averageTeamStats['pts_per_game']) * 100); ?>%;"></div>
                            </div>
                        </td>
                        <td><?php echo number_format($averageTeamStats['pts_per_game'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
