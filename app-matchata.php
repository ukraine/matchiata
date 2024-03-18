<?php

// echo "{\"gameUrl\":\"$_POST[targetLanguage]\"}";
// exit();

// echo "{\"gameUrl\":\"https:\/\/yatsiv.com\/apps\/lingo\/play\/yEjYa5xZ\"}";
// exit();
// error_reporting(E_ALL);
// ini_set("display_errors", On); 

include "app-settings.php";
include "safeplace/api.php";

require 'vendor/autoload.php';

use GuzzleHttp\Client;

include "app-matchata.lib.php";

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");


// Read POST data from the request
$inputJSON = file_get_contents('php://input');

// print_r($inputJSON);
$input = json_decode($inputJSON, true);

// $text = substr(trim(base64_decode($_POST['text'])), 0, 1000);
$text = substr(trim($_POST['text']), 0, 1500);

define('TARGET_LANGUAGE',trim($_POST['targetLanguage']));
define('WORDS_TO_GENERATE', $settings['numberOfWordsToExtractOrGenerate']);
define('SENTENCES_TO_GENERATE', $settings['numberOfSentencesGenerated']);

$mode = ($_POST['mode'] === 'teacher') ? 'teacher' : $mode;

$format = ($_POST['foramt'] === 'json') ? 'json' : $format;


// print_r($_POST);

// exit();

$format = "default";

$textResponses = [

"default" => "like  this 'source word: translation', each on its own single line. You don't use any numbers",

"json" => "in JSON format that has the following root variables: commomWords and dialogues. In the first variable we have the source words standing for the key, and their correspondign translations for the values. The dialogue contains two keys - source and translation. 

Additionally make-up a meaningful dialogue between two people, based on the words you generated in both languages. The consistent dialogue should be $settings[numberOfSentencesGenerated] sentences in lentgh. Please make sure to include them into our JSON response. Your response is only json and nothing else",
   
];


$initialInstructions['noprompt'] = "do nothing at all.";


$srchRplc = ["{TARGET_LANGUAGE}"=>TARGET_LANGUAGE, "{TOPIC}" => $text, "{SENTENCES_TO_GENERATE}" => SENTENCES_TO_GENERATE, "{WORDS_TO_GENERATE}" => WORDS_TO_GENERATE,];

//Since the dialogue part is not needed for this task, you can omit it or set it to \"None\"

$initialInstructions['teacherExperimentStableJSON'] = "

Task: Create a list of 50 meaningful, yet less common words or up to two words phrases, including those with their possible prepositions and postpositions, that people may use in conversations related to the topics, related to \"{TOPIC}\". 

Then, identify and translate {WORDS_TO_GENERATE} key phrases, considering nouns, verbs, phrases, or other types of words along with their prepositions or postpositions, from the source language into {TARGET_LANGUAGE}. The specified topic and the use of \"{TOPIC}\" imply the source language

Steps:

1. Word Identification and Translation: Extract a balanced mix of {WORDS_TO_GENERATE} nouns, verbs, phrases, or other types of words, ensuring to include any associated prepositions (e.g., \"to do\" instead of \"do\") or postpositions.

Translate these terms from the source language into {TARGET_LANGUAGE}, paying close attention to how prepositions or postpositions affect the meaning. Phrases to not exceed 2 words.

Output Format: Present your translations in a JSON format within the commonWords section, mapping the original phrases (including any prepositions or postpositions) to their {TARGET_LANGUAGE} counterparts.

Expected JSON Structure:

{

  \"dialogue\": {

    \"None\": \"None\",

    ...

  },

  \"commonWords\": {

    \"SourceLanguageVerb1/Noun1/OtherType1\": \"ItsTargetTranslation1\",

    ...

  }

}

Your response is JSON only.

";

$initialInstructions['teacherWordsSentences'] = "

Task: Craft a dialogue relevant to '{TEXT}' using meaningful, yet less common words. Following the dialogue, identify and translate the key words, including nouns and verbs involved into {TARGET_LANGUAGE}. Based on the specified topic you understand what source language was used to write it.

Steps:

1. Dialogue Creation: Write a short {SENTENCES_TO_GENERATE}-sentnce conversation between two people, incorporating less common terms. The dialogue should be relevant to the topic but use unique vocabulary. Each its sentence shouldn't be too long.

2. Word Identification and Translation: From the dialogue, extract {WORDS_TO_GENERATE} nouns, verbs or other type of word used. Translate these words from the source language into {TARGET_LANGUAGE}.

Output Format: Present your dialogue and translations in a JSON format with two sections: `dialogue` for the conversation and `commonWords` for the translations. The `dialogue` should list the original sentences and their {TARGET_LANGUAGE} translations only. The `commonWords` section should map the original words to their {TARGET_LANGUAGE} counterparts.

Expected JSON Structure:

```json

{

  \"dialogue\": {

    \"SourceLanguageSentence\": \"ItsTargetTranslation\",

    ...

  },

  \"commonWords\": {

    \"SourceLanguageVerb1/Noun1/OtherType1\": \"ItsTargetTranslation1\",

    ...

  }

}

```

";

$initialInstructions['teacher'] = "

Instructions:

You're a translator with encyclopedia like memory. You were given a specific topic, called \"$text\". 

Based on the specified topic you understand what source language was used to write it.

Now your task is to come up with a list of the top " . WORDS_TO_GENERATE . " most meaningful, but not so common both verbs and nouns.

Then you translate the list into " . TARGET_LANGUAGE . ".

The output format of your response should be only and always $textResponses[$format].

";

$initialInstructions['teacherFailed'] = "

**Task:** Translate " . WORDS_TO_GENERATE . " unique and meaningful \"$text\"-related verbs and nouns to ". TARGET_LANGUAGE . ".  and craft a exactly 4-sentence dialogue using these words. Based on the specified topic you understand what source language was used to write it.

**Output Format:** Provide your translations and dialogue in a JSON format with two sections: `commonWords` (for translations) and `dialogue` (for the conversation). In `commonWords`, map Ukrainian words to their English counterparts. In `dialogue`, include the original source sentences and their " . TARGET_LANGUAGE . " translations.

**Expected JSON Structure:**

```json
{
  \"commonWords\": {
    \"SourceVerb1/Noun1\": \"TargetTranslation1\",
    ...
  },
  \"dialogue\": {
    \"SourceSentence\": \"ItsTargetTranslation\",
    ...
  }
}
```

**Requirements:** Focus on less common, meaningful words and ensure the dialogue is relevant to everyday household themes



";

$initialInstructions['default'] = "

Instructions: You're an analyst and a translator. You read the text in the paragraph that goes after this one and extract the top " . WORDS_TO_GENERATE . " most meaningful, non frequently used verbs and nouns, hereafter called 'source words'. Then you eliminate any proper names you extracted like New York or AirBnB, single-lettered words, including \"O\", \"B\" and empty words with no letters. Then you translate them only into " . TARGET_LANGUAGE . ". If the source text is matched with the " . TARGET_LANGUAGE. ", translate into $defaultLanguage. The output format of your response should be only and always like this \"source word: translation\", each on its own single line. For demonstration purposes, use prepositions, articles, and conjunctions in addition to verbs and nouns. Here goes the text:\n\n\n ${text}\n\n\n";

// $prompt =  $initialInstructions[$mode]; // $prompt_temp;

$prompt .= str_replace(array_keys($srchRplc), array_values($srchRplc), $initialInstructions[$mode]);

// echo $prompt;
// exit();

$prompt = mb_convert_encoding($prompt, 'UTF-8', 'auto');

// echo $prompt;

$promptForLog = "$input[targetLanguage] $input[text]";

$client = new Client([
    'headers' => [
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json'
    ],
    'verify' => false,
]);

try {
    // Make the API request
    $response = $client->post('https://api.openai.com/v1/chat/completions', [
        'json' => [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $settings['maxTokens'],
            'n' => 1,
            'stop' => null,
            'temperature' => 0.5,
        ]
    ]);

    // echo $responseData;

    // exit();

    // echo $response->getBody();

    // Parse the API response
    $responseData = json_decode((string) $response->getBody(), true);
    $responseText = $responseData['choices'][0]['message']['content'];



    logme($promptForLog, $responseText);
    
    // Создаем краткий URL по умолчанию
    $shortUrlId = generateShortUrl();

    cacheURLlocally($responseText, $shortUrlId);

    $gameUrl = PRIMARY_DOMAIN . "play/" . $shortUrlId;

    $data['shortUrlId'] = $shortUrlId;
    $data['numberOfWordPairs'] = substr_count($responseText, "\n") + 1;
    $data['timestamp'] = date('d-m-Y h:m');
    $data['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
    $data['IP_address'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
    $data['country'] = $_SERVER['HTTP_CF_IPCOUNTRY'];
    $data['language']   = "\"$_SERVER[HTTP_ACCEPT_LANGUAGE]\"";
    $data['referer'] = $_SERVER['HTTP_REFERER'];

    foreach($data as $key=>$val) { $preCellValue .= "$val,"; } 

    $data['spreadsheetId'] = $spreadsheetId;
    $data['cellsRange'] = "MatchataStats";
    $data['cellValue'] = $preCellValue;

    // Measure game creations by a user
    curlPOST($data, SPREADSHEETS_API);

    // Return the JSON response
    // echo json_encode(['responseText' => $responseText]);
    echo json_encode(['gameUrl' => $gameUrl]);

} catch (Exception $e) {
    // Handle errors
    http_response_code(500);
    logme("error");
    echo json_encode(['error' => $e->getMessage()]);
}