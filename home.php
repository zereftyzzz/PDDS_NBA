

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Progress Bar Example</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
    $temp = 64;
    $temp2 = 100 - $temp;
    ?>
  <div class="progress-container">
    <span id="progress-text1"><?= $temp ?>%</span>
    <progress id="progress" value="<?= $temp ?>" max="100"></progress>
    <span id="progress-text2"><?= $temp2 ?>%</span>
  </div>
  
</body>
</html>
