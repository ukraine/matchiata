<?php

define("CACHE_FILE_PREFIX", 'cache/');
define('DEFAULT_SHORT_URL_LENGTH', '8');
define('PRIMARY_DOMAIN','https://yatsiv.com/apps/lingo/');
define("SPREADSHEETS_API", "https://apps.yatsiv.com/sheets/");

// Game Specific Settings

$settings = [

// GamePlay

"numberOfMistakesAllowed" => 5,
"numberOfFailedGamesDaily" => 5,

// XP points formula

"defaultXPValue" => 30,
"defaultGameDuration" => 120,
"defaultLimitNumberOfWords" => 7,

// MaxTokems

"maxTokens" => 500,

// GameGeneration

"numberOfSentencesGenerated" => 5,
"numberOfWordsToExtractOrGenerate" => 21,

];

$cssFrameWork = "https://cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.css";
// "https://cdn.jsdelivr.net/npm/picnic";
// https://cdnjs.cloudflare.com/ajax/libs/milligram/1.4.1/milligram.min.css

$threeDots = "<span class=\"dots\">...</span>";

// Game Generation

$defaultLanguage = 'Ukrainian';

$appUrl = "https://yatsiv.com/apps/lingo/app-matchata.php";

// Metrics

$googleTagId = 'G-SWF30HKFVD';
$segmentTagId = 'NX1E41I16kPSXEIgOtiHMtDSCWzjdaOF';
$spreadsheetId = "111eOZH_G5nZKqLnhs2fP6K6St2l-US4JLAtCxhgpAeQ";

// Detect lang

$defaultLanguage = 'en';

$language = empty($_GET['hl']) ? $defaultLanguage : $_GET['hl'];