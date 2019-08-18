<?php

namespace Happypixels\Shopr\Models;

use Illuminate\Database\Eloquent\Model;
use Happypixels\Shopr\Contracts\Shoppable as ShoppableContract;

class Shoppable extends Model implements ShoppableContract
{
    /**
     * The identifier of the model.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The name/title of the model.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * The price of the model.
     *
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Whether or not the model is a discount coupon.
     *
     * @return bool
     */
    public function isDiscount() : bool
    {
        return false;
    }

    /**
     * Whether or not the item should be included in the default list of items.
     *
     * @return bool
     */
    public function shouldBeIncludedInItemList() : bool
    {
        return true;
    }
}
