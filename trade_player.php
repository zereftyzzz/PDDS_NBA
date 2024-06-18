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

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['name']) && !empty($_GET['name'])) {
    $playerName = $_GET['name'];
    
    $playerCursor = $collection->find(['player' => $playerName, 'season' => $year]);
    $playerData = iterator_to_array($playerCursor);
    
    if (!empty($playerData)) {
        $teamDetails = $teamCollection->findOne(['abbreviation' => $playerData[0]['tm']]);
        
        $playerStats = [
            'trb_per_game' => 0,
            'ast_per_game' => 0,
            'stl_per_game' => 0,
            'blk_per_game' => 0,
            'pts_per_game' => 0,
            'count' => count($playerData)
        ];

        foreach ($playerData as $player) {
            $playerStats['trb_per_game'] += $player['trb_per_game'];
            $playerStats['ast_per_game'] += $player['ast_per_game'];
            $playerStats['stl_per_game'] += $player['stl_per_game'];
            $playerStats['blk_per_game'] += $player['blk_per_game'];
            $playerStats['pts_per_game'] += $player['pts_per_game'];
        }

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
                'trb_per_game' => 0,
                'ast_per_game' => 0,
                'stl_per_game' => 0,
                'blk_per_game' => 0,
                'pts_per_game' => 0,
                'count' => count($yearData)
            ];

            foreach ($yearData as $player) {
                $yearStats['trb_per_game'] += $player['trb_per_game'];
                $yearStats['ast_per_game'] += $player['ast_per_game'];
                $yearStats['stl_per_game'] += $player['stl_per_game'];
                $yearStats['blk_per_game'] += $player['blk_per_game'];
                $yearStats['pts_per_game'] += $player['pts_per_game'];
            }

            foreach ($yearStats as $key => $value) {
                if ($key !== 'count') {
                    $averagePlayerStats[$key] = $value / $yearStats['count'];
                }
            }
        }

        // Calculate average team stats for the selected year
        $totalTeamStats = [
            'trb_per_game' => 0,
            'ast_per_game' => 0,
            'stl_per_game' => 0,
            'blk_per_game' => 0,
            'pts_per_game' => 0,
            'count' => 0
        ];

        foreach ($yearData as $player) {
            $totalTeamStats['trb_per_game'] += $player['trb_per_game'];
            $totalTeamStats['ast_per_game'] += $player['ast_per_game'];
            $totalTeamStats['stl_per_game'] += $player['stl_per_game'];
            $totalTeamStats['blk_per_game'] += $player['blk_per_game'];
            $totalTeamStats['pts_per_game'] += $player['pts_per_game'];
            $totalTeamStats['count']++;
        }

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
                    'trb_per_game' => 0,
                    'ast_per_game' => 0,
                    'stl_per_game' => 0,
                    'blk_per_game' => 0,
                    'pts_per_game' => 0,
                    'count' => count($teamStatsData)
                ];

                foreach ($teamStatsData as $player) {
                    $selectedTeamStats['trb_per_game'] += $player['trb_per_game'];
                    $selectedTeamStats['ast_per_game'] += $player['ast_per_game'];
                    $selectedTeamStats['stl_per_game'] += $player['stl_per_game'];
                    $selectedTeamStats['blk_per_game'] += $player['blk_per_game'];
                    $selectedTeamStats['pts_per_game'] += $player['pts_per_game'];
                }

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
        }

        form {
            margin-bottom: 20px;
        }

        label {
            margin-right: 10px;
        }

        select, input {
            margin-right: 10px;
        }

        button {
            padding: 5px 10px;
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
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 10px;
            text-align: right;
        }

        th {
            background-color: #f2f2f2;
        }

        .team-details {
            margin-top: 20px;
        }

        .team-details p {
            margin: 5px 0;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script>
        $(function() {
            $("#player_name").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "fetch_players.php",
                        data: { term: request.term },
                        dataType: "json",
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 1,
                select: function(event, ui) {
                    $('#player_name').val(ui.item.value);
                    return false;
                }
            });

            $('#year').change(function() {
                $('#search_form').submit();
            });

            $('#team').change(function() {
                $('#search_form').submit();
            });
        });
    </script>
</head>
<body>
    <form id="search_form" method="GET" action="">
        <label for="player_name">Player Name:</label>
        <input type="text" id="player_name" name="name" value="<?php echo isset($_GET['name']) ? $_GET['name'] : ''; ?>" />

        <label for="year">Year:</label>
        <select id="year" name="year">
            <option value="">Select Year</option>
            <?php for ($yr = 1947; $yr <= 2024; $yr++) : ?>
                <option value="<?php echo $yr; ?>" <?php if(isset($_GET['year']) && $_GET['year'] == $yr) echo 'selected'; ?>>
                    <?php echo $yr; ?>
                </option>
            <?php endfor; ?>
        </select>

        <label for="team">Select Team to Transfer:</label>
        <select id="team" name="team">
            <option value="">Select Team</option>
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
            <p><strong>Team Name:</strong> <?php echo htmlspecialchars($teamDetails['team']); ?></p>
            <p><strong>Abbreviation:</strong> <?php echo htmlspecialchars($teamDetails['abbreviation']); ?></p>
        </div>
    <?php endif; ?>

    <div class="tables-container">
        <?php if (!empty($selectedPlayerStats) && !empty($averagePlayerStats)) : ?>
            <table>
                <caption>Comparison with Average Player Stats in <?php echo $year; ?></caption>
                <thead>
                    <tr>
                        <th>Statistic</th>
                        <th>Player's Value</th>
                        <th>Average Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Rebounds per Game</td>
                        <td><?php echo number_format($selectedPlayerStats['trb_per_game'], 2); ?></td>
                        <td><?php echo number_format($averagePlayerStats['trb_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Assists per Game</td>
                        <td><?php echo number_format($selectedPlayerStats['ast_per_game'], 2); ?></td>
                        <td><?php echo number_format($averagePlayerStats['ast_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Steals per Game</td>
                        <td><?php echo number_format($selectedPlayerStats['stl_per_game'], 2); ?></td>
                        <td><?php echo number_format($averagePlayerStats['stl_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Blocks per Game</td>
                        <td><?php echo number_format($selectedPlayerStats['blk_per_game'], 2); ?></td>
                        <td><?php echo number_format($averagePlayerStats['blk_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Points per Game</td>
                        <td><?php echo number_format($selectedPlayerStats['pts_per_game'], 2); ?></td>
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
                        <th>Team's Value</th>
                        <th>Average Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Rebounds per Game</td>
                        <td><?php echo number_format($selectedTeamStats['trb_per_game'], 2); ?></td>
                        <td><?php echo number_format($averageTeamStats['trb_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Assists per Game</td>
                        <td><?php echo number_format($selectedTeamStats['ast_per_game'], 2); ?></td>
                        <td><?php echo number_format($averageTeamStats['ast_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Steals per Game</td>
                        <td><?php echo number_format($selectedTeamStats['stl_per_game'], 2); ?></td>
                        <td><?php echo number_format($averageTeamStats['stl_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Blocks per Game</td>
                        <td><?php echo number_format($selectedTeamStats['blk_per_game'], 2); ?></td>
                        <td><?php echo number_format($averageTeamStats['blk_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Points per Game</td>
                        <td><?php echo number_format($selectedTeamStats['pts_per_game'], 2); ?></td>
                        <td><?php echo number_format($averageTeamStats['pts_per_game'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

