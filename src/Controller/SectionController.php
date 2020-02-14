<?php

namespace App\Controller;

use App\Entity\Sections;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SectionController extends AbstractController {

    public function index ( string $code = null, Request $request ) {
        return $this->render( 'section/index.html.twig', [
            'controller_name' => 'SectionController',
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
