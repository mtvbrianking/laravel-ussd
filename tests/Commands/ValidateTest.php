<?php

namespace Bmatovu\Ussd\Tests\Commands;

use Bmatovu\Ussd\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class ValidateTest extends TestCase
{
    public function testValidXml()
    {
        $menuFile = tempnam(sys_get_temp_dir(), 'phpunit_test_');

        file_put_contents($menuFile, '<menu><response text="Hello World"/></menu>');

        $this->artisan('ussd:validate', ['--file' => $menuFile])
            ->expectsOutput('OK')
            ->assertExitCode(0)
        ;

        if (file_exists($menuFile)) {
            unlink($menuFile);
        }
    }

    public function testInvalidXml()
    {
        $xsd = <<<'XML'
<xs:schema attributeFormDefault="unqualified" elementFormDefault="qualified" xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="menu" type="menuType"/>
  <xs:complexType name="menuType">
    <xs:sequence>
      <xs:element type="xs:string" name="dummy"/>
    </xs:sequence>
  </xs:complexType>
</xs:schema>
XML;

        $menuFile = tempnam(sys_get_temp_dir(), 'phpunit_test_');

        file_put_contents($menuFile, '<menu><dummy/></menu>');

        // Artisan::call('ussd:validate', ['--file' => $menuFile]);
        // dd(Artisan::output());

        $this->artisan('ussd:validate', ['--file' => $menuFile])
            ->doesntExpectOutput('OK')
            ->expectsTable(['Line', 'Element', 'Message'], [
                [1, 'dummy', 'This element is not expected. Expected is one of ( action, variable, question, response, options, list, if, choose ).'],
            ])
            ->assertExitCode(0)
        ;

        if (file_exists($menuFile)) {
            unlink($menuFile);
        }
    }
}
