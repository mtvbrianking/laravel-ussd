<?php

namespace Bmatovu\Ussd\Tests;

use Bmatovu\Ussd\Ussd;

class UssdTest extends TestCase
{
    public function testMissingTag()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Missing tag DummyTag.');

        $xpath = $this->xmlToXpath('<dummy/>');

        $ussd = (new Ussd($xpath, 'ussd_wScXk'))->entry('/*[1]');

        $ussd->handle();
    }

    public function testExceptionExit()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Bye bye.');

        $xpath = $this->xmlToXpath('<response text="Bye bye."/>');

        $ussd = (new Ussd($xpath, 'ussd_wScXk'))->entry('/*[1]');

        $ussd->handle();
    }

    public function testProceedQuietly()
    {
        $xml = <<<'XML'
<menu>
    <variable name="name" value="John Doe"/>
    <question name="alias" text="Enter username: "/>
</menu>
XML;

        $xpath = $this->xmlToXpath($xml);

        $ussd = new Ussd($xpath, 'ussd_wScXk');

        $ussd->store = $this->store; // ->set('name', 'John Doe');

        $output = $ussd->handle();

        static::assertSame('Enter username: ', $output);
        static::assertSame('John Doe', $ussd->store->get('name'));
    }

    public function testReuseSession()
    {
        $this->store->put('_session_id', 'ussd_wScXk');
        $this->store->put('_pre', '');
        $this->store->put('_exp', '/menu/*[1]');

        $xml = <<<'XML'
<menu>
    <question name="alias" text="Enter username: "/>
</menu>
XML;

        $xpath = $this->xmlToXpath($xml);

        $ussd = new Ussd($xpath, 'ussd_wScXk');

        $output = $ussd->handle();

        static::assertSame('Enter username: ', $output);
        static::assertSame('ussd_wScXk', $this->store->get('_session_id'));
        static::assertSame('/menu/*[1]', $this->store->get('_pre'));
        static::assertSame('/menu/*[2]', $this->store->get('_exp'));
    }

    public function testBreakpoints()
    {
        $xml = <<<'XML'
<menu>
    <variable name="gender" value="M"/>
    <if key="gender" value="M">
        <variable name="color" value="Blue"/>
    </if>
    <question name="greet" text="Say hi: "/>
</menu>
XML;
        $this->store->put('_session_id', 'ussd_wScXk');
        $this->store->put('_pre', '/menu/*[2]/*[1]');
        $this->store->put('_exp', '/menu/*[2]/*[2]');
        $this->store->put('_breakpoints', '[{"\/menu\/*[2]\/*[2]":"\/menu\/*[3]"}]');

        $xpath = $this->xmlToXpath($xml);

        $ussd = new Ussd($xpath, 'ussd_wScXk');

        $output = $ussd->handle();

        static::assertSame('Say hi: ', $output);
    }

    public function testParseLongCode()
    {
        $xml = <<<'XML'
<menu>
    <question name="title" text="Enter title: "/>
    <question name="fname" text="Enter first name: "/>
    <question name="lname" text="Enter last name: "/>
</menu>
XML;

        $xpath = $this->xmlToXpath($xml);

        $ussd = (new Ussd($xpath, 'ussd_wScXk'));

        $output = $ussd->handle('Mr*John');

        static::assertSame('Enter last name: ', $output);
    }

    public function testTracksAnswers()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Hi Mr. John Doe');

        $xml = <<<'XML'
<menu>
    <!--<question name="title" text="Enter title: "/>-->
    <question name="fname" text="Enter first name: "/>
    <question name="lname" text="Enter last name: "/>
    <response text="Hi {{title}} {{fname}} {{lname}}."/>
</menu>
XML;

        $xpath = $this->xmlToXpath($xml);

        $ussd = (new Ussd($xpath, 'ussd_wScXk'));

        $ussd->store->put('title', 'Mr.');
        $ussd->store->put('_answer', 'Mr.');

        $ussd->handle('John*Doe');

        static::assertSame('Mr.*John*Doe', $ussd->store->get('_answer'));
    }

    public function testPathFromFile()
    {
        $xml = <<<'XML'
<menu>
    <question name="alias" text="Enter username: "/>
</menu>
XML;

        $menuFile = tempnam(sys_get_temp_dir(), 'phpunit_test_');

        file_put_contents($menuFile, $xml);

        $ussd = (new Ussd($menuFile, 'ussd_wScXk'));

        $output = $ussd->handle('');

        static::assertSame('Enter username: ', $output);

        if (file_exists($menuFile)) {
            unlink($menuFile);
        }
    }

    public function testSaveOptions()
    {
        $xpath = $this->xmlToXpath('<question name="alias" text="Enter username: "/>');

        $rand = rand(100, 1000);

        $ussd = (new Ussd($xpath, 'ussd_wScXk'))
            ->entry('/*[1]')
            ->save(['rand' => $rand])
        ;

        $ussd->handle();

        static::assertSame($rand, $ussd->store->get('rand'));
    }
}
