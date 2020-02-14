<?php
namespace App\Service;



use App\Entity\Order;
use App\Entity\OrderLine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class OrderService{

    private $em;
    private $security;

    private $user;
    private $user_id;


    function __construct (EntityManagerInterface $entityManager, Security $security){
        $this->em = $entityManager;
        $this->security = $security;

        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->user = $this->security->getUser();
            $this->user_id = $this->user->getId();
        }
    }


    function getOrder(int $order = 0, int $user = 0){
        $filter = [];

        if($user > 0)
            $filter['user_id'] = $user;

        if ($order > 0)
            $filter['id'] = $order;

        if(empty($filter['user_id']) && !empty($this->user_id))
            $filter['user_id'] = $this->user_id;


        $result = $this->em->getRepository(Order::class)->findByArray($filter);

        $arOrderId = array_keys($result);

        $arInfo = $this->em->getRepository(OrderLine::class)->getOrdersSum($arOrderId);


        return [
            'arResult' => $result,
            'arInfo' => $arInfo
        ];
    }
}