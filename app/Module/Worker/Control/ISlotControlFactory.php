<?php

namespace App\Module\Worker\Control;

interface ISlotControlFactory
{
    public function create(): SlotControl;
}