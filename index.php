<?php
namespace app;

include 'helper.php';
include 'vending.php';
include 'products.php';
include 'ingredients.php';


$options = \getopt('', ['id:', 'coin::', 'reset']);

$helper = new Helper();

if ($helper->isScriptInUse() === true) {
    echo 'ERROR - The Machine is already in use!' . PHP_EOL;
    exit;
}

if (\key_exists('reset', $options)){
    $helper->resetDefault();
    echo 'We reset Memcached' . PHP_EOL;
    exit;
}

$helper->lockScript();
$helper->validateArguments($options);

$id = (int)$options['id'];
$total = $helper->calculateTotal($options['coin']);

(new Vending($helper))->prepareDrink($id, $total);

$helper->unlockScript();