<?php

namespace app;

class Vending
{
    protected Product $product;
    protected Helper $helper;
    protected Ingredients $ingredients;

    function __construct(Helper $helper)
    {
        $this->helper = $helper;
        $this->product = (new Product())->init();
        $this->ingredients = new Ingredients($helper);
    }

    public function prepareDrink(int $id, int $price)
    {
        /** @var Product $productIndexed */
        $productIndexed = $this->product->getProductById($id);

        if (!$productIndexed) {
            $this->helper->unlockScript('ERROR - The product selected is not found!');
        }

        if ($price < $productIndexed->getPrice()) {
            $this->helper->unlockScript('ERROR - Not enough founds!');
        }

        if ($this->ingredients->checkProductIngredients($id)) {
            $this->helper->unlockScript("Success - you can pick up the " . $productIndexed->getName());
            echo "The machine gives change back: " . ($price - $productIndexed->getPrice()) . PHP_EOL;
            echo 'Remaining ingredients are:' . PHP_EOL;
            print_r($this->ingredients->getDefaultData());
        }
    }
}