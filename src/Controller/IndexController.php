<?php

namespace App\Controller;

use App\Entity\Goods;
use App\Form\GoodsType;

use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    public function index(Request $request)
    {


        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }



}
