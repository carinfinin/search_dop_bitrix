# пример использования

require($_SERVER["DOCUMENT_ROOT"]."/local/include/DopSearchClass.php");
require($_SERVER["DOCUMENT_ROOT"]."/local/include/Transcription.php");

$search = new DopSearchClass();
$transcription = new Transcription();

 $arrSearch = $search->checkValueSearch($searchQury);
 $quryTranscription = $transcription->dmstring($arResult["query"]);
$er = $transcription->getSearchArticle($arResult["query"]);
