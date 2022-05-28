<?php

namespace Bmatovu\Ussd\Tests;

use Bmatovu\Ussd\Parser;

class ParserTest extends TestCase
{
    public function testMissingOptions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Missing parser options: session_id, phone_number, service_code, and expression');

        $xpath = $this->xmlToXpath('<dummy/>');

        $opts = [];

        $parser = new Parser($xpath, $opts, $this->cache, 120);

        $parser->parse('');
    }

    public function testMissingTag()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Missing class: Bmatovu\\Ussd\\Tags\\DummyTag');

        $xpath = $this->xmlToXpath('<dummy/>');

        $opts = [
            'session_id' => 'qwerty',
            'phone_number' => '256712999222',
            'service_code' => '321',
            'expression' => '/*[1]',
        ];

        $parser = new Parser($xpath, $opts, $this->cache, 120);

        $parser->parse('');
    }

    public function testExceptionExit()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Bye bye.');

        $xpath = $this->xmlToXpath('<response text="Bye bye."/>');

        $opts = [
            'session_id' => 'qwerty',
            'phone_number' => '256712999222',
            'service_code' => '321',
            'expression' => '/*[1]',
        ];

        $parser = new Parser($xpath, $opts, $this->cache, 120);

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

        $opts = [
            'session_id' => 'qwe534',
            'phone_number' => '256712999222',
            'service_code' => '321',
            'expression' => '/document/*[1]',
        ];

        $parser = new Parser($xpath, $opts, $this->cache, 120);

        $output = $parser->parse('');

        static::assertSame('Enter username: ', $output);
        static::assertSame('John Doe', $this->cache->get('256712999222321_name'));
    }

    public function testReuseSession()
    {
        $this->cache->put('256712999222321_session_id', 'qwe534');
        $this->cache->put('256712999222321_pre', '');
        $this->cache->put('256712999222321_exp', '/document/*[1]');

        $xml = <<<'XML'
<document>
    <question name="alias" text="Enter username: "/>
</document>
XML;

        $xpath = $this->xmlToXpath($xml);

        $opts = [
            'session_id' => 'qwe534',
            'phone_number' => '256712999222',
            'service_code' => '321',
            'expression' => '/document/*[1]',
        ];

        $parser = new Parser($xpath, $opts, $this->cache, 120);

        $output = $parser->parse('');

        static::assertSame('Enter username: ', $output);
        static::assertSame('qwe534', $this->cache->get('256712999222321_session_id'));
        static::assertSame('/document/*[1]', $this->cache->get('256712999222321_pre'));
        static::assertSame('/document/*[2]', $this->cache->get('256712999222321_exp'));
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
        $this->cache->put('256712999222321_session_id', '54746');
        $this->cache->put('256712999222321_pre', '/document/*[2]/*[1]');
        $this->cache->put('256712999222321_exp', '/document/*[2]/*[2]');
        $this->cache->put('256712999222321_breakpoints', '[{"\/document\/*[2]\/*[2]":"\/document\/*[3]"}]');

        $xpath = $this->xmlToXpath($xml);

        $opts = [
            'session_id' => '54746',
            'phone_number' => '256712999222',
            'service_code' => '321',
            'expression' => '/document/*[1]',
        ];

        $parser = new Parser($xpath, $opts, $this->cache, 120);

        $output = $parser->parse('');

        static::assertSame('Say hi: ', $output);
    }
}
