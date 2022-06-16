<?
function getCorrectedString(string $inputText): string {
    $resultText = $inputText;
    $curlContent = getUrlContent(
        'http://speller.yandex.net/services/spellservice.json/checkTexts',
        [
            'text' => $inputText
        ],
        [
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 10
        ]
    );
    if (
        isset($curlContent['result'])
        && !empty($curlContent['result'])
    ) {
        $spellResult = current(
            json_decode($curlContent['result'])
        );
        $correctionMap = [];
        foreach ($spellResult as $correction) {
            $correctionMap[$correction->word] = current($correction->s);
        }
        $resultText = str_replace(
            array_keys($correctionMap),
            array_values($correctionMap),
            $inputText
        );
    }
    return $resultText;
}


function getUrlContent(
    string $url, array $postData = [], array $optionsList = []
) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, $url);
    if (!empty($postData)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));
    }
    if (!empty($optionsList)) {
        foreach ($optionsList as $optionKey => $optionValue) {
            curl_setopt($curl, $optionKey, $optionValue);
        }
    }
    $result = [
        'result' => curl_exec($curl),
        'errno' => curl_errno($curl),
        'error' => curl_error($curl),
        'http_code' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
    ];
    curl_close($curl);
    return $result;
}

$answerText = 'отзвчивый ядекс';
$responseText = getCorrectedString($answerText);
echo $responseText;