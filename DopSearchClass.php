<?
class DopSearchClass {

    public function checkValueSearch($textSearch) {
        $textQuery = urlencode($textSearch);

        $url = "https://speller.yandex.net/services/spellservice.json/checkText?text=".$textQuery;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $resultCheck = curl_exec($curl);
        $resultCheck = json_decode($resultCheck, true);

        if($resultCheck) {
            /* массив в который будут записываться обработанные значения */
            $listCurrentText = array();

            /* если у нас только одно ошибочное слово */
            if(count($resultCheck) == 1) {
                /* записываем вариант ошибки */
                $falseText = $resultCheck[0]["word"];

                /* преобразуем ассоциативный массив в обычный */
                $arrValidWord = array_values($resultCheck[0]["s"]);
                /* удаляем однокоренные слова */
                $validWord = $this->clearBySixFirstLetter($arrValidWord);

                foreach($validWord as $word) {
                    $newTextSearch = str_replace($falseText, $word, $textSearch);
                    array_push($listCurrentText, $newTextSearch);
                }
            }
            /* если несколько ошибочных слов */
            else if(count($resultCheck) > 1) {
                foreach($resultCheck as $arrWord) {
                    $falseText = $arrWord["word"];
                    $textSearch = str_replace($falseText, $arrWord["s"][0], $textSearch);
                }
                array_push($listCurrentText, $textSearch);
            }

            return $listCurrentText;

        }
    }

    /* УДАЛЯЕМ ОДНОКОРЕННЫЕ СЛОВА */
    public function clearBySixFirstLetter($array) {
        /* записываем корни слов */
        $has = [];

        return array_filter(
            $array,
            function ($word) use (&$has) {
                $sixLetters = mb_substr($word, 0, 6);

                if (!in_array($sixLetters, $has)) {
                    array_push($has, $sixLetters);
                    return true;
                }

                return false;
            }
        );
    }

}



