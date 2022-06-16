<?
class Transcription {
    private $iblockBrand = 16;
    private $iblockCatalog = 21;
    public $result = [];
    public function __construct()
    {
        \Bitrix\Main\Loader::includeModule('iblock');
    }
    private $ru = array(
        'А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д', 'Е', 'е', 'Ё', 'ё', 'Ж', 'ж', 'З', 'з',
        'И', 'и', 'Й', 'й', 'К', 'к', 'Л', 'л', 'М', 'м', 'Н', 'н', 'О', 'о', 'П', 'п', 'Р', 'р',
        'С', 'с', 'Т', 'т', 'У', 'у', 'Ф', 'ф', 'Х', 'х', 'Ц', 'ц', 'Ч', 'ч', 'Ш', 'ш', 'Щ', 'щ',
        'Ъ', 'ъ', 'Ы', 'ы', 'Ь', 'ь', 'Э', 'э', 'Ю', 'ю', 'Я', 'я', ' '
    );
    private $en = array(
        'A', 'a', 'B', 'b', 'V', 'v', 'G', 'g', 'D', 'd', 'E', 'e', 'E', 'e', 'Zh', 'zh', 'Z', 'z',
        'I', 'i', 'J', 'j', 'K', 'k', 'L', 'l', 'M', 'm', 'N', 'n', 'O', 'o', 'P', 'p', 'R', 'r',
        'S', 's', 'T', 't', 'U', 'u', 'F', 'f', 'H', 'h', 'C', 'c', 'Ch', 'ch', 'Sh', 'sh', 'Sch', 'sch',
        '\'', '\'', 'Y', 'y', '\'', '\'', 'E', 'e', 'Ju', 'ju', 'Ja', 'ja', ' '
    );
    public function dmstring($string)
    {
        $arrRes = [];
        $arrText = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($arrText as $k => $letter) {

//            if($k == 0 && $letter == 'ю') {
//                $letter = 'у';
//            }
//            if($k == 1 && $letter == 'у') {
//                $arrRes[] = 'o';
//                $letter = 'o';
//            }

            $letterEn = $this->translit($letter);

            $arrRes[] = $letterEn;
        }
        $word = implode("", $arrRes);
        $result = $this->searchWord($word);
        return $result;
    }
    private function translit($letter)
    {
        $key = array_search($letter, $this->ru);

        if($key) {
            return $this->en[$key];
        }
        else if(array_search($letter, $this->en)) {
            return $letter;

        }
    }
    public function getName($iblock, $arrID = false) {
        $arr = [];
        $result = [];
        $el = new CIBlockElement;
        if(!$arrID) {
            $ob = $el->GetList([], ['IBLOCK_ID' => $iblock], false, false, ['ID', 'IBLOCK_ID', 'NAME']);
            while ($res = $ob->GetNext(true, false)) {
                preg_match_all('/([a-z\’\.1-9]+)/i', $res['NAME'], $arr);
                foreach ($arr[1] as $word) {

                    if (strlen($word) > 2) {

                        $result[] = mb_strtolower($word);
                    }
                }
            }
        }else {
            $ob = $el->GetList([], ['IBLOCK_ID' => $iblock, 'ID' => $arrID], false, false, ['ID', 'IBLOCK_ID', 'NAME', "DETAIL_PAGE_URL"]);
            while ($res = $ob->GetNext(true, false)) {
                $result[$res['ID']]['NAME'] = $res['NAME'];
                $result[$res['ID']]['URL'] = $res['DETAIL_PAGE_URL'];
                $result[$res['ID']]['MODULE_ID'] = 'iblock';
                $result[$res['ID']]['PARAM1'] = 'aspro_next_catalog';
                $result[$res['ID']]['PARAM2'] = $res['IBLOCK_ID'];
                $result[$res['ID']]['ITEM_ID'] = $res['ID'];
            }
        }
        return $result;
    }
    public function getSectionName($iblock) {
        $arr = [];
        $result = [];
        $el = new CIBlockSection;
        $ob = $el->GetList([], ['IBLOCK_ID' => $iblock, "ACTIVE" => 'Y'], false, ['ID','IBLOCK_ID','NAME']);
        while ($res = $ob->GetNext(true, false)) {
            preg_match_all('/([a-z\’]+)/i', $res['NAME'], $arr);
            foreach ($arr[1] as $word) {

                if(strlen($word) > 2) {
                    $result[] =  mb_strtolower($word);
                }
            }

        }
        return $result;
    }
    public function getAllWord()
    {
        $drand = $this->getName($this->iblockBrand);
//        $catalog = $this->getName($this->iblockCatalog);
        $catalogSec = $this->getSectionName($this->iblockCatalog);

        $result = array_merge($drand, $catalogSec);
        $this->result = array_unique($result);
    }
    public function searchWord($input) {
        $input =  mb_strtolower($input);
        $this->getAllWord();
        $shortest = -1;
        foreach ($this->result as $word) {
            $lev = levenshtein($input, $word);

            if ($lev == 0) {
                $closest = $word;
                $shortest = 0;
                break;
            }
            if ($lev <= $shortest || $shortest < 0) {

                $closest  = $word;
                $shortest = $lev;
            }
        }
        return $closest;
    }
    public function getSearchArticle($number) {
        if($number) {
            $number = trim((string)$number);
            $arrArticle = [];
            $el = new CIBlockElement;
            $ob = $el->GetList([], ['IBLOCK_ID' => $this->iblockCatalog], false, false, ['ID','IBLOCK_ID','NAME', 'PROPERTY_CML2_ARTICLE']);
            while ($res = $ob->GetNext(true, false)) {
                $arrArticle[$res['ID']] = $res['PROPERTY_CML2_ARTICLE_VALUE'];
            }
            $matching_key = preg_grep("/^$number/", $arrArticle);

            return $this->getName($this->iblockCatalog, array_keys($matching_key));
        }
    }
}