<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Cart
{
    private $requestStack;
    private $entityManager;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }


    public function add($id)
    {
        $session = $this->requestStack->getSession();

        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $session->set('cart', $cart);
    }

    public function get()
    {
        $session = $this->requestStack->getSession();

        return $session->get('cart');
    }

    public function remove()
    {
        $session = $this->requestStack->getSession();

        return $session->remove('cart');
    }

    public function delete($id)
    {
        $session = $this->requestStack->getSession();

        $cart = $session->get('cart', []);

        unset($cart[$id]);

        return $session->set('cart', $cart);
    }

    public function decrease($id)
    {
        $session = $this->requestStack->getSession();

        $cart = $session->get('cart', []);

        // Vérifier si la quantité de notre produit = 1

        if ($cart[$id] > 1) {
            // Retirer une quantité
            $cart[$id]--;
        } else {
            // Supprimer mon produit
            unset($cart[$id]);
        }

        return $session->set('cart', $cart);
    }

    public function getFull() {
        $cartComplete = [];

        if ($this->get()) {
            foreach ($this->get() as $id => $quantity) {
                $product_object = $this->entityManager->getRepository((Product::class))->findOneBy([
                    'id' => $id,
                ]);

                if (!$product_object) {
                    $this->delete($id);
                    continue;
                }

                $cartComplete[] = [
                    'product' => $product_object,
                    'quantity' => $quantity,
                ];
            }
        }

        return $cartComplete;
    }
}


