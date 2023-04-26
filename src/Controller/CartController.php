<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart_index")
     */
    public function index(CartService $cartService): Response
    {
        $cartItems = $cartService->getCartItems();

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="cart_add")
     */
    public function add(Product $product, CartService $cartService): Response
    {
        $cartService->add($product);

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/cart/checkout", name="cart_checkout")
     */
    public function checkout(CartService $cartService): Response
    {
        $cartItems = $cartService->getCartItems();

        // Code to create and save a new order using the cart items

        $cartService->clear();

        return $this->redirectToRoute('cart_index');
    }
}