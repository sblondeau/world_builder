<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent()]
final class Cube
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?int $x = null;

    #[LiveProp]
    public ?int $y = null;


    #[LiveProp]
    public ?int $z = null;

    #[LiveProp]
    public ?string $type = null;
}
