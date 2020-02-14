<?php
namespace App\Service;

use App\Entity\Goods;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;


class BasketService{
    /*
    order:
        id	int(11)
        user_id	int(11)
        state_id	int(11)
        zombie	tinyint(1)
    order_line:
        id	int(11)
        order_id	int(11)
        goods_id	int(11)
        goods_qnt	double
        goods_price	double
    order_state:
        id	int(11)
        name	varchar(255)
        zombie	tinyint(1)
    */


    private $security,
            $entityManager,
            $requestStack;

    public $user = null;
    public $user_id = null;
    public $user_session_key = null;

    function __construct (RequestStack $requestStack, Security $security, EntityManagerInterface $entityManager, SessionInterface $session) {

        $this->security = $security;
        $this->entityManager = $entityManager;

        $this->requestStack = $requestStack->getCurrentRequest();


        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // Получаем пользователя
            $this->user = $this->security->getUser();
            $this->user_id = $this->user->getId();
        }
        else{
            // Получаем анонимный ключ корзины
            $this->user_session_key = $this->requestStack->cookies->get('user_session_key');

            if(empty($this->user_session_key)){
                $this->user_session_key = $this->generate_session_key();

                $response = new Response();
                $response->headers->setCookie(new Cookie('user_session_key', $this->user_session_key));
                $response->send();
            }
        }

    }

    // Add to basket
    public function add(int $good, float $count, float $price=null, int $user=null){

        if($good <= 0 || $count <= 0 || ($user != null && $user <= 0)){
            throw new \Exception('Полученные данные не корректны');
        }

        $good = intval($good);
        $count = floatval($count);


        // Получаем актуальную цену продукта
        if($price == null){
            $arGoods = $this->entityManager->getRepository( Goods::class )->findOneBy(['id' => $good]);

            if(!empty($arGoods)){
                $price = $arGoods->getPrice();
            }
            else{
                $price = 0;
            }
        }


        // Получаем id корзины
        $orderId = $this->getId($user);

        if($orderId <= 0){
            $orderId = $this->create();
        }


        $arFilter = [
            'order_id' => $orderId,
            'goods_id' => $good
        ];


        $orderLine = $this->entityManager->getRepository( OrderLine::class )->findOneBy( $arFilter );

        if(!empty($orderLine)){
            $orderLine->setGoodsQnt(floatval($orderLine->getGoodsQnt()) + $count);
            $orderLine->setGoodsPrice($price);

            $this->entityManager->flush();

            return $this->entityManager->contains($orderLine);
        }
        else{
            $newLine = new OrderLine();

            $newLine->setOrderId($orderId);
            $newLine->setGoodsId($good);
            $newLine->setGoodsQnt($count);
            $newLine->setGoodsPrice($price);

            $this->entityManager->persist($newLine);
            $this->entityManager->flush();

            return $this->entityManager->contains($newLine);
        }

    }

    /**
     * @param array    $position
     * @param int|null $user
     * @param int|null $order
     * @return array
     * @throws \Exception
     */
    public function update(array $position = [], int $user = null, int $order = null){
        $orderID = 0;

        if ($user > 0){
            $orderID = $this->getId($user);
        }

        if ($order > 0){
            $orderID = $order;
        }

        if($orderID <= 0){
            $orderID = $this->getId();
        }

        if($orderID <= 0){
            throw new \Exception('Не найден идентификатор заказа');
        }


        try{

            $arIDs = array_diff(array_map(function($val){return intval($val);}, array_column($position, 'goods')), ['', 0, null]);

            $orderLines = $this->entityManager->getRepository( OrderLine::class )->findBy(['order_id' => $orderID, 'goods_id' => $arIDs]);

            foreach ($orderLines as $item){
                if( isset($position[ $item->getGoodsId() ]) ){

                    if( isset($position[ $item->getGoodsId() ]['count']) ){
                        if( floatval($position[ $item->getGoodsId() ]['count']) > 0 ){
                            $item->setGoodsQnt( floatval($position[ $item->getGoodsId() ]['count']) );
                        }
                        elseif( floatval($position[ $item->getGoodsId() ]['count']) == 0 ){
                            $this->entityManager->remove($item);
                            continue;
                        }
                    }

                    if( isset($position[ $item->getGoodsId() ]['price']) ){
                        if( floatval($position[ $item->getGoodsId() ]['price']) >= 0 ){
                            $item->setGoodsPrice( floatval($position[ $item->getGoodsId() ]['price']) );
                        }
                    }
                }
            }

            $this->entityManager->flush();

        }
        catch (\Exception $e){
            throw new \Exception('При обновлении произошла ошибка');
        }

        return true;
    }


    // dell line
    public function del(array $arGoods, int $user = null, int $order = null){
        if(!empty($order)){
            $orderId = $order;
        }
        else{
            $orderId = $this->getId($user);
        }


        $arGoods = array_map(function($val){return intval($val);}, $arGoods);

        $arResult = $this->entityManager->getRepository( OrderLine::class )->findBy( ['order_id' => $orderId, 'goods_id' => $arGoods] );

        if( !empty($arResult) ){
            foreach ($arResult as $item){
                $this->entityManager->remove($item);
            }
            $this->entityManager->flush();
        }

        return true;
    }


    // get user basket id
    public function getId(int $user = null){

        if ($this->user_id > 0 || $user != null) {
            $arFilter = [
                'state_id' => OrderRepository::$STATE_NEW
            ];

            if($user != null){
                $arFilter['user_id'] = $user;
            }
            else{
                $arFilter['user_id'] = $this->user_id;
            }


            // Ищем нашу корзину
            $order = $this->entityManager->getRepository( Order::class )->findOneBy($arFilter);

            if ( !empty( $order ) ) {
                return $order->getId();
            }

            return 0;
        }
        else{
            // Ищем нашу корзину
            $order = $this->entityManager->getRepository( Order::class )->findOneBy( [
                'user_session_key' => $this->user_session_key,
                'state_id' => OrderRepository::$STATE_NEW
            ]);


            if ( !empty( $order ) ) {
                return $order->getId();
            }
        }

    }

    // get basket
    public function getLines(int $order){

        if($order <= 0)
            throw new \Exception('Некорректный номер заказа');

        // Получаем содержимое корзины
        $order_lines = $this->entityManager->getRepository( OrderLine::class )->getOrderItemsAsArray( $order, $this->user_id, $this->user_session_key);

        if(!empty($order_lines)){
            return $order_lines;
        }

        return [];

    }

    // Получение всей информации по корзине
    public function getLinesInfo(int $order){
        if($order <= 0)
            throw new \Exception('Некорректный номер заказа');

        // Получаем содержимое корзины
        $arOrderLines = $this->getLines($order);

        if(empty($arOrderLines)) {
            return [];
        }

        $arGoods = $this->entityManager->getRepository( Goods::class )->findBy(['id' => array_column($arOrderLines, 'goods_id') ]);

        foreach ($arGoods as $item){
            $arOrderLines[$item->getId()]['goods_info'] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'section' => $item->getSection(),
                'date_update' => $item->getDateUpdate(),
                'date_create' => $item->getDateCreate(),
                'sort' => $item->getSort(),
                'code' => $item->getCode(),
                'img' => $item->getImg(),
                'price' => $item->getPrice(),
            ];
        }
        return $arOrderLines;

    }


    // create basket
    public function create(){

        $order = new Order();
        $order->setStateId(OrderRepository::$STATE_NEW);

        if ($this->user_id > 0) {
            $order->setUserId($this->user_id);
        }
        else{
            $order->setUserSessionKey($this->user_session_key);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order->getId();

    }

    // Формирование заказа
    public function formation(array $data){
        // Обновляем данные
        // Измекняем статус

        if( empty($data["first_name"]) || empty($data["last_name"]) || empty($data["email"]) || empty($data["phone"]) || empty($data["address"]) ) {
            throw new \Exception('Не заполнены обязательные поля');
        }

        $orderId = $this->getId();
        if($orderId <= 0){
            throw new \Exception('Корзина пуста');
        }

        $order = $this->entityManager->getRepository( Order::class )->find( $orderId );

        if(!empty($order)){
            // Проверка корзины на пустоту
            $arFilter = [ 'order_id' => $orderId ];
            $emOrderLine = $this->entityManager->getRepository( OrderLine::class );
            $emOrderLine->findBy( $arFilter );
            $countOrderLine = $emOrderLine->count($arFilter);
            if($countOrderLine <= 0){
                throw new \Exception('Корзина пуста');
            }


            // Обновление корзины
            if(!empty($data["first_name"]))
                $order->setFirstName($data["first_name"]);
            if(!empty($data["last_name"]))
                $order->setLastName($data["last_name"]);
            if(!empty($data["second_name"]))
                $order->setSecondName($data["second_name"]);
            if(!empty($data["phone"]))
                $order->setPhone($data["phone"]);
            if(!empty($data["email"]))
                $order->setEmail($data["email"]);
            if(!empty($data["address"]))
                $order->setAddress($data["address"]);

            $order->setStateId(OrderRepository::$STATE_PROCESSED);

            $this->entityManager->flush();

            if($this->entityManager->contains($order)){
                $response = new Response();
                $response->headers->setCookie(new Cookie('user_session_key', ''));
                $response->send();

                return $order->getId();
            }
            else{
                return 0;
            }
        }
        else{
            throw new \Exception('Корзина пуста');
        }
    }


    // Генерация ключа корзины для анонимов
    private function generate_session_key(){
        return md5(md5(rand(1000,99999).microtime()).rand(0,99999));
    }



}