<?php

namespace Bmatovu\Ussd\Tests;

use Bmatovu\Ussd\Parser;

class ParserTest extends TestCase
{
    public function testMissingTag()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Missing class: Bmatovu\\Ussd\\Tags\\DummyTag');

        $xpath = $this->xmlToXpath('<dummy/>');

        $parser = new Parser($xpath, '/*[1]', $this->cache, 'prefix', 'test-session', 120);

        $parser->parse('');
    }

    public function testExceptionExit()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Bye bye.');

        $xpath = $this->xmlToXpath('<response text="Bye bye."/>');

        $parser = new Parser($xpath, '/*[1]', $this->cache, 'prefix', 'test-session', 120);

        $parser->parse('');
    }

    public function testProceedQuietly()
    {
        $xml = <<<'XML'
<document>
    <variable name="name" value="John Doe"/>
    <question name="alias" text="Enter username: "/>
</document>
XML;

        $xpath = $this->xmlToXpath($xml);

        $parser = new Parser($xpath, '/document/*[1]', $this->cache, 'prefix', 'test-session', 120);

        $output = $parser->parse('');

        static::assertSame('Enter username: ', $output);
        static::assertSame('John Doe', $this->cache->get('prefix_name'));
    }

    public function testReuseSession()
    {
        $this->cache->put('prefix_session_id', 'qwe534');
        $this->cache->put('prefix_pre', '');
        $this->cache->put('prefix_exp', '/document/*[1]');

        $xml = <<<'XML'
<document>
    <question name="alias" text="Enter username: "/>
</document>
XML;

        $xpath = $this->xmlToXpath($xml);

        $parser = new Parser($xpath, '/document/*[1]', $this->cache, 'prefix', 'qwe534', 120);

        $output = $parser->parse('');

        static::assertSame('Enter username: ', $output);
        static::assertSame('qwe534', $this->cache->get('prefix_session_id'));
        static::assertSame('/document/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/document/*[2]', $this->cache->get('prefix_exp'));
    }

    public function testBreakpoints()
    {
        $xml = <<<'XML'
<document>
    <variable name="gender" value="M"/>
    <if key="gender" value="M">
        <variable name="color" value="Blue"/>
    </if>
    <question name="greet" text="Say hi: "/>
</document>
XML;
        $this->cache->put('prefix_session_id', '54746');
        $this->cache->put('prefix_pre', '/document/*[2]/*[1]');
        $this->cache->put('prefix_exp', '/document/*[2]/*[2]');
        $this->cache->put('prefix_breakpoints', '[{"\/document\/*[2]\/*[2]":"\/document\/*[3]"}]');

        $xpath = $this->xmlToXpath($xml);

        $parser = new Parser($xpath, '/document/*[1]', $this->cache, 'prefix', '54746', 120);

        $output = $parser->parse('');

        static::assertSame('Say hi: ', $output);
    }
}
