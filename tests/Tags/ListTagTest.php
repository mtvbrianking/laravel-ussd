<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Contracts\ListProvider;
use Bmatovu\Ussd\Store;
use Bmatovu\Ussd\Tags\ListTag;
use Bmatovu\Ussd\Tests\TestCase;

class ListTagTest extends TestCase
{
    public function testHandleListTag()
    {
        $this->app['config']->set(['ussd.provider-ns' => ['Bmatovu\\Ussd\\Tests\\Tags']]);

        // dd($this->app['config']->get('ussd.provider-ns'));

        $this->store->put('_exp', '/*[1]');

        $node = $this->getNodeByTagName('<list header="Users" provider="user-accounts" prefix="user"/>', 'list');

        $tag = new ListTag($node, $this->store);

        $output = $tag->handle();

        static::assertSame("Users\n1) 0108567654\n2) 0202984256", $output);
        static::assertSame('/*[1]', $this->store->get('_pre'));
        static::assertSame('/*[2]', $this->store->get('_exp'));
    }

    public function testProccessValidAnswer()
    {
        $this->store->put('user_list', [
            [
                'id' => '536',
                'label' => '0108567654',
            ],
        ]);

        $node = $this->getNodeByTagName('<list header="Users" provider="user-accounts" prefix="user"/>', 'list');

        $tag = new ListTag($node, $this->store);

        $tag->process('1');

        static::assertSame('536', $this->store->get('user_id'));
        static::assertSame('0108567654', $this->store->get('user_label'));
    }

    public function testProccessInvalidAnswer()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('InvalidChoice');

        $this->store->put('user_list', []);

        $node = $this->getNodeByTagName('<list header="Users" provider="user-accounts" prefix="user" retries="0"/>', 'list');

        $tag = new ListTag($node, $this->store);

        $tag->process('1');
    }

    public function testProccessNoAnswer()
    {
        $this->markTestSkipped('Change in impl');

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Make a choice.');

        $this->store->put('user_list', []);

        $node = $this->getNodeByTagName('<list header="Users" provider="user-accounts" prefix="user"/>', 'list');

        $tag = new ListTag($node, $this->store);

        $tag->process('');
    }

    public function testHandleNonCompliantProvider()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage("'NonCompliantProvider' must implement the 'ListProvider' interface.");

        $this->app['config']->set(['ussd.provider-ns' => ['Bmatovu\\Ussd\\Tests\\Tags']]);

        // dd($this->app['config']->get('ussd.provider-ns'));

        $this->store->put('_exp', '/*[1]');

        $node = $this->getNodeByTagName('<list header="Users" provider="non-compliant" prefix="user"/>', 'list');

        $tag = new ListTag($node, $this->store);

        $output = $tag->handle();
    }

    public function testHandleInvalidListProvider()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('{"0.id":["The 0.id field is required."],"0.label":["The 0.label field is required."],"1.label":["The 1.label field is required."]}');

        $this->app['config']->set(['ussd.provider-ns' => ['Bmatovu\\Ussd\\Tests\\Tags']]);

        // dd($this->app['config']->get('ussd.provider-ns'));

        $this->store->put('_exp', '/*[1]');

        $node = $this->getNodeByTagName('<list header="Users" provider="invalid" prefix="user"/>', 'list');

        $tag = new ListTag($node, $this->store);

        $output = $tag->handle();
    }

    public function testHandleMissingListProvider()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('MissingProvider');

        $this->app['config']->set(['ussd.provider-ns' => ['Bmatovu\\Ussd\\Tests\\Tags']]);

        // dd($this->app['config']->get('ussd.provider-ns'));

        $this->store->put('_exp', '/*[1]');

        $node = $this->getNodeByTagName('<list header="Users" provider="unknown" prefix="user"/>', 'list');

        $tag = new ListTag($node, $this->store);

        $output = $tag->handle();

        // assert store has missing_provider = UnknownProvider
        // assert store has missing_provider_fqcn = Bmatovu\\Ussd\\Tests\\Tags\\UnknownProvider
    }
}

class UserAccountsProvider implements ListProvider
{
    protected Store $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function load(): array
    {
        return [
            [
                'id' => 536,
                'label' => '0108567654',
            ],
            [
                'id' => '275',
                'label' => '0202984256',
            ],
        ];
    }
}

class NonCompliantProvider
{
    protected Store $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function load(): array
    {
        return [];
    }
}

class InvalidProvider implements ListProvider
{
    protected Store $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function load(): array
    {
        return [
            [
                'id' => null,
                'name' => '0108567654',
            ],
            [
                'id' => '275',
                'name' => '0202984256',
            ],
        ];
    }
}
