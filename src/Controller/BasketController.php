<?php

namespace App\Controller;

use App\Service\BasketService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BasketController extends AbstractController
{
    public function index()
    {
        return $this->render('basket/index.html.twig', [
            'controller_name' => 'BasketController',
        ]);
    }

    public function success($number){
        return $this->render('basket/success.html.twig', [
            'controller_name' => 'BasketController',
            'number' => "№".$number,
        ]);
    }
}
