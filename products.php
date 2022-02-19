<?php

namespace app;

class Product
{
    protected int $id;
    protected string $name;
    protected int $price;

    private array $list = [];

    public function init(): self
    {
        $this->list = $this->getProductsList();

        return $this;
    }

    public function add(int $id, string $name, int $price): self
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;

        return $this;
    }

    public function getProductById(int $id): ?Product
    {
        return $this->list[$id] ?? null;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function getProductsList(): array
    {
        $products = [];
        $productsJson = $this->getProductsData();

        foreach ($productsJson as $product) {
            list($id, $name, $price) = \array_values($product);
            $products[$id] = (new Product)->add($id, $name, $price);
        }

        return $products;
    }

    private function getProductsData(): array
    {
        $data = '[
            {"id": 1,"name": "Coffee","price": 7},
            {"id": 2,"name": "Espresso","price": 10},
            {"id": 3,"name": "Milk","price": 3},
            {"id": 4,"name": "Hot Chocolate","price": 5}
        ]';

        return \json_decode($data, true);
    }

}