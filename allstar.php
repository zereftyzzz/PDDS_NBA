<?php

require_once 'autoload.php';

// Connect to the MongoDB client
$client = new MongoDB\Client();

// Select the database and collection
$collection = $client->pdds_proyek->player;
$teamCollection = $client->pdds_proyek->team;

$tempWin = 0;
$allStar = 0.0;

// Initialize the filter array
$filter = [];

// Initialize players and topPlayers arrays
$players = [];
$topPlayers = [];
$averageStats = [
    'trb_per_game' => 0.0,
    'ast_per_game' => 0.0,
    'stl_per_game' => 0.0,
    'blk_per_game' => 0.0,
    'pts_per_game' => 0.0,
];

// Initialize a flag to indicate whether to fetch data
$shouldFetchData = false;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Check if any filter is set
    if (!empty($_GET['name']) || !empty($_GET['position']) || !empty($_GET['year'])) {
        $shouldFetchData = true;

        // Apply filters based on input
        if (!empty($_GET['name'])) {
            $filter['player'] = ['$regex' => $_GET['name'], '$options' => 'i'];
        }
        if (!empty($_GET['position'])) {
            $filter['pos'] = $_GET['position'];
        }
        if (!empty($_GET['year'])) {
            $filter['season'] = (int)$_GET['year'];
        }

        // Fetch filtered documents from the collection
        $playersCursor = $collection->find($filter);
        $players = $playersCursor->toArray();

        // Check if year filter is set to fetch top 10 players by points per game
        if (!empty($_GET['year'])) {
            $topFilter = [
                'season' => (int)$_GET['year'],
            ];

            // Fetch top 10 players by points per game for the specified year
            $topPlayersCursor = $collection->find($topFilter, [
                'sort' => ['pts_per_game' => -1],
                'limit' => 10
            ]);

            // Convert cursor to array
            $topPlayers = $topPlayersCursor->toArray();

            // Calculate average stats for top 10 players
            $totalPlayers = count($topPlayers);
            foreach ($topPlayers as $player) {
                $averageStats['trb_per_game'] += (float)$player['trb_per_game'];
                $averageStats['ast_per_game'] += (float)$player['ast_per_game'];
                $averageStats['stl_per_game'] += (float)$player['stl_per_game'];
                $averageStats['blk_per_game'] += (float)$player['blk_per_game'];
                $averageStats['pts_per_game'] += (float)$player['pts_per_game'];
            }

            if ($totalPlayers > 0) {
                $averageStats['trb_per_game'] /= (float)$totalPlayers;
                $averageStats['ast_per_game'] /= (float)$totalPlayers;
                $averageStats['stl_per_game'] /= (float)$totalPlayers;
                $averageStats['blk_per_game'] /= (float)$totalPlayers;
                $averageStats['pts_per_game'] /= (float)$totalPlayers;
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
    <title>Player Data</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            margin-right: 10px;
        }

        input,
        select {
            margin-right: 10px;
        }

        button {
            padding: 5px 10px;
        }

        .highlight-green {
            background-color: #d4edda;
        }

        .highlight-red {
            background-color: #f8d7da;
        }
    </style>
</head>

<body>
    <form method="GET" action="">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="">

        <label for="position">Position:</label>
        <select id="position" name="position">
            <option value="">All</option>
            <option value="PG">PG</option>
            <option value="SG">SG</option>
            <option value="SF">SF</option>
            <option value="PF">PF</option>
            <option value="C">C</option>
        </select>

        <label for="year">Year:</label>
        <select id="year" name="year">
            <option value="">All</option>
            <?php for ($year = 1947; $year <= 2024; $year++) : ?>
                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
            <?php endfor; ?>
        </select>

        <button type="submit">Search</button>
    </form>

    <?php if ($shouldFetchData) : ?>
        <?php if (count($players) === 1) : ?>
            <?php $player = $players[0]; ?>
            <table>
                <thead>
                    <tr>
                        <th>Player Name</th>
                        <th>Position</th>
                        <th>Age</th>
                        <th>Experience</th>
                        <th>Season</th>
                        <th>Team</th>
                        <th>Rebound</th>
                        <th>Assist</th>
                        <th>Steal</th>
                        <th>Block</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $teamDetails = $teamCollection->findOne(['abbreviation' => $player['tm']]);
                    $teamName = $teamDetails ? $teamDetails['team'] : 'Unknown Team';
                    $teamWinPercentage = $teamDetails ? round(($teamDetails['w'] / ($teamDetails['w'] + $teamDetails['l'])) * 100, 2) : 'N/A';
                    $highlightRebound = $player['trb_per_game'] > $averageStats['trb_per_game'] ? 'highlight-green' : 'highlight-red';
                    $highlightAssist = $player['ast_per_game'] > $averageStats['ast_per_game'] ? 'highlight-green' : 'highlight-red';
                    $highlightSteal = $player['stl_per_game'] > $averageStats['stl_per_game'] ? 'highlight-green' : 'highlight-red';
                    $highlightBlock = $player['blk_per_game'] > $averageStats['blk_per_game'] ? 'highlight-green' : 'highlight-red';
                    $highlightPoints = $player['pts_per_game'] > $averageStats['pts_per_game'] ? 'highlight-green' : 'highlight-red';

                    $tempWin += ($player['trb_per_game'] > $averageStats['trb_per_game']) ? 1 : 0;
                    $tempWin += ($player['ast_per_game'] > $averageStats['ast_per_game']) ? 1 : 0;
                    $tempWin += ($player['stl_per_game'] > $averageStats['stl_per_game']) ? 1 : 0;
                    $tempWin += ($player['blk_per_game'] > $averageStats['blk_per_game']) ? 1 : 0;
                    $tempWin += ($player['pts_per_game'] > $averageStats['pts_per_game']) ? 1 : 0;
                    $allStar = round((float)($tempWin / 5) * $teamWinPercentage, 2)
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($player['player'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($player['pos'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($player['age'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($player['experience'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($player['season'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($player['tm'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="<?php echo $highlightRebound; ?>"><?php echo htmlspecialchars($player['trb_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="<?php echo $highlightAssist; ?>"><?php echo htmlspecialchars($player['ast_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="<?php echo $highlightSteal; ?>"><?php echo htmlspecialchars($player['stl_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="<?php echo $highlightBlock; ?>"><?php echo htmlspecialchars($player['blk_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="<?php echo $highlightPoints; ?>"><?php echo htmlspecialchars($player['pts_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                </tbody>
            </table>
            <p><strong>Team:</strong> <?php echo htmlspecialchars($teamName, ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Team Win:</strong> <?php echo htmlspecialchars($teamWinPercentage, ENT_QUOTES, 'UTF-8'); ?>%</p>
            <p><strong>All-Star Probability:</strong> <?php echo htmlspecialchars($allStar, ENT_QUOTES, 'UTF-8'); ?>%</p>
        <?php elseif (!empty($players)) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Player Name</th>
                        <th>Position</th>
                        <th>Age</th>
                        <th>Experience</th>
                        <th>Season</th>
                        <th>Team</th>
                        <th>Rebound</th>
                        <th>Assist</th>
                        <th>Steal</th>
                        <th>Block</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($players as $player) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($player['player'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['pos'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['age'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['experience'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['season'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['tm'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['trb_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['ast_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['stl_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['blk_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['pts_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if (!empty($topPlayers)) : ?>
            <h2>Top 10 Players by Points per Game in <?php echo htmlspecialchars($_GET['year'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Player Name</th>
                        <th>Position</th>
                        <th>Age</th>
                        <th>Experience</th>
                        <th>Season</th>
                        <th>Team</< /th>
                        <th>Rebound</th>
                        <th>Assist</th>
                        <th>Steal</th>
                        <th>Block</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topPlayers as $player) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($player['player'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['pos'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['age'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['experience'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['season'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['tm'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['trb_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['ast_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['stl_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['blk_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($player['pts_per_game'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>Top 10 Stats in <?php echo htmlspecialchars($_GET['year'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Stats</th>
                        <th>Value</< /th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Rebound</td>
                        <td><?php echo htmlspecialchars(number_format($averageStats['trb_per_game'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td>Assist</td>
                        <td><?php echo htmlspecialchars(number_format($averageStats['ast_per_game'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td>Steal</td>
                        <td><?php echo htmlspecialchars(number_format($averageStats['stl_per_game'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td>Block</td>
                        <td><?php echo htmlspecialchars(number_format($averageStats['blk_per_game'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td>Points</td>
                        <td><?php echo htmlspecialchars(number_format($averageStats['pts_per_game'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    <?php else : ?>
        <p>Please select at least one filter to search for players.</p>
    <?php endif; ?>
</body>

</html>