<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderLineRepository")
 */
class OrderLine
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $order_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $goods_id;

    /**
     * @ORM\Column(type="float")
     */
    private $goods_qnt;

    /**
     * @ORM\Column(type="float")
     */
    private $goods_price;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?int
    {
        return $this->order_id;
    }

    public function setOrderId(int $order_id): self
    {
        $this->order_id = $order_id;

        return $this;
    }

    public function getGoodsId(): ?int
    {
        return $this->goods_id;
    }

    public function setGoodsId(int $goods_id): self
    {
        $this->goods_id = $goods_id;

        return $this;
    }

    public function getGoodsQnt(): ?float
    {
        return $this->goods_qnt;
    }

    public function setGoodsQnt(float $goods_qnt): self
    {
        $this->goods_qnt = $goods_qnt;

        return $this;
    }

    public function getGoodsPrice(): ?float
    {
        return $this->goods_price;
    }

    public function setGoodsPrice(float $goods_price): self
    {
        $this->goods_price = $goods_price;

        return $this;
    }
}
