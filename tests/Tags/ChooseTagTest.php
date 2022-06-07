<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\ChooseTag;
use Bmatovu\Ussd\Tests\TestCase;

class ChooseTagTest extends TestCase
{
    public function testHandleChoose()
    {
        $this->store->put('_exp', '/*[1]');

        $this->store->put('gender', 'F');

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

        $tag = new ChooseTag($node, $this->store);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]', $this->store->get('_pre'));
        static::assertSame('/*[1]/*[2]', $this->store->get('_exp'));
    }

    public function testHandleChooseOtherwise()
    {
        $this->store->put('_exp', '/*[1]');

        $this->store->put('gender', 'X');

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

        $tag = new ChooseTag($node, $this->store);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]', $this->store->get('_pre'));
        static::assertSame('/*[1]/*[3]', $this->store->get('_exp'));
    }

    public function testHandleChooseNoMatch()
    {
        $this->store->put('_exp', '/*[1]');

        $this->store->put('gender', 'X');

        $xml = <<<'XML'
<choose>
    <when key="gender" value="M">
        <question name="club" text="Football club? "/>
    </when>
</choose>
XML;

        $node = $this->getNodeByTagName($xml, 'choose');

        $tag = new ChooseTag($node, $this->store);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]', $this->store->get('_pre'));
        static::assertSame('/*[2]', $this->store->get('_exp'));
    }
}
