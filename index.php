<?php
define('LHTEXTANALYZER_DEBUG_LEVEL', 30);
define('__LIB_ROOT__', '/Users/user/MyData/phplib/');

spl_autoload_register(function ($class) {
    $suggested = [
        __LIB_ROOT__ . "lhChatterBoxDataProviders/classes/$class.php",
        __LIB_ROOT__ . "lhTestingSuite/classes/$class.php",
        __LIB_ROOT__ . "lhTextConv/$class.php",
        __DIR__ . "/lhTextAnalyzer/classes/$class.php",
        __DIR__ . "/../../PHP/billmgr-production/lib/lh/php/classes/$class.php"
    ];
    
    foreach ($suggested as $file) {
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

echo "Тестирование lhSentence\n";
(new lhSentence())->_test();
echo "Тестирование lhTextAnalyzer\n";
$mind = new lhAIML(__DIR__."/sample_mind.xml");
$ta = new lhTextAnalyzer($mind);
$ta->_test();

//print_r($mind->bestMatches('Со', '', 60));
//print_r($mind->bestMatches('Co вчерашнего', '', 60));
//print_r($mind->bestMatches('Со вчерашнего вечера', '', 60));

die();
// Database connection for testing
$db_connection = 'mysql:host=localhost;dbname=billmgr';
$db_username = 'gaus';
$db_password = 'KcscFWarSPFyWi';
$lhdda = new lhDirectDbAccess($db_connection, $db_username, $db_password);

$sql = 'SELECT * FROM (SELECT MIN(id) as id, ticket, message FROM ticket_message GROUP BY ticket) as first_messages WHERE message NOT LIKE "MANGO%" AND message NOT LIKE "
%" AND message NOT LIKE "Буфер вывода%" AND message NOT LIKE "Array%" AND message NOT LIKE "Исходящий запрос%" AND message NOT LIKE "Код ошибки%" AND message NOT LIKE "Создано автоматически%" AND message NOT LIKE "FREEVPN15%" ORDER BY id DESC';
        
$st = $lhdda->prepare($sql);
$st->execute();

$starts = [];
$words = [];
$lexems_list = [];
while ($row = $st->fetch()) {
    $sentences = lhTextConv::splitSentences($row['message']);
    foreach ($sentences as $sentence) {
//        echo "$sentence\n";
//        continue;
        $lexems = lhTextConv::split($sentence);
        $last_lexem = array_pop($lexems);
        $lexems[] = $last_lexem;
        if (!preg_match("/[\?.!]/u", $last_lexem)) {
            $last_lexem = '.';
            $lexems[] = '.';
        }
        array_unshift($lexems, $last_lexem);
        $starts[$last_lexem] = isset($starts[$last_lexem]) ? $starts[$last_lexem]+1 : 1;
        
        $last_lexem = '';
        //echo ('$lexems='. implode(' ', $lexems). "\n");
        foreach ($lexems as $lexem) {
            if (false === array_search($lexem, $lexems_list)) {
                $lexems_list[] = $lexem;
            }
            if ($last_lexem) {
                $words[$last_lexem][$lexem] = isset($words[$last_lexem][$lexem]) ? $words[$last_lexem][$lexem]+1 : 1;
            }
            $last_lexem = $lexem;
        }
    }
}
//print_r($words);
sort($lexems_list);
foreach ($lexems_list as $lexem) {
    echo "$lexem\n";
}
die();
$start = '.';
while (true) {
    $cur_word = $words[$start];
    $rand = rand(1, array_sum($cur_word));
    $sum = 0;
    foreach ($cur_word as $word=>$val) {
        $sum += $val;
        if ($rand <= $sum) {
            break;
        }
    }
    echo "$word ";
    $start = $word;
    if ($word == '.') {
        echo "\n";
        sleep(2);
    }
    if (!$words[$start]) $start = '.';
}
echo "$start\n";
