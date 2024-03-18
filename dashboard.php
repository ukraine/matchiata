<?php 

include "app-settings.php"; 
include "app-translation.php"; // Includes translation

$buttonInProgress = $translation[$language]['buttonInProgress'];
$placeHolder = $translation[$language]['placeHolder'];
$buttonDefaultName = $translation[$language]['buttonDefaultName'];
$buttonRestartGame = $translation[$language]['buttonRestartGame'];

$requestMode = 'teacher';

/*

<!-- 
show the stats as well
shows extracted keywords from a user input text
matched pairs are not replaced with the new words
show statistics finally and hides if the game is started over
variables moved to the top of the script
now the response array contains explanation for each word
shows initial instruictions
- get the most recent article

When the game is initialized for the first time, the text on the button should change it value every two seconds to:

1. Preparing game: sending the text to the server...
2. Preparing game: getting the major keywords...
3. Preparing game: getting the proper translations...
4. Preparing game: building the columns...



-->
*/?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Word Matching Game</title>

  </style>

  <link rel='stylesheet' href='/apps/lingo/play.css?<?=$rand?>'>
</head>
<body>

<br>

<div id="gameContent">

<br><br><br><br>

<section class="container" id="forms">
   <fieldset style='border:0;'>
      <textarea id="text" rows="10" cols="50" placeholder='<?=$placeHolder?>'></textarea>
      <button id="prepareButton" style='height: auto; margin: 5px 0;' onclick="prepareAndLaunchGame()" disabled><?=$buttonDefaultName;?></button>
   </fieldset>
</section>

<div id="progressMessage" onclick='copyDivContent("progressMessage")'></div>

<div id='copystatus'></div>
<div id='status' onclick="hideStatus('status');"></div>

</div>


<!-- <script src='script.js?<?=md5(rand());?>'></script>-->
<script src='//beta.russol.info/chat/js.js?<?=md5(rand());?>'>
</script>

<script>
const requestMode = '<?=$requestMode?>'

let unsuccessfulPairs = [];

const buttonNames = [
  'Sending the text to the brain<?=$threeDots?>',
  'Extracting the words<?=$threeDots?>',
  'Getting the translations<?=$threeDots?>',
  'Building the game<?=$threeDots?>',
  'Launching the game<?=$threeDots?>'
];



    function shuffleArray(array) {
   for (let i = array.length - 1; i > 0; i--) {
     const j = Math.floor(Math.random() * (i + 1));
     [array[i], array[j]] = [array[j], array[i]];
   }
 }

function startTimer() {
  startTime = new Date();
}

function stopTimer() {
  endTime = new Date();
}


function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

 function splitInputText(inputText) {

   const source = [];
   const target = [];
   const definition = [];
   const lines = inputText.split('\n');
   shuffleArray(lines);
   const limitNumberOfWords = 10; // inputText.trim().split('\n').length;

   // console.log(inputText);
   // console.log(source);
   console.log(lines);

   // Process only the first 25 lines
  const linesToProcess = lines.slice(0, limitNumberOfWords);

  totalMatches = limitNumberOfWords;

   // throw new Error("My error");
 
   linesToProcess.forEach(line => { // slice(0, 5)
   // console.log(linesToProcess);
   const [sourceText, targetText] = line.split(/:\s*/);  // , definitionText

   // Check if both sourceText and targetText are defined
   if (sourceText && targetText ) { //&& definitionText

     // console.log(sourceText);
     // console.log(targetText);
     source.push({ index: source.length, value: sourceText.trim() });
     // console.log("Source word: " + sourceText);
     target.push({ index: target.length, value: targetText.trim() });
     // console.log("Target word: " + targetText);
     // definition.push({ index: definition.length, value: definitionText.trim() });
   }
   });

   console.log(source);
   console.log(target);

   // throw new Error("My error");

   shuffleArray(source);
   shuffleArray(target);
   // shuffleArray(target);

   // throw new Error("My error");

   return { source, target, totalMatches }; //definitions
}

function playGame() {

  resetGameState();

  // let highlightedWords = [];
  // let successfulMatches = 0;
  // let unsuccessfulMatches = 0;

  const t_totalMatches = inputText.trim().split('\n').length;
  // alert(totalMatches);
   
  let startTime;
  let endTime;

  const { source, target, totalMatches } = splitInputText(inputText);

  const sourceColumn = document.getElementById('sourceColumn');
  const targetColumn = document.getElementById('targetColumn');

  sourceColumn.innerHTML = '';
  targetColumn.innerHTML = '';

  source.forEach(item => {
    const wordElement = document.createElement('div');
    wordElement.textContent = item.value;
    wordElement.id = `source-${item.index}`;
    wordElement.onclick = () => toggleHighlight(wordElement, totalMatches);
    sourceColumn.appendChild(wordElement);
  });

  target.forEach(item => {
    const wordElement = document.createElement('div');
    wordElement.textContent = item.value;
    wordElement.id = `target-${item.index}`;
    wordElement.onclick = () => toggleHighlight(wordElement, totalMatches);
    targetColumn.appendChild(wordElement);
  });

  startTimer();
  
} 
      
async function prepareAndLaunchGame() {
  const prepareButton = document.getElementById('prepareButton');
  const progressMessage = document.getElementById('progressMessage');
  const buttonNames = [
    'Generating game. Please wait...'
  ];

  let currentButtonIndex = 0;
  const updateButtonName = () => {
    progressMessage.innerHTML = buttonNames[currentButtonIndex];
    currentButtonIndex = (currentButtonIndex + 1) % buttonNames.length;
  };

  prepareButton.disabled = true;
  updateButtonName();

  const updateInterval = setInterval(updateButtonName, 3750);

  const formData = new FormData();

// Split the string into an array of lines
const lines = document.getElementById('text').value.split('\n');

// The first line
const targetLanguage = lines[0];

// The rest of the lines joined back into a string
const text = lines.slice(1).join('\n');

// alert(targetLanguage);
// alert(text);

  formData.append('text', text);
  formData.append('targetLanguage', targetLanguage);
  formData.append('mode', requestMode);

  const response = await fetch('<?=$appUrl;?>', {
  method: 'POST',
  mode: 'cors',
  body: formData
});

clearInterval(updateInterval);
progressMessage.innerHTML = '';
prepareButton.disabled = false;

if (response.ok) {
   const text = await response.text(); // Get the response as text
  console.log(text); // Log it to see what's actually being returned
  const responseData = JSON.parse(text); // Now manually parse it
  
  // Get the value of the 'follow' query parameter from the URL
const urlParams = new URLSearchParams(window.location.search);
const followParam = urlParams.get('follow');
const languageParam = urlParams.get('hl');

// Check if 'follow' is set to 1
if (followParam === '1') {
    // Redirect to the game URL
    window.location.href = responseData.gameUrl + "?hl=" + languageParam + "&follow=" + followParam;
} else {
    // Display the game URL in the element with id 'progressMessage'
    document.getElementById('progressMessage').innerHTML = responseData.gameUrl
}


} else {
  alert('Error: Failed to fetch data from app.php');
}
}


document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('prepareButton').disabled = false;
});


  </script>
</body>
</html>
