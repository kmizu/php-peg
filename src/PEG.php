<?php
namespace PEG;

class ParseResult {
    private $value;
    private string $rest;

    /**
     * @return object
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getRest(): string
    {
        return $this->rest;
    }

    public function __construct($value, string $rest)
    {
        $this->value = $value;
        $this->rest = $rest;
    }
}

function pcat(Parser $a, Parser $b): ?Parser
{
    return parser(function($input) use ($a, $b) {
       $result_a = $a->parse($input);
       if(is_null($result_a)) {
           return null;
       }
       $rest = $result_a->getRest();
       $result_b = $b->parse($rest);
       if(is_null($result_b)) {
           return null;
       }
       return new ParseResult([$result_a->getValue(), $result_b->getValue()], $result_b->getRest());
    });
}

function por(Parser $a, Parser $b): ?Parser
{
    return parser(function($input) use ($a, $b) {
        $result_a = $a->parse($input);
        if(is_null($result_a)) {
            return $b->parse($input);
        } else {
            return $result_a;
        }
    });
}

function prepeat0(Parser $a): ?Parser
{
    return parser(function($input) use ($a) {
        $result = $a->parse($input);
        if(is_null($result)) {
            return new ParseResult([], $input);
        }
        $values = [];
        do {
            array_push($values, $result->getValue());
            $rest = $result->getRest();
            $result = $a->parse($rest);
        } while (!is_null($result));
        return new ParseResult($values, $rest);
    });
}

function pmap(Parser $a, $f): Parser {
    return parser(function($input) use ($a, $f) {
        $result = $a->parse($input);
        if(is_null($result)) return null;
        return new ParseResult($f($result->getValue()), $result->getRest());
    });
}

function prepeat1(Parser $a): ?Parser
{
    return pmap(pcat($a, prepeat0($a)), function($v) {
        list($v1, $v2) = $v;
        array_push($v2, $v1);
        return $v2;
    });
}

interface Parser {
    public function parse(string $input): ?ParseResult;
}

function parser($parser_function) : Parser {
    return new class($parser_function) implements Parser {
        private $parser_function;
        public function __construct($parser_function)
        {
            $this->parser_function = $parser_function;
        }
        public function parse(string $input): ?ParseResult
        {
            return ($this->parser_function)($input);
        }
    };
}

function literal(string $value): Parser {
    return parser(function($input) use ($value) {
        if(strpos($input, $value) === 0) {
            return new ParseResult($value, substr($input, strlen($value)));
        } else {
            return null;
        }
    });
}

