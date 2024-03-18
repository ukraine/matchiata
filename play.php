<?php 

// Added a progress bar
// Fixed the timer untimed appeareance on start
// Added emojis to the stats
// Added contributors 
// Added animation to the progress bar

include "app-settings.php";
include "app-translation.php";
include "app-matchata.lib.php";

$softName = $translation[$language]['softName'];

$rand = md5(rand());


    $data['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
    $data['country'] = $_SERVER['HTTP_CF_IPCOUNTRY'];
    $data['referer'] = $_SERVER['HTTP_REFERER'];
    $data['language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $data['IP_address'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
    $data['numberOfWordPairs'] = substr_count($responseText, "\n") + 1;
    $data['shortUrlId'] = $_GET['short_url'];

    $data['tableAction'] = "update";

    $data['spreadsheetId'] = $spreadsheetId;
    $data['cellsRange'] = "MatchataStats";
    $data['cellValue'] = date("d-m-Y h:m");

// print_r($data);

    // Measure game creations by a user
    curlPOST($data, SPREADSHEETS_API);


$buttonDefaultName = $translation[$language]['launch_game'];
$buttonRestartGame = $translation[$language]['play_again'];

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?=$softName;?></title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Victor+Mono">
  <link rel='stylesheet' href='/apps/lingo/play.css?<?=$rand?>'>
  <script src='/apps/lingo/play.js?<?=$rand?>'>
   
  </script>
  <link rel="stylesheet" href="<?=$cssFrameWork;?>">
</head>
<body>
   
<div id="instructions" class="container" style='text-align: center;'>
   <div class='row'>
      <h1><?=$translation[$language]['heading']?></h1>
      <p><?=$translation[$language]['train_your_brain']?></p>
   </div>
</div>

<div id="gameContent">

<div class='actionButtons'>

   <a class='shareGame' id='shareGame' onclick='CopyURLAddress("copied");'><?=$translation[$language]['share_game']?></a></span> 
   <span id='copied' class='shareGameCopied'><?=$translation[$language]['address_copied']?></span>

</div>


<section class="container playButton" id="forms">
   <fieldset>
      <!-- <textarea id="text" rows="10" cols="50" placeholder='Copy 4-8 paragraph from an article here'></textarea><br> -->
      <button id="prepareButton" onclick="prepareAndLaunchGame()" disabled><?=$buttonDefaultName;?></button>
   </fieldset>
</section>

<div id="livesContainer" style='display: none;'></div>

<div id='progressBarContainer' style='display: none;'>
   <div id='progressBar'></div>
</div>

<div id='startInstructions' class='startInstructions' style='display: none;'><?=$translation[$language]['to_win']?></div>

<div id="timer"></div>

<div id='progressMessage'></div>

  <div class="container">
    <div id="sourceColumn" class="column"></div>
    <div id="targetColumn" class="column"></div>
  </div>
  <div id='statistics'></div>

</div>


<p style='text-align: center; margin-top: 15px'>
<!--<a onclick="analytics.track('Clicked to created own game')" class='createMyGame' href='http://yatsiv.com/e/matchata'><?=$translation[$language]['create_your_own_game']?></a></span>
&nbsp; -->

<a id="flagLink" class='share reportGame' title='Report this game'><?=$translation[$language]['report_issue']?></a>
<span id="flagged" class='red status'></span>
<a style='font-size: 70%; text-decoration: none;' href='/apps/lingo/p/contributors'><?=$translation[$language]['contributors']?></a>

<script>

let unsuccessfulPairs = [];

const reportUrl = '/apps/lingo/r.php';

let totalPointsEarned = 0;

let numberOfGamesPlayed = 0;

let AccumulatedAccuracy = 0;

const defaultNumberOfMistakesAllowed = <?=$settings['numberOfMistakesAllowed']?>;

let numberOfMistakesAllowed = defaultNumberOfMistakesAllowed;

const limitNumberOfWords = <?=$settings['defaultLimitNumberOfWords']?>; 

const buttonRestartGame = '<?=$buttonRestartGame?>';

const defaultGameDuration = '<?=$settings['defaultGameDuration']?>';

const defaultXPValue = '<?=$settings['defaultXPValue']?>'

const prepareButton = document.getElementById('prepareButton'); 
       
async function prepareAndLaunchGame() {
   
   document.getElementById('startInstructions').style.display='block';
   document.getElementById('livesContainer').style.display='block';
   document.getElementById('progressBarContainer').style.display='block';

   document.getElementById('shareGame').style.display = 'none';
   inputText = "<?=str_replace(array("\r","\n"),array("\\r","\\n"), file_get_contents(CACHE_FILE_PREFIX . $_GET['short_url']))?>";
   playGame();

}


// Remove plural
// remove duplicates both in source and target

let countdown; // this will hold our interval

function showStatistics(totalMatches, isWin) { //, unmatchedPairs) { // add isWin and unmatchedPairs parameters
  let accuracy = Math.round(((successfulMatches / (successfulMatches + unsuccessfulMatches)) * 100).toFixed(2));
  const elapsedTime = getElapsedTime();
  const statisticsDiv = document.getElementById('statistics');
  const secondsToMatchesIndex = 2;
  const pointsEarned = (totalMatches - unsuccessfulMatches) * defaultXPValue;

  accuracy = getLevelName(accuracy)

  totalPointsEarned += pointsEarned;
  AccumulatedAccuracy += accuracy;
  averageAccuracy = AccumulatedAccuracy/numberOfGamesPlayed;

  document.getElementById('startInstructions').style.display = "none";
  document.getElementById('livesContainer').style.display = "none";
  document.getElementById('progressBarContainer').style.display = "none";
  document.getElementById('shareGame').style.display = 'inline';
  document.getElementById('timer').textContent = "";
  document.getElementById('timer').textContent = "";

  // ${!isWin ? `<p>Unmatched Pairs: ${unmatchedPairs}</p>` : ''} <!-- display Unmatched pairs based on isWin -->
  statisticsDiv.innerHTML = `
  
    
   <h3>${isWin ? '<?=$translation[$language]['youDidIt']?>' : '<?=$translation[$language]['youLose']?>'}</h3>
    
    <p> ⚡️ <?=$translation[$language]['pointsEarned']?> +<b>${pointsEarned}</b>
    <br>🎯 <?=$translation[$language]['accuracy']?> <b>${accuracy}</b>
    <br>🕐 <?=$translation[$language]['time']?> ${elapsedTime.minutes} min ${elapsedTime.seconds} sec
    </p>

    <p>🤩 <?=$translation[$language]['totalMatches']?> <b>${totalMatches}</b>
    <br>🙉 <?=$translation[$language]['mistakesMade']?> <b>${unsuccessfulMatches}</b>
    </p>
    

   <!-- <p><b><?=$translation[$language]['yourProfile']?></b>

    <p><?=$translation[$language]['totalPoints']?> <b>${totalPointsEarned}</b>
    <br><?=$translation[$language]['gamesPlayed']?> <b>${numberOfGamesPlayed}</b>
    <br><?=$translation[$language]['averageAccuracy']?> <b>${averageAccuracy}</b>
    <p><?=$translation[$language]['heartsLeft']?>: ${numberOfMistakesAllowed}</p> -->
    
  `;

  // Change the button's text and onclick attribute when the game ends
  const prepareButton = document.getElementById('prepareButton');
  prepareButton.innerHTML = '<?=$buttonRestartGame?>';
  prepareButton.style.display = 'block';
  prepareButton.onclick = restartGameAndDisplayBlock;
  analytics.track('Game Restarted');
}



function restartGameAndDisplayBlock() {
  restartGame(); // Assuming restartGame is a function defined elsewhere
  document.getElementById('startInstructions').style.display = "block";
  document.getElementById('livesContainer').style.display = "block";
  document.getElementById('progressBarContainer').style.display = "block";
  document.getElementById('shareGame').style.display = 'none';
  document.getElementById('progressBar').style.width = "0";
}


document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('prepareButton').disabled = false;
});

// Copy Share Report Game

displayLives(numberOfMistakesAllowed);

// Call the function to set up the event listener
flagPage();

  </script>


<? 

include "googleTag.php"; 
include "1footer.php";

?>

</body>
</html>