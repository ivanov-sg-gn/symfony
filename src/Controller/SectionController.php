<?php

namespace App\Controller;

use App\Entity\Sections;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SectionController extends AbstractController {
    /**
     * @Route("/section", name="section")
     */
    public function index () {
        return $this->render( 'section/index.html.twig', [
            'controller_name' => 'SectionController',
        ] );
    }



    public function create_menu_left(){
        $em = $this->getDoctrine()->getRepository(Sections::class);
        $arSections = $em->findAll();

        return $this->render( 'section/menu_left.html.twig', [
            'controller_name' => 'SectionController',
            'arSections' => $arSections,
        ] );
    }

    public static function createLinks ( array $arSections = null ): array {
        $arRes = [];

        if($arSections == null) return $arRes;

        # Формируем ссылку для каждого элемента
        foreach ( $arSections as $key => $item ) {
            $arLinks = array_slice( $arRes, count( $arRes ) - 1, count( $arRes ) );

            $arLinks[] = $item->getCode();

            $arRes[ $item->getId() ] = str_replace('//', '/', join( '/', array_values( $arLinks ) ) .'/');
        }

        return $arRes;
    }
}
