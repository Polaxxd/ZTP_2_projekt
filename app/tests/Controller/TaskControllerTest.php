<?php
/**
 * Task controller tests.
 */

namespace App\Tests\Controller;

use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * class TaskControllerTest.
 */
class TaskControllerTest extends WebTestCase
{
    /**
     * Test '/task' route.
     */
    public function testTaskListRoute(): void
    {
        // given
        $client = static::createClient();

        // when
        $client->request('GET', '/task');
        $resultHttpStatusCode = $client->getResponse()->getStatusCode();

        // then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

//    /**
//     * Test default greetings.
//     */
//    public function testDefaultGreetings(): void
//    {
//        // given
//        $client = static::createClient();
//
//        // when
//        $client->request('GET', '/task');
//
//        // then
//        $this->assertSelectorTextContains('html title', 'title.task_list');
////        $this->assertSelectorTextContains('html p', 'Hello World!');
//    }

//    /**
//     * Test pesonalized greetings.
//     *
//     * @param string $name              Name
//     * @param string $expectedGreetings Expected greetings
//     *
//     * @dataProvider dataProviderForTestPersonalizedGreetings
//     */
//    public function testPersonalizedGreetings(string $name, string $expectedGreetings): void
//    {
//        // given
//        $client = static::createClient();
//
//        // when
//        $client->request('GET', '/hello/'.$name);
//
//        // then
//        $this->assertSelectorTextContains('html title', $expectedGreetings);
//        $this->assertSelectorTextContains('html p', $expectedGreetings);
//    }
//
//    /**
//     * Data provider for testPersonalizedGreetings() method.
//     *
//     * @return \Generator Test case
//     */
//    public function dataProviderForTestPersonalizedGreetings(): Generator
//    {
//        yield 'Hello Ann' => [
//            'name' => 'Ann',
//            'expectedGreetings' => 'Hello Ann!',
//        ];
//        yield 'Hello John' => [
//            'name' => 'John',
//            'expectedGreetings' => 'Hello John!',
//        ];
//        yield 'Hello Beth' => [
//            'name' => 'Beth',
//            'expectedGreetings' => 'Hello Beth!',
//        ];
//    }
}
