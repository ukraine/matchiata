
function delayhideDiv(id) {
   document.getElementById(id).classList.add("fade-out");
   setTimeout(function(){
     document.getElementById(id).style.display = 'none';
   },1500)
 }

 function CopyURLAddress(id) {

   let url = document.location.href

   navigator.clipboard.writeText(url).then(function() {
       console.log('Copied!');
       analytics.track('Copied Game URL');
       document.getElementById(id).style.display = "inline";
       delayhideDiv(id);
   }, function() {
       console.log('Copy error')
   });   
   
}

function startTimer() {
   let time = defaultGameDuration; // 2 minutes * 60 seconds
   startTime = new Date();
   
   // Update the timer display immediately before starting the countdown
   document.getElementById('timer').textContent = formatTime(time);

   countdown = setInterval(() => {
       // Decrement first to account for the initial display
       if(--time < 0) {
           endTime = new Date();
           stopTimer(); // time's up
           // Cleanup and statistics display logic
           sourceColumn.innerHTML = '';
           targetColumn.innerHTML = '';
           showStatistics(totalMatches, false); // Show "You lose" message and other stats
       } else {
           // Update the timer display with the decremented time
           document.getElementById('timer').textContent = formatTime(time);
       }
   }, 1000);
}

 function formatTime(time) {
     let minutes = Math.floor(time / 60);
     let seconds = time % 60;
     if(minutes < 10) minutes = '0' + minutes;
     if(seconds < 10) seconds = '0' + seconds;
     return minutes + ':' + seconds;
 }
 
 function stopTimer() {
   clearInterval(countdown);
   endTime = new Date();
 }
 
 function resetGameState() {
   highlightedWords = [];
   successfulMatches = 0;
 
   clearInterval(countdown);
 
   unsuccessfulMatches = 0;
   numberOfMistakesAllowed = defaultNumberOfMistakesAllowed;  
   displayLives(numberOfMistakesAllowed);
   
   document.getElementById('statistics').innerHTML = '';
 }
 
 function restartGame() {
   // Call the playGame function to restart the game without fetching new words
   playGame2();
 }
 
 function getElapsedTime() {
   const timeDiff = endTime - startTime; // in ms
   const seconds = Math.floor(timeDiff / 1000);
   const minutes = Math.floor(seconds / 60);
   const remainingSeconds = seconds % 60;
   return { minutes, seconds: remainingSeconds };
 }

function flagPage() {

   const flagLink = document.getElementById('flagLink');
   const flaggedDiv = document.getElementById('flagged');
   
   const url = window.location.href;
   
   flagLink.addEventListener('click', async function(event) {
       event.preventDefault(); // Prevent the default link behavior
       analytics.track('Game Reported');
   
       console.log('here');
       
       try {
         console.log(url)
           const response = await fetch(reportUrl, {
               method: 'POST', // or 'GET' depending on your server
               body: JSON.stringify({ weburl: url}),
               headers: {
                   'Content-Type': 'application/json'
               }
           });
   
           if (response.ok) {
               const responseData = await response.text();
               flaggedDiv.style.display = 'inline';
               flaggedDiv.textContent = responseData;
           } else {
            flaggedDiv.style.display = 'inline';
               flaggedDiv.textContent = '-Error: ' + response.status;
           }
       } catch (error) {
         flaggedDiv.style.display = 'inline';
           flaggedDiv.textContent = '-Error: ' + error.message;
           
       }
   
       delayhideDiv("flagged");
   });
   }


   
function sleep(ms) {
   return new Promise(resolve => setTimeout(resolve, ms));
 }

 function handleMatch(totalMatches) {
   highlightedWords.forEach(element => {
      element.classList.remove("orange");
      element.classList.add("green");
   });
  
   setTimeout(() => {
      highlightedWords.forEach(element => {
        element.style.display = 'none';
      });
  
      successfulMatches++;
      console.log(successfulMatches + "Successful matches");
      console.log(totalMatches + "Total matches");
  
      // Calculate the progress percentage
      const progressPercentage = (successfulMatches / totalMatches) * 100;
  
      // Update the progress bar
      const progressBar = document.getElementById("progressBar");
      progressBar.style.width = `${progressPercentage}%`; // Assuming the progress bar is a div with a width
  
      if (successfulMatches === totalMatches) {
        stopTimer();
        showStatistics(totalMatches, true); // , unmatchedPairs);
        document.getElementById("timer").innerText = "";
      }
  
      highlightedWords = [];
   }, 150);
  }
  

 
function handleMatch_1(totalMatches) {

   highlightedWords.forEach(element => {
     element.classList.remove("orange");
     element.classList.add("green");
   });
 
   setTimeout(() => {
     highlightedWords.forEach(element => {
       element.style.display = 'none';
     });
 
     successfulMatches++;
     console.log(successfulMatches);
     console.log(totalMatches);
 
     if (successfulMatches === totalMatches) {
       stopTimer();
       showStatistics(totalMatches, true); // , unmatchedPairs);
       document.getElementById("timer").innerText = "";
     }
 
     highlightedWords = [];
   }, 150);
 }


 function playGame() {

   numberOfGamesPlayed++;

   resetGameState();
 
     // Create a div for the timer and append it to the body
     const timerDiv = document.createElement('div');
     timerDiv.id = 'timerOK';
     document.getElementById('progressMessage').prepend(timerDiv); // places it on top of the page
   
   prepareButton.innerHTML = buttonRestartGame;
   prepareButton.onclick = restartGame;
   prepareButton.style.display = 'none';
 
   const t_totalMatches = inputText.trim().split('\n').length;
    
   let startTime;
   let endTime;
 
   const { source, target, totalMatches } = splitInputText(inputText);
 
   const sourceColumn = document.getElementById('sourceColumn');
   const targetColumn = document.getElementById('targetColumn');
 
   sourceColumn.innerHTML = '';
   targetColumn.innerHTML = '';

   startTimer();
 
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
 
   
 
 } 

 function playGame2() {
   // Parse the JSON string to get the JavaScript object
   const data = JSON.parse(inputText);
   const commonWords = data.commonWords;

   // Convert the commonWords object into an array of objects
   let commonWordsArray = Object.keys(commonWords).map((key, index) => ({
       index: index,
       source: key,
       target: commonWords[key]
   }));

   // Shuffle the array using the Fisher-Yates algorithm
   function shuffleArray(array) {
       for (let i = array.length - 1; i > 0; i--) {
           const j = Math.floor(Math.random() * (i + 1));
           [array[i], array[j]] = [array[j], array[i]]; // Swap elements
       }
   }
   shuffleArray(commonWordsArray);

   // Your existing code to reset game state and prepare the UI
   numberOfGamesPlayed++;
   resetGameState();
   const timerDiv = document.createElement('div');
   timerDiv.id = 'timerOK';
   document.getElementById('progressMessage').prepend(timerDiv);

   prepareButton.innerHTML = buttonRestartGame;
   prepareButton.onclick = restartGame;
   prepareButton.style.display = 'none';

   let startTime;
   let endTime;

   // Prepare the source and target columns
   const sourceColumn = document.getElementById('sourceColumn');
   const targetColumn = document.getElementById('targetColumn');
   sourceColumn.innerHTML = '';
   targetColumn.innerHTML = '';

   // Start the timer
   startTimer();

   // Limit the number of items displayed in each column
   commonWordsArray = commonWordsArray.slice(0, limitNumberOfWords);

   shuffleArray(commonWordsArray);

   // Split the shuffled and limited commonWordsArray into sources and targets
   let sources = commonWordsArray.map(item => ({
       index: item.index,
       word: item.source,
       matched: false
   }));
   let targets = commonWordsArray.map(item => ({
       index: item.index,
       word: item.target,
       matched: false
   }));

   totalMatches = limitNumberOfWords;

   let sourcesPlay = sources.slice(0, limitNumberOfWords);
   let targetsPlay = targets.slice(0, limitNumberOfWords);

   shuffleArray(sourcesPlay);
   shuffleArray(targetsPlay);

   console.log(sources.length + " number of words");

   // Iterate over the sources and targets arrays to create the source and target elements
   sourcesPlay.forEach(sourcesPlay => {
       // Create and append the source element
       const sourceElement = document.createElement('div');
       sourceElement.textContent = sourcesPlay.word;
       sourceElement.id = `source-${sourcesPlay.index}`;
       sourceElement.onclick = () => toggleHighlight(sourceElement, totalMatches);
       sourceColumn.appendChild(sourceElement);
   });

   targetsPlay.forEach(targetsPlay => {
       // Create and append the target element
       const targetElement = document.createElement('div');
       targetElement.textContent = targetsPlay.word;
       targetElement.id = `target-${targetsPlay.index}`;
       targetElement.onclick = () => toggleHighlight(targetElement, totalMatches);
       targetColumn.appendChild(targetElement);
   });
}



 
 function splitInputText(inputText) {

   const source = [];
   const target = [];
   const definition = [];
   const lines = inputText.split('\n');
   shuffleArray(lines);
   // inputText.trim().split('\n').length;

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

   // console.log(source);
   // console.log(target);

   // throw new Error("My error");

   shuffleArray(source);
   shuffleArray(target);
   // shuffleArray(target);

   // throw new Error("My error");

   return { source, target, totalMatches }; //definitions
}



async function handleMismatch(totalMatches) {
   
   unsuccessfulMatches++;

   console.log(unsuccessfulMatches + "iteration");

   const totalives = numberOfMistakesAllowed - unsuccessfulMatches;

    // numberOfMistakesAllowed--; // Decrease the number of lives
    displayLives(totalives); 


   if (unsuccessfulMatches >= numberOfMistakesAllowed) {
      // Stop the game. This could be done in several ways, such as showing an alert
      // and then resetting the game state or disabling interaction.
      // alert('Game Over! Too many unsuccessful matches.');
      resetGameState(); // Assuming you have a function to reset the game

      stopTimer(); // time's up
      // const unmatchedPairs = totalMatches - successfulMatches;
      // alert(totalMatches);
      // alert(succesfulMatches);
      sourceColumn.innerHTML = '';
      targetColumn.innerHTML = '';
      showStatistics(totalMatches, false); 

      return; // Exit the function early to prevent further execution
  }
   
   // Store the unsuccessful pair
   if (highlightedWords.length === 2) {
     unsuccessfulPairs.push({
       id: highlightedWords[0].id,
       source: highlightedWords[0].textContent,
       target: highlightedWords[1].textContent
 
     });
   }
 
   highlightedWords.forEach(element => {
     element.classList.remove("orange");
     element.classList.add("red");
   });
 
   await sleep(150);
 
   highlightedWords.forEach(element => {
     element.classList.remove("orange");
     element.classList.remove("red");
   });
 
   highlightedWords = [];
 }
 
 
 
 async function toggleHighlight(element, totalMatches) {
   const index = highlightedWords.indexOf(element);
 
   if (index >= 0) {
    element.classList.remove("orange");
     highlightedWords.splice(index, 1);
   } else {
     if (highlightedWords.length < 2) {
       element.classList.add("orange");
       highlightedWords.push(element);
 
       if (highlightedWords.length === 2) {
         const [sourceIndex, targetIndex] = highlightedWords.map(word => parseInt(word.id.split('-')[1]));
 
         if (sourceIndex === targetIndex) {
           handleMatch(totalMatches);
         } else {
           await handleMismatch(totalMatches);
         }
       }
     }
   }
 }
 

 
function shuffleArray(array) {
   for (let i = array.length - 1; i > 0; i--) {
     const j = Math.floor(Math.random() * (i + 1));
     [array[i], array[j]] = [array[j], array[i]];
   }
 }


 function displayLives(lives) {
   const livesContainer = document.getElementById('livesContainer');
   livesContainer.innerHTML = ''; // Clear the current hearts
   for (let i = 0; i < lives; i++) {
       livesContainer.innerHTML += '😏'; // Append a heart for each life
   }
}

function getLevelName(score) {
   if (score < 0 || score > 100) {
     return "Invalid score. Please enter a valid score between 0 and 100.";
   }
 
   const levels = [
     { maxScore: 24, name: "Good. Keep practicing" },
     { maxScore: 50, name: "Not bad. I know you can do better" },
     { maxScore: 75, name: "Wow! You can do even better. Practice" },
     { maxScore: 85, name: "Perfect! But keep practicing" },
     { maxScore: 99, name: "You're a champion! Keep improving!" },
     { maxScore: 100, name: "You're a hero! Practice to keep this level" }, // Ensure this captures the score of 100
   ];
 
   // Find the first level where the score is less than or equal to the level's maxScore
   const level = levels.find(level => score <= level.maxScore);
   return level ? level.name : "Unknown level"; // Fallback, in case no level matches
 }
