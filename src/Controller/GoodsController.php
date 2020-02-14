<?php

namespace App\Controller;

use App\Entity\Goods;

use App\Entity\Images;
use App\Entity\Sections;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Stopwatch\Section;


class GoodsController extends AbstractController {

    # Параметры url
    public $params = [];
    public $goods_code = '';
    private $pagination = [
        'count' => 0,
        'page' => 1,
        'scale' => 0,
    ];
    public $counInPage = 10;


    public function routing ( string $particles = null, Request $request ) {

        if ( !empty( $particles ) ) {

            $this->params = explode( '/', $particles );
            $this->params = array_diff( $this->params, [ '', 0, ' ' ] );

            $last_param = end( $this->params );

            # Проверяем существование товара с таким символьным кодом
            $em = $this->getDoctrine()->getManager();
            $goods = $em->getRepository( Goods::class )->findOneBy( [ 'code' => $last_param ] );

            if ( !empty( $goods ) ) {
                $this->goods_code = $last_param;
                return $this->detail( $goods );
            }

            return $this->listing( $request );
        }
        else {
            return $this->listing( $request );
        }
    }


    public function listing ( Request $request ) {
        $arGoods = [];
        $arFilter = [];

        # Проверяем и вытаскиваем секции
        $arSectionsInfo = $this->get_sections();
        if(!empty($arSectionsInfo)){
            $lastSection = end($arSectionsInfo);

            $arFilter['section'] = intval($lastSection->getId());
        }

        # Левое меню
        $em = $this->getDoctrine()->getManager()->getRepository(Sections::class);
        $arSections = $em->findAll();

        # формирование ссылок для хлебных крошек
        $arBreadCrams = SectionController::createLinks($arSectionsInfo);

        # Получение товаров
        $em = $this->getDoctrine()->getManager()->getRepository( Goods::class );
        $arGoods = $em->findBy(
            $arFilter,
            [ 'sort' => 'asc' ],
            $this->counInPage,
            ($request->get('page', 1) - 1)  * $this->counInPage
        );
        $count = $em->count($arFilter);

        # Получение фотографий
        $arImages = $arImagesFilter = [];
        foreach ($arGoods as $item){
            $arImagesFilter['id'][$item->getImg()] = $item->getImg();
        }

        if(!empty($arImagesFilter)){
            $arImages = $this->getDoctrine()->getManager()->getRepository( Images::class )->findBy_array($arImagesFilter);
        }


        $this->pagination['count'] = ceil($count / $this->counInPage);
        $this->pagination['page'] = $request->get('page', 1);

        return $this->render( 'goods/listing.html.twig', [
            'controller_name'   => 'GoodsController',
            'arGoods'           => $arGoods,
            'arImages'          => $arImages,
            'arSections'        => $arSections,
            'arSectionsInfo'    => $arSectionsInfo,
            'arBreadCrams'      => $arBreadCrams,
            'arPagination'      => $this->pagination,
        ] );
    }



    public function detail ( Goods $goods ) {

        # Левое меню
        $em = $this->getDoctrine()->getManager()->getRepository(Sections::class);
        $arSections = $em->findAll();


        # Получение фотографий
        $arImages = $this->getDoctrine()->getManager()->getRepository( Images::class )->findBy_array(['id' => $goods->getImg()]);


        return $this->render( 'goods/detail.html.twig', [
            'controller_name' => 'GoodsController',
            'goods'           => $goods,
            'arImages'          => $arImages,
            'arSections'        => $arSections,
        ] );
    }


    # Получение секций
    public function get_sections () {
        $arRes = [];
        $em = $this->getDoctrine()->getManager();

        if ( !empty( $this->params ) ) {
            $arRes = $em->getRepository(Sections::class)->findAllSectionsByLinks($this->params);
        }
        
        return $arRes;
    }

}
