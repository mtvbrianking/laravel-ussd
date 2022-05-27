<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\ChooseTag;
use Bmatovu\Ussd\Tests\TestCase;

class ChooseTagTest extends TestCase
{
    public function testHandleChoose()
    {
        $this->cache->put('prefix_exp', '/*[1]');

        $this->cache->put('prefix_gender', 'F');

        $xml = <<<'XML'
<choose>
    <when key="gender" value="M">
        <question name="club" text="Football club? "/>
    </when>
    <when key="gender" value="F">
        <question name="soap" text="TV soap? "/>
    </when>
    <otherwise>
        <question name="hobby" text="Hobbies? "/>
    </otherwise>
</choose>
XML;

        $node = $this->getNodeByTagName($xml, 'choose');

        $tag = new ChooseTag($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[1]/*[2]', $this->cache->get('prefix_exp'));
    }

    public function testHandleChooseOtherwise()
    {
        $this->cache->put('prefix_exp', '/*[1]');

        $this->cache->put('prefix_gender', 'X');

        $xml = <<<'XML'
<choose>
    <when key="gender" value="M">
        <question name="club" text="Football club? "/>
    </when>
    <when key="gender" value="F">
        <question name="soap" text="TV soap? "/>
    </when>
    <otherwise>
        <question name="hobby" text="Hobbies? "/>
    </otherwise>
</choose>
XML;

        $node = $this->getNodeByTagName($xml, 'choose');

        $tag = new ChooseTag($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[1]/*[3]', $this->cache->get('prefix_exp'));
    }

    public function testHandleChooseNoMatch()
    {
        $this->cache->put('prefix_exp', '/*[1]');

        $this->cache->put('prefix_gender', 'X');

        $xml = <<<'XML'
<choose>
    <when key="gender" value="M">
        <question name="club" text="Football club? "/>
    </when>
</choose>
XML;

        $node = $this->getNodeByTagName($xml, 'choose');

        $tag = new ChooseTag($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[2]', $this->cache->get('prefix_exp'));
    }
}
