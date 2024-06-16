<?php
require_once 'autoload.php';
include '../navbar.php';

// Connect to the MongoDB client
$client = new MongoDB\Client();
$playerCollection = $client->pdds_proyek->player;
$teamCollection = $client->pdds_proyek->team;
$tstatsCollection = $client->pdds_proyek->tstats;
$ostatsCollection = $client->pdds_proyek->ostats;

// Fetch seasons
  $seasons = $teamCollection->distinct('season');
  rsort($seasons);

// Initialize data
  $players = [];
  $teamWins = 100;
  $teamLosses = 0;

// Submitted
  if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['season']) && isset($_GET['team'])) {
      $selectedSeason = $_GET['season'];
      $selectedTeam = $_GET['team'];

      // Fetch team data based on the selected team and season
      $teamFilter = ['abbreviation' => $selectedTeam, 'season' => (int)$selectedSeason];
      $teamData = $teamCollection->findOne($teamFilter);

      if ($teamData) {
          $teamWins = $teamData['w'];
          $teamLosses = $teamData['l'];
      }

      // Fetch players from the player collection based on the selected team and season
      $filter = ['tm' => $selectedTeam, 'season' => (int)$selectedSeason];
      $options = []; // Sort option removed to handle sorting in PHP

      $cursor = $playerCollection->find($filter, $options);

      // Store the data in an array
      foreach ($cursor as $document) {
          $players[] = $document;
      }

      // Sort the array by mp_per_game in descending order
      usort($players, function($a, $b) {
          return $b['mp_per_game'] <=> $a['mp_per_game'];
      });

      // Output player data (for debugging or display purposes)
      foreach ($players as $player) {
          $name = isset($player['player']) ? htmlspecialchars($player['player'], ENT_QUOTES, 'UTF-8') : 'N/A';
          $mpPerGame = isset($player['mp_per_game']) ? htmlspecialchars($player['mp_per_game'], ENT_QUOTES, 'UTF-8') : 'N/A';
      }
  }

// Team Name
  $teamName = 'Team';

  // Fetch team information based on the submitted abbreviation
  $abbreviation = isset($_GET['team']) ? $_GET['team'] : null;

  if ($abbreviation) {
      // Query to find the team based on abbreviation
      $filter = ['abbreviation' => $abbreviation];
      $team = $teamCollection->findOne($filter);

      // Display team name if found
      if ($team) {
          $teamName = isset($team['team']) ? htmlspecialchars($team['team'], ENT_QUOTES, 'UTF-8') : 'Team Not Found'; // Replace 'team' with your actual field name for team name
      } else {
          $teamName = 'Team Not Found';
      }
  }

// Stats
  $tstats_fg_per_game = 'N/A';
  $tstats_fga_per_game = 'N/A';
  $tstats_x3p_per_game = 'N/A';
  $tstats_x3pa_per_game = 'N/A';
  $tstats_ft_per_game = 'N/A';
  $tstats_fta_per_game = 'N/A';
  $tstats_trb_per_game = 'N/A';
  $tstats_ast_per_game = 'N/A';
  $tstats_stl_per_game = 'N/A';
  
  $ostats_fg_per_game = 'N/A';
  $ostats_fga_per_game = 'N/A';
  $ostats_x3p_per_game = 'N/A';
  $ostats_x3pa_per_game = 'N/A';
  $ostats_ft_per_game = 'N/A';
  $ostats_fta_per_game = 'N/A';
  $ostats_trb_per_game = 'N/A';
  $ostats_ast_per_game = 'N/A';
  $ostats_stl_per_game = 'N/A';
  
  // Team Stats (tstats)
  if (isset($_GET['season']) && isset($_GET['team'])) {
      $selectedSeason = (int)$_GET['season'];
      $selectedTeam = $_GET['team'];
  
      // Define the filter based on season and team abbreviation
      $tstats_filter = [
          'season' => $selectedSeason,
          'abbreviation' => $selectedTeam
      ];
  
      // Specify the fields you want to retrieve
      $tstats_projection = [
          'fg_per_game' => 1,
          'fga_per_game' => 1,
          'x3p_per_game' => 1,
          'x3pa_per_game' => 1,
          'ft_per_game' => 1,
          'fta_per_game' => 1,
          'trb_per_game' => 1,
          'ast_per_game' => 1,
          'stl_per_game' => 1,
          '_id' => 0 // Exclude the _id field from the result
      ];
  
      // Query the MongoDB collection to fetch the data
      $tstatsData = $tstatsCollection->findOne($tstats_filter, ['projection' => $tstats_projection]);
  
      // Check if data is found for the specified season and team
      if ($tstatsData) {
          // Extract the statistics from the retrieved document
          $tstats_fg_per_game = isset($tstatsData['fg_per_game']) ? $tstatsData['fg_per_game'] : 'N/A';
          $tstats_fga_per_game = isset($tstatsData['fga_per_game']) ? $tstatsData['fga_per_game'] : 'N/A';
          $tstats_x3p_per_game = isset($tstatsData['x3p_per_game']) ? $tstatsData['x3p_per_game'] : 'N/A';
          $tstats_x3pa_per_game = isset($tstatsData['x3pa_per_game']) ? $tstatsData['x3pa_per_game'] : 'N/A';
          $tstats_ft_per_game = isset($tstatsData['ft_per_game']) ? $tstatsData['ft_per_game'] : 'N/A';
          $tstats_fta_per_game = isset($tstatsData['fta_per_game']) ? $tstatsData['fta_per_game'] : 'N/A';
          $tstats_trb_per_game = isset($tstatsData['trb_per_game']) ? $tstatsData['trb_per_game'] : 'N/A';
          $tstats_ast_per_game = isset($tstatsData['ast_per_game']) ? $tstatsData['ast_per_game'] : 'N/A';
          $tstats_stl_per_game = isset($tstatsData['stl_per_game']) ? $tstatsData['stl_per_game'] : 'N/A';
      }
  }
  
  // Opponent Stats (ostats)
  if (isset($_GET['season']) && isset($_GET['team'])) {
      $selectedSeason = (int)$_GET['season'];
      $selectedTeam = $_GET['team'];
  
      // Define the filter based on season and team abbreviation
      $ostats_filter = [
          'season' => $selectedSeason,
          'abbreviation' => $selectedTeam
      ];
  
      // Specify the fields you want to retrieve
      $ostats_projection = [
          'opp_fg_per_game' => 1,
          'opp_fga_per_game' => 1,
          'opp_x3p_per_game' => 1,
          'opp_x3pa_per_game' => 1,
          'opp_ft_per_game' => 1,
          'opp_fta_per_game' => 1,
          'opp_trb_per_game' => 1,
          'opp_ast_per_game' => 1,
          'opp_stl_per_game' => 1,
          '_id' => 0 // Exclude the _id field from the result
      ];
  
      // Query the MongoDB collection to fetch the data
      $ostatsData = $ostatsCollection->findOne($ostats_filter, ['projection' => $ostats_projection]);
  
      // Check if data is found for the specified season and team
      if ($ostatsData) {
          // Extract the statistics from the retrieved document
          $ostats_fg_per_game = isset($ostatsData['opp_fg_per_game']) ? $ostatsData['opp_fg_per_game'] : 'N/A';
          $ostats_fga_per_game = isset($ostatsData['opp_fga_per_game']) ? $ostatsData['opp_fga_per_game'] : 'N/A';
          $ostats_x3p_per_game = isset($ostatsData['opp_x3p_per_game']) ? $ostatsData['opp_x3p_per_game'] : 'N/A';
          $ostats_x3pa_per_game = isset($ostatsData['opp_x3pa_per_game']) ? $ostatsData['opp_x3pa_per_game'] : 'N/A';
          $ostats_ft_per_game = isset($ostatsData['opp_ft_per_game']) ? $ostatsData['opp_ft_per_game'] : 'N/A';
          $ostats_fta_per_game = isset($ostatsData['opp_fta_per_game']) ? $ostatsData['opp_fta_per_game'] : 'N/A';
          $ostats_trb_per_game = isset($ostatsData['opp_trb_per_game']) ? $ostatsData['opp_trb_per_game'] : 'N/A';
          $ostats_ast_per_game = isset($ostatsData['opp_ast_per_game']) ? $ostatsData['opp_ast_per_game'] : 'N/A';
          $ostats_stl_per_game = isset($ostatsData['opp_stl_per_game']) ? $ostatsData['opp_stl_per_game'] : 'N/A';
      }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xq1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha384-FC8/UxiVpH5WqD7GKhbp/w+AYxz6N/1ly3V+p4n7KkMi0Py7mB2n+5mvVCe6Pz4u" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="home.css">
  
  <title>PDDS KEL 2</title>
</head>

<body>

<!-- Title  -->
  <h1 style="justify-content: center; align-items: center; display: flex; margin-top:30px;">NBA Data Modelling for Data Science Project</h1>
<!-- Season Dropdown -->
  <div class="container" style="margin-top: 20px;justify-content: center; align-items: center;">
    <div class="row justify-content-center">
      <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row col-8">
        
        <div class="col-md-4 mb-3">
            <label for="season">Select Season:</label>
            <select id="season" name="season" class="form-control" onchange="checkFormValidity()">
                <option value="">Select a season</option>
                <?php foreach ($seasons as $season) : ?>
                    <option value="<?= htmlspecialchars($season, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($season, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4 mb-3">
            <label for="team">Select Team:</label>
            <select id="team" name="team" class="form-control" onchange="checkFormValidity()">
                <option value="">Select a team</option>
                <?php
                // Default season for team selection (adjust as needed)
                $season = 2010;

                $filter = ['season' => $season];
                $options = ['projection' => ['abbreviation' => 1, '_id' => 0]];

                // Use find to fetch data
                $cursor = $teamCollection->find($filter, $options);

                // Iterate cursor to print abbreviations as dropdown options
                foreach ($cursor as $document) {
                    $abbreviation = htmlspecialchars($document['abbreviation'], ENT_QUOTES, 'UTF-8');
                    echo '<option value="' . $abbreviation . '">' . $abbreviation . '</option>';
                }
                ?>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="col-md-2 mb-3" style="margin-top:32px;">
            <button type="submit" id="submitBtn" class="btn btn-dark" disabled>Submit</button>
        </div>

        <div class="col" style="margin-top:32px;">
            <a href="starmap.php" class="btn btn-dark">★</a>
        </div>

      </form>

      <!-- <div class="col-2">
        <div class="row">
          <label>File</label>
        </div>
        <div class="row">
          <a href="starmap.php" class="btn btn-dark">★</a>
        </div>
      </div> -->

    </div>
  </div>

<!-- Submit -->
  <script>
    function checkFormValidity() {
      var seasonValue = document.getElementById('season').value;
      var teamValue = document.getElementById('team').value;
      var submitBtn = document.getElementById('submitBtn');

      // Enable submit button if both dropdowns have selections
      submitBtn.disabled = !(seasonValue && teamValue);
    }
  </script>


<!-- Player -->
  <?php
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

  $i = 1;
  foreach ($players as $player) {
      if ($i > 10) break;
      $name = isset($player['player']) ? htmlspecialchars($player['player'], ENT_QUOTES, 'UTF-8') : 'N/A';
      
      // Truncate the name to 12 characters and add ellipsis if it's longer
      $truncatedName = strlen($name) > 16 ? substr($name, 0, 16) . '..' : $name;
      
      $left = $playerPositions['p'.$i]['left'];
      $top = $playerPositions['p'.$i]['top'];
      echo "<p id='p$i' style='position:absolute; margin-left:{$left}px; margin-top:{$top}px; font-weight:bold;'>{$truncatedName}</p>";
      $i++;
  }
  ?>


<!-- Gambar Lapangan -->
  <div class="image-container" style="margin-top40px;">
    <img src="../assets/bg_home.png" class="centered-image" style="height:500px">
  </div>

<!-- Progress Bar -->
  <?php
  $Win_percent = number_format(($teamWins / ($teamWins + $teamLosses)) * 100, 1);
  $Lose_percent = number_format(100 - $Win_percent, 1);

  ?>

  <div class="progress-container">
    <span id="progress-text1"><?= $Win_percent ?>%</span>
    <progress id="progress" value="<?= $Win_percent ?>" max="100"></progress>
    <span id="progress-text2"><?= $Lose_percent ?>%</span>
  </div>

<!-- Display team name -->
  <div class="container" style="margin-top: 40px; width: 46%;">
    <h2 style="display: inline-block; margin-right: 20px;">
      <?php echo $teamName; ?>, <?php echo isset($_GET['season']) && $_GET['season'] !== '' ? htmlspecialchars($_GET['season'], ENT_QUOTES, 'UTF-8') . ' ' : ''; ?>
    </h2>
    <h4 style="display: inline-block;">
      ( <?php echo $teamWins === 100 ? 0 : $teamWins; ?>
      - <?php echo $teamLosses; ?> )
    </h4>
  </div>

<!-- Reserve -->
  <div class="container" style="margin-top: 20px;width:46%;">
      <h3 style="margin-bottom:10px;">Reserve</h3>
      <table class="table table-bordered">
          <thead>
              <tr>
                  <th>Player Name</th>
                  <th>Minute per Game</th>
              </tr>
          </thead>
          <tbody>
              <?php
              // Print remaining players in a table
              for ($j = 10; $j < count($players); $j++) {
                  $name = isset($players[$j]['player']) ? htmlspecialchars($players[$j]['player'], ENT_QUOTES, 'UTF-8') : 'N/A';
                  $mpPerGame = isset($players[$j]['mp_per_game']) ? htmlspecialchars($players[$j]['mp_per_game'], ENT_QUOTES, 'UTF-8') : 'N/A';
                  echo "<tr><td>{$name}</td><td>{$mpPerGame}</td></tr>";
              }
              ?>
          </tbody>
      </table>
  </div>

<!-- Stats -->
  <div class="container" style="margin-top: 30px;width:46%;">
  <h3 style="margin-top:40px;margin-bottom:40px;justify-content: center; align-items: center; display: flex;">Team vs Opponent Statistics</h3>

  <!-- FG -->
    <div class="row" >
      <div class="col-2">
        <p><?php echo $tstats_fg_per_game; ?></p>
      </div>

      <div class="col-8" style="justify-content: center; align-items: center; display: flex;">
        <p>Field Goals Made</p>
      </div>

      <div class="col-2" style="justify-content: right; align-items: right; display: flex;">
        <p><?php echo $ostats_fg_per_game; ?></p>
      </div>
    </div>

  <!-- FGA -->
    <div class="row" >
      <div class="col-2">
        <p><?php echo $tstats_fga_per_game; ?></p>
      </div>

      <div class="col-8" style="justify-content: center; align-items: center; display: flex;">
        <p>Field Goals Attempted</p>
      </div>

      <div class="col-2" style="justify-content: right; align-items: right; display: flex;">
        <p><?php echo $ostats_fga_per_game; ?></p>
      </div>
    </div>

  <!-- 3P -->
    <div class="row" style="margin-top:20px;">
      <div class="col-2">
        <p><?php echo $tstats_x3p_per_game; ?></p>
      </div>

      <div class="col-8" style="justify-content: center; align-items: center; display: flex;">
        <p>Three Point Made</p>
      </div>

      <div class="col-2" style="justify-content: right; align-items: right; display: flex;">
        <p><?php echo $ostats_x3p_per_game; ?></p>
      </div>
    </div>
  <!-- 3PA -->
    <div class="row" >
      <div class="col-2">
        <p><?php echo $tstats_x3pa_per_game; ?></p>
      </div>

      <div class="col-8" style="justify-content: center; align-items: center; display: flex;">
        <p>Three Point Attempted</p>
      </div>

      <div class="col-2" style="justify-content: right; align-items: right; display: flex;">
        <p><?php echo $ostats_x3pa_per_game; ?></p>
      </div>
    </div>
  <!-- FT -->
    <div class="row" style="margin-top:20px;">
      <div class="col-2">
        <p><?php echo $tstats_ft_per_game; ?></p>
      </div>

      <div class="col-8" style="justify-content: center; align-items: center; display: flex;">
        <p>Free Throw Made</p>
      </div>

      <div class="col-2" style="justify-content: right; align-items: right; display: flex;">
        <p><?php echo $ostats_ft_per_game; ?></p>
      </div>
    </div>
  <!-- FTA -->
    <div class="row" >
      <div class="col-2">
        <p><?php echo $tstats_fta_per_game; ?></p>
      </div>

      <div class="col-8" style="justify-content: center; align-items: center; display: flex;">
        <p>Free Throw Attempted</p>
      </div>

      <div class="col-2" style="justify-content: right; align-items: right; display: flex;">
        <p><?php echo $ostats_fta_per_game; ?></p>
      </div>
    </div>
  
  <hr style="width:100%;text-align:left;margin-left:0;background-color:#222;">
  
  <!-- RB -->
    <div class="row" style="margin-top:40px;">
        <div class="col-2">
          <p><?php echo $tstats_trb_per_game; ?></p>
        </div>

        <div class="col-8" style="justify-content: center; align-items: center; display: flex;">
          <p>Rebound Made</p>
        </div>

        <div class="col-2" style="justify-content: right; align-items: right; display: flex;">
          <p><?php echo $ostats_trb_per_game; ?></p>
        </div>
    </div>

  <!-- AST -->
    <div class="row">
        <div class="col-2">
          <p><?php echo $tstats_ast_per_game; ?></p>
        </div>

        <div class="col-8" style="justify-content: center; align-items: center; display: flex;">
          <p>Assist Made</p>
        </div>

        <div class="col-2" style="justify-content: right; align-items: right; display: flex;">
          <p><?php echo $ostats_ast_per_game; ?></p>
        </div>
    </div>

  <!-- STL -->
    <div class="row">
        <div class="col-2">
          <p><?php echo $tstats_stl_per_game; ?></p>
        </div>

        <div class="col-8" style="justify-content: center; align-items: center; display: flex;">
          <p>Steal Made</p>
        </div>

        <div class="col-2" style="justify-content: right; align-items: right; display: flex;">
          <p><?php echo $ostats_stl_per_game; ?></p>
        </div>
      </div>
    </div>

</body>
</html>
