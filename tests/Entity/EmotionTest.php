<?php

namespace App\Tests\Entity;

use App\Entity\Emotion;
use PHPUnit\Framework\TestCase;

class EmotionTest extends TestCase
{
    public function testGetName()
    {
        $emotion = new Emotion();
        $emotion->setName('Joy');

        $this->assertEquals('Joy', $emotion->getName());
    }

    public function testGetDescription()
    {
        $emotion = new Emotion();
        $emotion->setDescription('Feeling happy and content');

        $this->assertEquals('Feeling happy and content', $emotion->getDescription());
    }
}
