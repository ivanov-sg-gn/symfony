<?php

namespace App\Controller;

use App\Entity\Goods;
use App\Entity\Images;
use App\Form\GoodsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function index(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $goods = new Goods();

        $form = $this->createForm(GoodsType::class, $goods);
        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            $brochureFile = $form->get('imgFile')->getData();

            if (!empty($brochureFile)) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                try {
                    $brochureFile->move(
                        $this->getParameter('upload_goods_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('Не удалось сохранить файл');
                }

                // save images in your table
                $images = new Images();
                $images->setPath($newFilename)
                    ->setType($brochureFile->getClientMimeType());

                $em->persist($images);
                $em->flush();

                // save images id in goods table
                $goods->setImg($images->getId());
            }

            $em->persist($goods);
            $em->flush();

            return $this->redirectToRoute("admin");
        }





        $arGoods = $em->getRepository(Goods::class)->findAll();

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'form' => $form->createView(),
            'arGoods' => $arGoods,
        ]);
    }

    public function removeGoods(Goods $goods, Request $request){
        $em = $this->getDoctrine()->getManager();

        $em->remove($goods);
        $em->flush();

        return $this->redirectToRoute('admin');
    }
}
