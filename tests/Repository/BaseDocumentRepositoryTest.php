<?php

namespace MdjamanCommonTest\Repository;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
use MdjamanCommon\Repository\BaseDocumentRepository;
use MongoDB\BSON\Regex;
use PHPUnit\Framework\TestCase;

/**
 * fullSearchText() built a \MongoRegex, a class of the removed ext-mongo driver: with the mongodb extension every call
 * was a fatal "Class 'MongoRegex' not found". It also pasted the typed text into the pattern unescaped.
 */
class BaseDocumentRepositoryTest extends TestCase
{
    public function testTheClassOfTheRemovedDriverIsNotNeeded()
    {
        $this->assertFalse(class_exists('MongoRegex', false), 'the test must run where ext-mongo does not exist');
        $this->assertInstanceOf(Regex::class, BaseDocumentRepository::containsRegex('abc'));
    }

    public function testContainsMatchesIgnoringCase()
    {
        $regex = BaseDocumentRepository::containsRegex('abc');

        $this->assertSame('i', $regex->getFlags());
        $this->assertSame(1, preg_match('/' . $regex->getPattern() . '/' . $regex->getFlags(), 'xxABCxx'));
        $this->assertSame(0, preg_match('/' . $regex->getPattern() . '/' . $regex->getFlags(), 'xxABxCxx'));
    }

    public function testTypedTextIsLiteralNotARegularExpression()
    {
        $dot = BaseDocumentRepository::containsRegex('A.1');
        $this->assertSame(1, preg_match('/' . $dot->getPattern() . '/i', 'ZZA.1'));
        $this->assertSame(0, preg_match('/' . $dot->getPattern() . '/i', 'ZZAX1'), 'a dot is a dot, not any character');

        // an unbalanced parenthesis, a star and a slash used to make the pattern invalid or wild
        foreach (['(1', 'a*', '[x', 'a/b', '\\', '^$'] as $text) {
            $regex = BaseDocumentRepository::containsRegex($text);
            $this->assertSame(1, preg_match('/' . str_replace('/', '\\/', $regex->getPattern()) . '/i', 'q' . $text . 'q'), $text);
        }
    }

    /** the query fullSearchText() would run, and what was asked of the builder on the way */
    private function recordingRepository(array &$seen): BaseDocumentRepository
    {
        $expr = $this->createMock(Expr::class);
        $expr->method('field')->willReturnSelf();
        $expr->method('equals')->willReturnCallback(function ($value) use (&$seen, $expr) {
            $seen['equals'][] = $value;

            return $expr;
        });

        $qb = $this->createMock(Builder::class);
        $qb->method('expr')->willReturn($expr);
        $qb->method('addOr')->willReturnCallback(function ($e) use (&$seen, $qb) {
            $seen['or'] = ($seen['or'] ?? 0) + 1;

            return $qb;
        });
        $qb->method('field')->willReturnCallback(function ($name) use (&$seen, $qb) {
            $seen['fields'][] = $name;

            return $qb;
        });
        $qb->method('equals')->willReturnCallback(function ($value) use (&$seen, $qb) {
            $seen['paramValues'][] = $value;

            return $qb;
        });
        $qb->method('sort')->willReturnCallback(function ($by, $dir) use (&$seen, $qb) {
            $seen['sort'] = [$by, $dir];

            return $qb;
        });
        $qb->method('limit')->willReturnCallback(function ($n) use (&$seen, $qb) {
            $seen['limit'] = $n;

            return $qb;
        });
        $qb->method('skip')->willReturnCallback(function ($n) use (&$seen, $qb) {
            $seen['skip'] = $n;

            return $qb;
        });
        return new class($qb) extends BaseDocumentRepository {
            private $builder;

            public function __construct($builder)
            {
                $this->builder = $builder;
            }

            public function createQueryBuilder(): \Doctrine\ODM\MongoDB\Query\Builder
            {
                return $this->builder;
            }

            public function build(...$arguments)
            {
                return $this->buildFullSearchTextQuery(...$arguments);
            }
        };
    }

    public function testFullSearchTextAsksForAnyOfTheCriteriaAsAnEscapedRegexAndNarrowsByParams()
    {
        $seen = [];
        $repo = $this->recordingRepository($seen);

        $repo->build(['barcode' => 'a(b', 'code' => 'Z'], 'barcode', -1, 10, 5, ['disease' => 'd1']);

        $this->assertSame(2, $seen['or']);
        $this->assertCount(2, $seen['equals']);
        foreach ($seen['equals'] as $value) {
            $this->assertInstanceOf(Regex::class, $value);
            $this->assertSame('i', $value->getFlags());
        }
        $this->assertSame('a\\(b', $seen['equals'][0]->getPattern());
        $this->assertSame(['disease'], $seen['fields']);
        $this->assertSame(['d1'], $seen['paramValues']);
        $this->assertSame(['barcode', -1], $seen['sort']);
        $this->assertSame(10, $seen['limit']);
        $this->assertSame(5, $seen['skip']);
    }

    public function testNoCriteriaAndNoOffsetDoNotBreakTheQuery()
    {
        $seen = [];
        $repo = $this->recordingRepository($seen);

        // null criteria used to be iterated as-is; a limit without an offset used to hand null to skip(int)
        $repo->build(null, null, 1, 10, null, []);
        $this->assertArrayNotHasKey('or', $seen);
        $this->assertSame(0, $seen['skip']);
    }
}
