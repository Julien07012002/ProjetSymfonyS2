<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\CartItem;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    private $entityManager;
    private $cartItems = [];
    private $items = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addToCart(Product $product, int $quantity)
    {
        // Vérifier si le produit existe déjà dans le panier
        $cartItem = $this->entityManager->getRepository(CartItem::class)->findOneBy(['product' => $product]);

        if ($cartItem) {
            // Mettre à jour la quantité si le produit existe déjà dans le panier
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        } else {
            // Ajouter un nouvel élément au panier si le produit n'existe pas encore
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $this->entityManager->persist($cartItem);
        }

        $this->entityManager->flush();
    }

    public function getCartItems()
{
    // Récupérer tous les éléments du panier
    return $this->entityManager->getRepository(CartItem::class)->findAll();
}

    public function add(Product $product, int $quantity = 1): void
    {
    if ($quantity <= 0) {
        throw new \InvalidArgumentException('Quantity must be greater than 0.');
    }

    if (isset($this->items[$product->getId()])) {
        $this->items[$product->getId()]['quantity'] += $quantity;
    } else {
        $this->items[$product->getId()] = [
            'product' => $product,
            'quantity' => $quantity,
        ];
    }
    }

    public function clear()
{
    $this->cartItems = [];
}
}