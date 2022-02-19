<?php
namespace app;

use Memcached;

class helper {
    const cacheKey = 'inUse';
    const cacheServer = 'memcached';
    const cachePort = 11211;

    const acceptedPayment = [1, 5, 10];

    protected Memcached $cache;

    function __construct()
    {
        $this->cache = new Memcached();
        $this->cache->addServer(self::cacheServer, self::cachePort);
    }

    function isScriptInUse(): bool
    {
        return $this->cache->get(self::cacheKey);
    }

    function lockScript(): void
    {
        $this->cache->set(self::cacheKey, true, 10);
    }

    function unlockScript(string $message = null): void
    {
        if ($message) {
            echo $message . PHP_EOL;
        }

        $this->cache->set(self::cacheKey, false);
    }

    function validateArguments(array $options)
    {
        if (\key_exists('reset', $options)) {
            $this->resetDefault();
            echo 'We reset Memcached' . PHP_EOL;
            exit;
        }

        if (!\key_exists('id', $options)) {
            $this->unlockScript('ERROR - Please select a product id --id [1-4]');
            exit;
        }

        if (!\key_exists('coin', $options)) {
            $this->unlockScript('ERROR - Wrong payment input!');
            echo 'example: --coin=\'{"1":2, "5":1}\' => 2 coins of value 1 and 1 coin value 5 total 7' . PHP_EOL;
            exit;
        }

        $coin = json_decode($options['coin']);
        if (!$coin instanceof \stdClass) {
            $this->unlockScript('ERROR - Wrong payment input!');
            echo 'example: --coin=\'{"1":2, "5":1}\' => 2 coins of value 1 and 1 coin value 5 total 7' . PHP_EOL;
            exit;
        }
    }

    function getCache(): Memcached
    {
        return $this->cache;
    }

    function resetDefault()
    {
        $this->cache->flush();
    }

    function calculateTotal(string $coins): int
    {
        $coins = \json_decode($coins);
        $total = 0;

        foreach ($coins as $coin => $amount) {
            $coin = (int)$coin;
            if (\in_array($coin, self::acceptedPayment)) {
                $total += $coin * $amount;
            }
        }

        return $total;
    }
}