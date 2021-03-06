<?php

/*
 * This file is part of the async generator runtime project.
 *
 * (c) Julien Bianchi <contact@jubianchi.fr>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jubianchi\async\tests\units\runtime;

use function jubianchi\async\runtime\all;
use jubianchi\async\runtime;
use jubianchi\async\runtime\tests\func;

class all extends func
{
    /** @dataProvider valueDataProvider */
    public function testAllValue($value)
    {
        $this
            ->object($generator = all($value))->isInstanceOf(\generator::class)
            ->array(runtime\await($generator))->isIdenticalTo([$value])
        ;
    }

    /** @dataProvider valueDataProvider */
    public function testAllValues($value, $otherValue = null)
    {
        $this
            ->given($otherValue = $otherValue ?? $value)
            ->then
                ->object($generator = all($value, $otherValue))->isInstanceOf(\generator::class)
                ->array(runtime\await($generator))->isIdenticalTo([$value, $otherValue])
        ;
    }

    /** @dataProvider valueDataProvider */
    public function testAllGenerator($value)
    {
        $this
            ->given($creator = function ($limit, $value) {
                while ($limit-- > 0) {
                    yield;
                }

                return $value;
            })
            ->then
                ->object($generator = all($creator(3, $value)))->isInstanceOf(\generator::class)
                ->array(runtime\await($generator))->isIdenticalTo([$value])
        ;
    }

    /** @dataProvider valueDataProvider */
    public function testAllGenerators($value, $otherValue = null)
    {
        $this
            ->given(
                $otherValue = $otherValue ?? $value,
                $creator = function ($limit, $value) {
                    while ($limit-- > 0) {
                        yield;
                    }

                    return $value;
                }
            )
            ->then
                ->object($generator = all($creator(3, $value), $creator(5, $otherValue)))->isInstanceOf(\generator::class)
                ->array(runtime\await($generator))->isIdenticalTo([$value, $otherValue])
        ;
    }

    /** @dataProvider valueDataProvider */
    public function testAllGeneratorCreators($value, $otherValue = null)
    {
        $this
            ->given(
                $otherValue = $otherValue ?? $value,
                $creator = function ($limit, $value) {
                    while ($limit-- > 0) {
                        yield;
                    }

                    return (function () use ($value) {
                        yield;

                        return $value;
                    })();
                }
            )
            ->then
                ->object($generator = all($creator(3, $value), $creator(5, $otherValue)))->isInstanceOf(\generator::class)
                ->array(runtime\await($generator))->isIdenticalTo([$value, $otherValue])
        ;
    }

    /** @dataProvider valueDataProvider */
    public function testAllWrappedGeneratorCreators($value, $otherValue = null)
    {
        $this
            ->given(
                $otherValue = $otherValue ?? $value,
                $creator = function ($limit, $value) {
                    while ($limit-- > 0) {
                        yield;
                    }

                    return (function () use ($value) {
                        yield;

                        return $value;
                    })();
                }
            )
            ->then
                ->object($generator = all(runtime\wrap($creator(3, $value)), runtime\wrap($creator(5, $otherValue))))->isInstanceOf(\generator::class)
                ->array(runtime\await($generator))
                    ->object[0]->isInstanceOf(\generator::class)
                    ->object[1]->isInstanceOf(\generator::class)
        ;
    }

    protected function valueDataProvider()
    {
        return [
            [rand(0, PHP_INT_MAX), rand(0, PHP_INT_MAX)],
            [1 / 3, 7 / 5],
            [uniqid(), uniqid()],
            [false, true],
            [true, false],
            [null],
            [range(0, 2), range(2, 4)],
            [new \stdClass(), new \stdClass()],
        ];
    }
}
