<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent()]
final class Map
{
    public const LEFT = 'left';
    public const RIGHT = 'right';
    public const TOP = 'top';
    public const BOTTOM = 'bottom';
    public const FRONT = 'front';
    public const BACK = 'back';

    public const STEP = 20;
    public const MAP_SIZE = 12;

    public const INTERACTIONS = [
        self::LEFT => [-1, 0, 0],
        self::RIGHT => [1, 0, 0],
        self::TOP => [0, 0, 1],
        self::BOTTOM => [0, 0, -1],
        self::FRONT => [0, 1, 0],
        self::BACK => [0, -1, 0],
    ];

    use DefaultActionTrait;

    #[LiveProp()]
    public array $cubes = [];

    #[LiveProp(writable: true)]
    public string $currentType = 'grass';

    #[LiveProp()]
    public int $max = 1;

    #[LiveProp()]
    public int $rotationX = 60;

    #[LiveProp()]
    public int $rotationZ = 30;

    #[LiveProp()]
    public int $zoom = 5;
    public function mount()
    {
        for ($i = 1; $i <= self::MAP_SIZE; $i++) {
            for ($j = 1; $j <= self::MAP_SIZE; $j++) {
                    $this->addCube($i, $j, 1);
                
            }
        }
        //$this->initializeMap(20);
        $this->getMax();
    }


    private function randomCoordinates(int $height = 10)
    {
        $z = rand(1, $height);
        $x = rand(1, self::MAP_SIZE);
        $y = rand(1, self::MAP_SIZE);

        return [$x, $y, $z];
    }

    private function cubeExists(int $x, int $y, int $z): bool
    {
        foreach ($this->cubes as $cube) {
            if ($cube['x'] === $x && $cube['y'] === $y && $cube['z'] === $z) {
                return true;
            }
        }

        return false;
    }

    private function addCubesBelow(int $x, int $y, int $z): void
    {
        for ($i = $x - 1; $i <= $x + 1; $i++) {
            for ($j = $y - 1; $j <= $y + 1; $j++) {
                if (!$this->cubeExists($i, $j, $z - 1) && $i > 0 && $i <= self::MAP_SIZE && $j > 0 && $j <= self::MAP_SIZE && $z > 0) {
                    $this->addCube($i, $j, $z - 1);
                }
            }
        }
    }

    public function initializeMap(int $size = 10)
    {
        $this->addCube(...$this->randomCoordinates());
        $this->addCube(...$this->randomCoordinates());
        $this->addCube(...$this->randomCoordinates());
        $this->addCube(...$this->randomCoordinates());
        $this->addCube(...$this->randomCoordinates());


        $maxZ = $minZ = 1;
        foreach ($this->cubes as $cube) {
            $maxZ = max($maxZ, $cube['z']);
            $minZ = min($minZ, $cube['z']);
        }
        for ($z = $maxZ; $z >= $minZ; $z--) {
            for ($x = 1; $x <= $size; $x++) {
                for ($y = 1; $y <= $size; $y++) {
                    if ($this->cubeExists($x, $y, $z)) {
                        $this->addCubesBelow($x, $y, $z);
                    } elseif ($z === $minZ) {
                        $this->addCube($x, $y, $z, 'water');
                    }
                }
            }
        }
    }

    public function addCube(#[LiveArg] $x, #[LiveArg] $y, #[LiveArg] $z, ?string $type = null)
    {
        if ($x > 0 && $y > 0 && $z > 0) {

            $this->cubes[] = ['x' => $x, 'y' => $y, 'z' => $z, 'type' => $type ?? $this->currentType];
        }
    }

    #[LiveAction]
    #[LiveListener('interact')]
    public function interact(#[LiveArg] int $key, #[LiveArg] string $face)
    {
        $cube = $this->cubes[$key];
        if ($this->currentType === 'destroy') {
            unset($this->cubes[$key]);
        } else {

            [$xModifier, $yModifier, $zModifier] = self::INTERACTIONS[$face];
            $x = $cube['x'] + $xModifier;
            $y = $cube['y'] + $yModifier;
            $z = $cube['z'] + $zModifier;

            $this->addCube($x, $y, $z);
        }
    }

    public function getMax(): int
    {
        foreach ($this->cubes as $cube) {
            $max = max($this->max, $cube['x'], $cube['y']);
        }

        return $this->max = $max;
    }

    #[LiveAction]
    public function moveLeft()
    {
        $this->rotationZ += self::STEP;
    }
    #[LiveAction]
    public function moveRight()
    {
        $this->rotationZ -= self::STEP;
    }
    #[LiveAction]
    public function moveTop()
    {
        $this->rotationX += self::STEP;
    }
    #[LiveAction]
    public function moveBottom()
    {
        $this->rotationX -= self::STEP;
    }

    #[LiveAction]
    public function zoomIn()
    {
        $this->zoom += self::STEP / 10;
    }
    #[LiveAction]
    public function zoomOut()
    {
        $this->zoom -= self::STEP / 10;
    }
}
