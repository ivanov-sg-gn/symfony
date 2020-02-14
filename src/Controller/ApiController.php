<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\OrderLine;
use mysql_xdevapi\Exception;
use App\Service\BasketService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

class ApiController extends AbstractController
{
    public function index(string $particles = null, Request $request, BasketService $basketService)
    {



        try {
            switch ( trim( $particles ) ) {

                case 'goods':

                    $method = $request->getMethod();

                    /*
                    // Http запросы
                    $client = HttpClient::create([
                        'headers' => [
                            'User-Agent' => 'My F.. App',
                            'Content-Type' => 'application/json',
                        ]
                    ]);

                    $res = $client->request('POST', 'https://ivanov-host.ru/api/goods');

                    dump($res->getHeaders(), $res->toArray());*/



                    /*
                    // Email validate
                    $result = null;
                    $value = $request->request->get('value');

                    $validation = Validation::createValidator();
                    $validationRes = $validation->validate(
                        $value,
                        [
                            new NotBlank(),
                            new Email()
                        ]
                    );

                    if(count($validationRes) !== 0){
                        foreach ($validationRes as $item){
                            $result[] = $item->getMessage();
                        }
                    }*/




                    return new JsonResponse([
                        'success' => false,
                        'method' => $method,
                        'errorMessage' => 'Ухади!!!'
                    ]);

                    break;

                case 'basket/add':
                    // CSRF проверка
                    $submittedToken = $request->request->get('token');
                    if (!$this->isCsrfTokenValid('goods-add', $submittedToken)) {
                        return new JsonResponse([
                            'success' => false,
                            'errorMessage' => 'Данные скомпрометированы'
                        ]);
                    }


                    $id = intval($request->request->get('id'));
                    $count = intval($request->request->get('count'));

                    $result = $basketService->add( $id, $count );

                    if($result == true){
                        return new JsonResponse([
                            'success' => true,
                        ]);
                    }
                    else{
                        return new JsonResponse([
                            'success' => false,
                            'errorMessage' => 'Товар не добавился'
                        ]);
                    }

                    break;

                case 'basket/del':
                    // CSRF проверка
                    $submittedToken = $request->request->get('token');
                    if (!$this->isCsrfTokenValid('basket-rud', $submittedToken)) {
                        return new JsonResponse([
                            'success' => false,
                            'errorMessage' => 'Данные скомпрометированы'
                        ]);
                    }

                    if($basketService->del( intval($request->request->get('goods'))) ){
                        return new JsonResponse([
                            'success' => true
                        ]);
                    }

                    break;

                case 'basket/update':
                    // CSRF проверка
                    $submittedToken = $request->request->get('token');
                    if (!$this->isCsrfTokenValid('basket-rud', $submittedToken)) {
                        return new JsonResponse([
                            'success' => false,
                            'errorMessage' => 'Данные скомпрометированы'
                        ]);
                    }

                    $newPosition = [];
                    $position = $request->request->get('position');

                    foreach ($position as $item){
                        $newPosition[$item['goods']] = [
                            'goods' => $item['goods'],
                            'count' => $item['count'],
                        ];
                    }

                    // Выдаст исключение
                    $resUpdate = $basketService->update($newPosition);

                    return new JsonResponse([
                        'success' => true
                    ]);


                    break;

                case 'basket/get':
                    // CSRF проверка
                    $submittedToken = $request->request->get('token');
                    if (!$this->isCsrfTokenValid('basket-rud', $submittedToken)) {
                        return new JsonResponse([
                            'success' => false,
                            'errorMessage' => 'Данные скомпрометированы'
                        ]);
                    }

                    $arOrderLines = [];

                    $orderId = $basketService->getId();
                    if($orderId > 0){
                        $arOrderLines = $basketService->getLinesInfo($orderId);
                    }


                    # Получение фотографий
                    $arImages = [];
                    $arImages = $this->getDoctrine()->getManager()->getRepository( Images::class )->findBy_array( [ 'id' => array_column(array_column($arOrderLines, 'goods_info'), 'img') ] );

                    // Только требуемые данные отдаём
                    $returnArray = [];

                    foreach ($arOrderLines as $key => $item){
                        $returnArray[$key] = [
                            'id' => $item['id'],
                            'goods_id' => $item['goods_id'],
                            'goods_qnt' => $item['goods_qnt'],
                            'goods_price' => $item['goods_price'],
                            'goods_info' => [
                                'id' => $item['goods_info']['id'],
                                'name' => $item['goods_info']['name'],
                                'code' => $item['goods_info']['code'],
                                'link' => $this->generateUrl('goods_routing', ['particles' => $item['goods_info']['code']]),
                                'img' => $item['goods_info']['img'],
                            ],
                        ];

                        if(isset($arImages[$item['goods_info']['img']])){
                            $returnArray[$key]['goods_info']['imgPath'] = $arImages[$item['goods_info']['img']]['path'];
                        }
                    }

                    return new JsonResponse([
                        'success' => true,
                        'data' => $returnArray
                    ]);

                    break;

                # Формирование корзины
                case 'basket/formation':
                    // CSRF проверка
                    $submittedToken = $request->request->get('token');
                    if (!$this->isCsrfTokenValid('basket-formation', $submittedToken)) {
                        return new JsonResponse([
                            'success' => false,
                            'errorMessage' => 'Данные скомпрометированы'
                        ]);
                    }


                    $data = [
                        "first_name"    => $request->request->get('first_name'),
                        "last_name"     => $request->request->get('last_name'),
                        "second_name"   => $request->request->get('second_name'),
                        "email"         => $request->request->get('email'),
                        "phone"         => intval($request->request->get('phone')),
                        "address"       => $request->request->get('address'),
                    ];

                    $result = $basketService->formation( $data );

                    if($result > 0){
                        return new JsonResponse([
                            'success' => true,
                            'order' => $result,
                        ]);
                    }
                    else{
                        return new JsonResponse([
                            'success' => false,
                            'errorMessage' => 'Заказ не сформировался'
                        ]);
                    }

                    break;
            }
        }
        catch(\Exception $e){
            return new JsonResponse([
                'success' => false,
                'errorMessage' => $e->getMessage(),
                'errorLine' => $e->getLine(),
                'errorFile' => $e->getFile()
            ]);
        }


        return new JsonResponse([
            'success' => false,
            'errorMessage' => 'Path not found.'
        ]);
    }
}
