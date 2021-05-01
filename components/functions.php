<?php

function dump($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    exit();
}
function createAlias($s)
{
    $s = (string) $s; // преобразуем в строковое значение
    $s = strip_tags($s); // убираем HTML-теги
    $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
    $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
    $s = trim($s); // убираем пробелы в начале и конце строки
    $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
    $s = strtr($s, array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'j', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'ъ' => '', 'ь' => ''));
    $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
    $s = str_replace(" ", "_", $s); // заменяем пробелы знаком минус
    return $s; // возвращаем результат
}

function formatPrice($price)
{
    return number_format($price, 0, ',', ' ') . ' UZS';
}

function sekToDay($sek)
{
    $min = floor($sek / 60);
    $hour = floor($min / 60);
    $day = floor($hour / 24);

    if ($day > 1) {
        return $day . ' д';
    } else if ($hour > 1) {
        return $hour . ' ч';
    } else if ($min > 20) {
        return $min . ' м';
    } else {
        return false;
    }
}

function distance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadiusKm = 6371;

    $dLat = (($lat2 - $lat1) * M_PI) / 180;
    $dLon = (($lon2 - $lon1) * M_PI) / 180;

    $lat1 = ($lat1 * M_PI) / 180;
    $lat2 = ($lat2 * M_PI) / 180;

    $a =
        sin($dLat / 2) * sin($dLat / 2) +
        sin($dLon / 2) *
        sin($dLon / 2) *
        cos($lat1) *
        cos($lat2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return round($earthRadiusKm * $c * 10) / 10;
}

function translate($s)
{
    $s = (string) $s; // преобразуем в строковое значение
    $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
    $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
    $s = trim($s); // убираем пробелы в начале и конце строки
    $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
    if (preg_match("/[а-я]+/i", $s)) {
        $s = strtr($s, array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'j', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'ъ' => '', 'ь' => ''));
    } else {
        $s = strtr($s, array('a' => 'а', 'b' => 'б', 'v' => 'в', 'g' => 'г', 'd' => 'д', 'e' => 'е', 'j' => 'ж', 'z' => 'з', 'i' => 'и', 'y' => 'й', 'k' => 'к', 'l' => 'л', 'm' => 'м', 'n' => 'н', 'o' => 'о', 'p' => 'п', 'r' => 'р', 's' => 'с', 't' => 'т', 'u' => 'у', 'f' => 'ф', 'h' => 'х', 'x' => 'х',  'ch' => 'ч', 'sh' => 'ш'));
    }
    return $s; // возвращаем результат
}

function commentDate($timestamp)
{
    $monthsList = array(
        "01" => "января",
        "02" => "февраля",
        "03" => "марта",
        "04" => "апреля",
        "05" => "мая",
        "06" => "июня",
        "07" => "июля",
        "08" => "августа",
        "09" => "сентября",
        "10" => "октября",
        "11" => "ноября",
        "12" => "декабря"
    );

    $year = date('Y', $timestamp);
    $day = date('d', $timestamp);
    $month = date('m', $timestamp);
    $time = date('h:i', $timestamp);

    return $day . '  ' . $monthsList[$month] . '  ' . $time;
}
