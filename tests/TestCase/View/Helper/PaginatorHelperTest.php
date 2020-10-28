<?php
namespace BootstrapUI\Test\TestCase\View\Helper;

use BootstrapUI\View\Helper\PaginatorHelper;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\I18n\I18n;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * PaginatorHelperTest class
 *
 */
class PaginatorHelperTest extends TestCase
{
    /**
     * @var \Cake\View\View
     */
    protected $View;

    /**
     * @var \BootstrapUI\View\Helper\PaginatorHelper
     */
    protected $Paginator;

    /**
     * @var string
     */
    public $locale;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Configure::write('Config.language', 'eng');

        $request = new ServerRequest([
            'params' => [
                'plugin' => null,
                'controller' => 'Clients',
                'action' => 'index',
                '_ext' => null,
                'pass' => [],
            ],
        ]);
        $request = $request->withParam('paging', [
            'Article' => [
                'page' => 1,
                'current' => 9,
                'count' => 62,
                'prevPage' => false,
                'nextPage' => true,
                'pageCount' => 7,
                'sort' => null,
                'direction' => null,
                'limit' => null,
            ],
        ]);

        $this->View = new View($request);
        $this->Paginator = new PaginatorHelper($this->View);
        $this->Paginator->Js = $this->getMockBuilder('Cake\View\Helper\PaginatorHelper')
            ->setConstructorArgs([$this->View])
            ->getMock();

        Configure::write('Routing.prefixes', []);
        Router::reload();
        Router::connect('/{controller}/{action}/*');
        Router::setRequest($request);

        $this->locale = I18n::getLocale();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->View, $this->Paginator);

        I18n::setLocale($this->locale);
    }

    /**
     * testNumbers method
     *
     * @return void
     */
    public function testNumbers()
    {
        $request = $this->Paginator->getView()->getRequest()->withAttribute('paging', [
            'Client' => [
                'page' => 8,
                'current' => 3,
                'count' => 30,
                'prevPage' => false,
                'nextPage' => 2,
                'pageCount' => 15,
            ],
        ]);
        $this->Paginator->getView()->setRequest($request);
        $result = $this->Paginator->numbers();
        $expected = [
            'ul' => ['class' => 'pagination'],
            '<li', ['a' => ['href' => '/Clients/index?page=4']], '4', '/a', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=5']], '5', '/a', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=6']], '6', '/a', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=7']], '7', '/a', '/li',
            ['li' => ['class' => 'active']], '<span', '8', 'span' => ['class' => 'sr-only'], '(current)', '/span', '/span', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=9']], '9', '/a', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=10']], '10', '/a', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=11']], '11', '/a', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=12']], '12', '/a', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $request = $this->Paginator->getView()->getRequest()->withParam('paging', [
            'Client' => [
                'page' => 8,
                'current' => 3,
                'count' => 30,
                'prevPage' => false,
                'nextPage' => 2,
                'pageCount' => 15,
            ],
        ]);
        $this->Paginator->getView()->setRequest($request);
        $result = $this->Paginator->numbers(['prev' => true, 'next' => true]);
        $expected = [
            'ul' => ['class' => 'pagination'],
            ['li' => ['class' => 'previous disabled']], ['a' => []], ['span' => ['aria-hidden' => 'true']], '&laquo;', '/span', '/a', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=4']], '4', '/a', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=5']], '5', '/a', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=6']], '6', '/a', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=7']], '7', '/a', '/li',
            ['li' => ['class' => 'active']], '<span', '8', 'span' => ['class' => 'sr-only'], '(current)', '/span', '/span', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=9']], '9', '/a', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=10']], '10', '/a', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=11']], '11', '/a', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=12']], '12', '/a', '/li',
            ['li' => ['class' => 'next']], ['a' => ['rel' => 'next', 'aria-label' => 'Next', 'href' => '/Clients/index?page=9']], ['span' => ['aria-hidden' => 'true']], '&raquo;', '/span', '/a', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $request = $this->Paginator->getView()->getRequest()->withAttribute('paging', [
            'Client' => [
                'page' => 1,
                'current' => 1,
                'count' => 2,
                'prevPage' => false,
                'nextPage' => 2,
                'pageCount' => 2,
            ],
        ]);
        $this->Paginator->getView()->setRequest($request);
        $result = $this->Paginator->numbers(['size' => 'lg']);
        $expected = [
            'ul' => ['class' => 'pagination pagination-lg'],
            ['li' => ['class' => 'active']], '<span', '1', 'span' => ['class' => 'sr-only'], '(current)', '/span', '/span', '/li',
            '<li', ['a' => ['href' => '/Clients/index?page=2']], '2', '/a', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);
    }

    protected function setupHelper($options)
    {
        $request = $this->View->getRequest()->withAttribute('params', [
            'pass' => [],
            'paging' => $options,
        ]);

        $this->View = new View($request);
        $this->Paginator = new PaginatorHelper($this->View);
    }
}
