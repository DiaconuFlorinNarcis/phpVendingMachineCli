<?php

namespace app;

class Ingredients
{
    protected int $id;
    protected string $name;
    protected int $quantity;

    private Helper $helper;

    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    public function add(int $id, string $name, int $quantity): self
    {
        $this->id = $id;
        $this->name = $name;
        $this->quantity = $quantity;

        return $this;
    }

    public function checkProductIngredients(int $productId): bool
    {
        $ingredients = $this->getById($productId, $this->getIngredientsData());

        foreach ($ingredients->ingredients as $ingredient) {
            $default = $this->getDefaultById($ingredient->ingredientId);

            $default->quantity += - $ingredient->quantity;

            if ($default->quantity < 0) {
                $this->helper->unlockScript("ERROR - We run out of $default->name");
                exit;
            }

            $default->removeHelper();

            $this->helper->getCache()->set(
                "product{$ingredient->ingredientId}",
                $default
            );

        }

        return true;
    }

    public function removeHelper(): self
    {
        //for memcache and display
        unset ($this->helper);

        return $this;
    }

    public function getDefaultData(): array
    {
        $data = '[
            {"id": 1,"name": "Coffee","quantity": 2000},
            {"id": 2,"name": "Aqua","quantity": 10000},
            {"id": 3,"name": "Milk","quantity": 2000},
            {"id": 4,"name": "Chocolate","quantity": 500}
        ]';

        $data = \json_decode($data);

        foreach ($data as &$item) {
            $item = $this->getCachedDefaultById($item->id) ?? $item;
            $item = (new Ingredients($this->helper))->add($item->id, $item->name, $item->quantity);
            $item->removeHelper();
        }

        return $data;
    }

    private function getIngredientsData(): array
    {
        $data = '[
            {"product_id": 1, "ingredients": [
                {"ingredientId": 1,"quantity": 25},
                {"ingredientId": 2,"quantity": 200}
            ]},
            {"product_id": 2, "ingredients": [
                {"ingredientId": 1,"quantity": 50},
                {"ingredientId": 2,"quantity": 100}
            ]},
            {"product_id": 3, "ingredients": [
                {"ingredientId": 3,"quantity": 200}
            ]},
            {"product_id": 4, "ingredients": [
                {"ingredientId": 2,"quantity": 200},
                {"ingredientId": 4,"quantity": 100}
            ]}
        ]';

        return \json_decode($data);
    }

    private function getById(int $id, array $list): ?\stdClass
    {
        $found = null;
        foreach ($list as $item) {
            if ($item->product_id === $id) {
                $found = $item;
                break;
            }
        }

        return $found;
    }

    private function getCachedDefaultById(int $id): self|\stdClass|null
    {
        $cache = $this->helper->getCache();
        $foundInCache = $cache->get("product{$id}");

        if ($cache->getResultCode() === \Memcached::RES_SUCCESS) {
            return $foundInCache;
        }

        return null;
    }

    private function getDefaultById(int $id): ?self
    {
        foreach ($this->getDefaultData() as $item) {
            if ($item->id === $id) {
                return $item;
            }
        }

        return null;
    }
}