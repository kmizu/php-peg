<?php

use PHPUnit\Framework\TestCase;
use function PEG\literal;
use function PEG\pcat;
use function PEG\por;
use function PEG\prepeat0;
use function PEG\prepeat1;

require 'src/PEG.php';

class PEGTest extends TestCase
{
    public function testLiteral()
    {
        echo getcwd();

        $parser = PEG\literal('A');
        $result = $parser->parse('A');
        self::assertEquals('A', $result->getValue());
    }

    public function testPcat()
    {
        $cat_parser = pcat(literal('FOO'), literal('BAR'));
        self::assertEquals("BAZ", $cat_parser->parse('FOOBARBAZ')->getRest());
    }

    public function testPOr()
    {

        $or_parser = por(literal('FOO'), literal('BAR'));
        self::assertEquals("_A", $or_parser->parse('FOO_A')->getRest());
        self::assertEquals("_B", $or_parser->parse('BAR_B')->getRest());
    }

    public function testPRepeat0()
    {

        $repeat_parser = prepeat0(literal("A"));
        self::assertEquals("", implode(',', $repeat_parser->parse('')->getValue()));
        self::assertEquals(['A', 'A', 'A'], $repeat_parser->parse('AAA')->getValue());
        self::assertEquals(['A', 'A', 'A', 'A'], $repeat_parser->parse('AAAAB')->getValue());
        self::assertEquals("B", $repeat_parser->parse('AAAAAAAB')->getRest());
    }

    public function testRepeat1()
    {
        $repeat_parser = prepeat1(literal("A"));
        self::assertTrue(is_null($repeat_parser->parse("")));
        self::assertEquals(['A', 'A', 'A'], $repeat_parser->parse('AAA')->getValue());
        self::assertEquals("B", $repeat_parser->parse('AAAAAAAB')->getRest());

    }
}
