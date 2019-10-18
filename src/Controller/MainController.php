<?php

namespace App\Controller;

use App\Entity\Goods;
use App\Form\GoodsType;

use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $goods = new Goods();

        $form = $this->createForm(GoodsType::class, $goods);
        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            $em->persist($goods);
            $em->flush();

            return $this->redirectToRoute("index");
        }

        $arGoods = $em->getRepository(Goods::class)->findAll();

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'form' => $form->createView(),
            'arGoods' => $arGoods,
        ]);
    }

    /**
     * @Route("/remove/{goods}", name="remove_goods")
     */
    public function removeGoods(Goods $goods, Request $request){
        $em = $this->getDoctrine()->getManager();

        $em->remove($goods);
        $em->flush();

        return $this->redirectToRoute('index');
    }

}
