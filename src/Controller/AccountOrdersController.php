<?php

namespace App\Controller;

use App\Entity\Goods;
use App\Entity\OrderLine;
use App\Service\OrderService;
use http\Client\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AccountOrdersController extends AbstractController
{
    public function index(OrderService $orderService)
    {

        $arOreder = $orderService->getOrder();


        return $this->render('account_orders/index.html.twig', [
            'controller_name' => 'AccountOrdersController',
            'arOrder' => $arOreder,
        ]);
    }
    public function detail(int $id, OrderService $orderService)
    {
        $arOreder = $orderService->getOrder($id);
        if(!empty($arOreder)){
            $arOrderLines = $this->getDoctrine()->getRepository(OrderLine::class)->findByArray(['order_id' => $id]);
            $arGoods = $this->getDoctrine()->getRepository(Goods::class)->findByArray(['id' => array_column($arOrderLines, 'goods_id')]);
        }

        return $this->render('account_orders/detail.html.twig', [
            'controller_name' => 'AccountOrdersController',
            'id' => $id,
            'arOrder' => $arOreder,
            'arOrderLines' => $arOrderLines,
            'arGoods' => $arGoods,
        ]);
    }
}
