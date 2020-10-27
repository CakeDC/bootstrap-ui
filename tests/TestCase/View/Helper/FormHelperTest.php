<?php

namespace BootstrapUI\Test\TestCase\View\Helper;

use BootstrapUI\View\Helper\FormHelper;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\Utility\Security;
use Cake\View\View;

class FormHelperTest extends TestCase
{
    /**
     * @var \Cake\View\View
     */
    public $View;

    /**
     * @var FormHelper
     */
    protected $Form;

    /**
     * @var array
     */
    public $article;

    /**
     * @var array
     */
    public $dateRegex = [
        'daysRegex' => 'preg:/(?:<option value="0?([\d]+)">\\1<\/option>[\r\n]*)*/',
        'monthsRegex' => 'preg:/(?:<option value="[\d]+">[\w]+<\/option>[\r\n]*)*/',
        'yearsRegex' => 'preg:/(?:<option value="([\d]+)">\\1<\/option>[\r\n]*)*/',
        'hoursRegex' => 'preg:/(?:<option value="0?([\d]+)">\\1<\/option>[\r\n]*)*/',
        'minutesRegex' => 'preg:/(?:<option value="([\d]+)">0?\\1<\/option>[\r\n]*)*/',
        'secondsRegex' => 'preg:/(?:<option value="([\d]+)">0?\\1<\/option>[\r\n]*)*/',
        'meridianRegex' => 'preg:/(?:<option value="(am|pm)">\\1<\/option>[\r\n]*)*/',
    ];

    protected $locale;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Configure::write('Config.language', 'eng');
        Configure::write('App.base', '');
        Configure::write('App.namespace', 'BootstrapUI\Test\TestCase\View\Helper');
        Configure::delete('Asset');

        $request = new ServerRequest([
            'webroot' => '',
            'base' => '',
            'url' => '/articles/add',
            'params' => [
                'controller' => 'articles',
                'action' => 'add',
                'plugin' => null,
            ],
        ]);
        $this->View = new View($request);

        $this->Form = new FormHelper($this->View);

        Router::reload();
        Router::setRequest($request);

        $this->article = [
            'schema' => [
                'id' => ['type' => 'integer'],
                'author_id' => ['type' => 'integer', 'null' => true],
                'title' => ['type' => 'string', 'null' => true],
                'body' => 'text',
                'published' => ['type' => 'boolean', 'length' => 1, 'default' => 0],
                '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
            ],
            'required' => [
                'author_id' => true,
                'title' => true,
            ],
        ];

        Security::setSalt('foo!');
        Router::connect('/:controller', ['action' => 'index']);
        Router::connect('/:controller/:action/*');

        $this->locale = I18n::getLocale();
        I18n::setLocale('eng');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->Form, $this->View);
        TableRegistry::getTableLocator()->clear();
        I18n::setLocale($this->locale);
    }

    public function testBasicTextInput()
    {
        unset($this->article['required']['title']);
        $this->Form->create($this->article);

        $result = $this->Form->control('title');
        $expected = [
            'div' => ['class' => 'form-group text'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'class' => 'form-control',
            ],
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testBasicTextControl()
    {
        unset($this->article['required']['title']);
        $this->Form->create($this->article);

        $result = $this->Form->control('title');
        $expected = [
            'div' => ['class' => 'form-group text'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'class' => 'form-control',
            ],
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testSelectInput()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('foreign_key', [
            'type' => 'select',
            'class' => 'my-class',
        ]);
        $expected = [
            'div' => ['class' => 'form-group select'],
            'label' => ['class' => 'control-label', 'for' => 'foreign-key'],
            'Foreign Key',
            '/label',
            'select' => [
                'name' => 'foreign_key',
                'id' => 'foreign-key',
                'class' => 'my-class form-control',
            ],
            '/select',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testStaticControl()
    {
        $this->View->setRequest($this->View->getRequest()->withAttribute('formTokenData', []));

        unset($this->article['required']['title']);
        $this->article['defaults']['title'] = 'foo <u>bar</u>';
        $this->Form->create($this->article);

        $result = $this->Form->control('title', ['type' => 'staticControl']);
        $expected = [
            'div' => ['class' => 'form-group staticControl'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            'p' => ['class' => 'form-control-static'],
            'foo &lt;u&gt;bar&lt;/u&gt;',
            '/p',
            'input' => [
                'type' => 'hidden',
                'name' => 'title',
                'id' => 'title',
                'value' => 'foo &lt;u&gt;bar&lt;/u&gt;',
            ],
            '/div',
        ];
        $this->assertHtml($expected, $result);
        $this->assertSame(
            ['title' => 'foo <u>bar</u>'],
            $this->Form->getFormProtector()->__debugInfo()['fields']
        );

        $this->Form->fields = [];

        $result = $this->Form->control('title', ['type' => 'staticControl', 'hiddenField' => false, 'escape' => false]);
        $expected = [
            'div' => ['class' => 'form-group staticControl'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            'p' => ['class' => 'form-control-static'],
            'foo',
            'u' => [],
            'bar',
            '/u',
            '/p',
            '/div',
        ];
        $this->assertHtml($expected, $result);
        $this->assertEmpty($this->Form->fields);
    }

    public function testNoLabelTextInput()
    {
        unset($this->article['required']['title']);
        $this->Form->create($this->article);

        $result = $this->Form->control('title', ['label' => false]);
        $expected = [
            'div' => ['class' => 'form-group text'],
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'class' => 'form-control',
            ],
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testLabelledTextInput()
    {
        unset($this->article['required']['title']);
        $this->Form->create($this->article);

        $result = $this->Form->control('title', ['label' => 'Custom Title']);
        $expected = [
            'div' => ['class' => 'form-group text'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Custom Title',
            '/label',
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'class' => 'form-control',
            ],
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testArrayLabelledTextInput()
    {
        unset($this->article['required']['title']);
        $this->Form->create($this->article);

        $result = $this->Form->control('title', ['label' => ['foo' => 'bar', 'text' => 'Custom Title']]);
        $expected = [
            'div' => ['class' => 'form-group text'],
            'label' => ['class' => 'control-label', 'for' => 'title', 'foo' => 'bar'],
            'Custom Title',
            '/label',
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'class' => 'form-control',
            ],
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testBasicPasswordInput()
    {
        $this->article['schema']['password'] = ['type' => 'string'];
        $this->Form->create($this->article);

        $result = $this->Form->control('password');
        $expected = [
            'div' => ['class' => 'form-group password'],
            'label' => ['class' => 'control-label', 'for' => 'password'],
            'Password',
            '/label',
            'input' => [
                'type' => 'password',
                'name' => 'password',
                'id' => 'password',
                'class' => 'form-control',
            ],
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testRequiredTextInput()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('title');
        $expected = [
            'div' => ['class' => 'form-group text required'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' => 'this.setCustomValidity(&#039;&#039;)',
                'id' => 'title',
                'class' => 'form-control',
                'required' => 'required',
            ],
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testErroredTextInput()
    {
        $this->article['errors'] = [
            'title' => ['error message'],
            'published' => ['error'],
        ];
        $this->Form->create($this->article);

        $result = $this->Form->control('title');
        $expected = [
            'div' => ['class' => 'form-group text required has-error'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' => 'this.setCustomValidity(&#039;&#039;)',
                'id' => 'title',
                'class' => 'form-control ',
                'required' => 'required',
            ],
            ['div' => ['class' => 'help-block']],
            'error message',
            '/div',
            '/div',
        ];

        $this->Form->end();

        $this->Form->create($this->article, ['align' => 'horizontal']);

        $result = $this->Form->control('title');
        $expected = [
            'div' => ['class' => 'form-group text required has-error'],
            'label' => ['class' => 'control-label col-md-2', 'for' => 'title'],
            'Title',
            '/label',
            ['div' => ['class' => 'col-md-6']],
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'required' => 'required',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' => 'this.setCustomValidity(&#039;&#039;)',
                'class' => 'error form-control',
            ],
            ['div' => ['class' => 'help-block']],
            'error message',
            '/div',
            '/div',
            '/div',
        ];

        $this->assertHtml($expected, $result);

        $result = $this->Form->control('published');
        $expected = [
            'div' => ['class' => 'form-group has-error'],
            ['div' => ['class' => 'col-md-offset-2 col-md-6']],
            ['div' => ['class' => 'checkbox']],
            'input' => [
                'type' => 'hidden',
                'name' => 'published',
                'class' => 'error',
                'value' => 0,
            ],
            'label' => ['for' => 'published'],
            ['input' => [
                'type' => 'checkbox',
                'name' => 'published',
                'id' => 'published',
                'value' => 1,
                'class' => 'error'
            ]],
            'Published',
            '/label',
            '/div',
            ['div' => ['class' => 'help-block']],
            'error',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testAddOnPrependedInput()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('title', ['prepend' => '@']);
        $expected = [
            'div' => ['class' => 'form-group text required'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            ['div' => ['class' => 'input-group']],
            'span' => ['class' => 'input-group-addon'],
            '@',
            '/span',
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' =>'this.setCustomValidity(&#039;&#039;)',
                'class' => 'form-control',
                'required' => 'required',
            ],
            '/div',
            '/div',
        ];

        $this->assertHtml($expected, $result);

        $result = $this->Form->control('url', ['prepend' => 'http://']);
        $expected = [
            'div' => ['class' => 'form-group text'],
            'label' => ['class' => 'control-label', 'for' => 'url'],
            'Url',
            '/label',
            ['div' => ['class' => 'input-group']],
            'span' => ['class' => 'input-group-addon'],
            'http://',
            '/span',
            'input' => [
                'type' => 'text',
                'name' => 'url',
                'id' => 'url',
                'class' => 'form-control',
            ],
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testAddOnAppendedInput()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('title', ['append' => '@']);
        $expected = [
            'div' => ['class' => 'form-group text required'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            ['div' => ['class' => 'input-group']],
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'required' => 'required',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' =>'this.setCustomValidity(&#039;&#039;)',
                'id' => 'title',
                'class' => 'form-control',
            ],
            'span' => ['class' => 'input-group-addon'],
            '@',
            '/span',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testAddOnAppendedSelect()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('author_id', ['append' => '@']);
        $expected = [
            'div' => ['class' => 'form-group select required'],
            'label' => ['class' => 'control-label', 'for' => 'author-id'],
            'Author',
            '/label',
            ['div' => ['class' => 'input-group']],
            'select' => [
                'name' => 'author_id',
                'id' => 'author-id',
                'required' => 'required',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' =>'this.setCustomValidity(&#039;&#039;)',
                'class' => 'form-control',
            ],
            '/select',
            'span' => ['class' => 'input-group-addon'],
            '@',
            '/span',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testAddOnAppendedTextarea()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('body', [
            'type' => 'textarea',
            'append' => $this->Form->button('GO'),
        ]);
        $expected = [
            'div' => ['class' => 'form-group textarea'],
            'label' => ['class' => 'control-label', 'for' => 'body'],
            'Body',
            '/label',
            ['div' => ['class' => 'input-group']],
            'textarea' => [
                'name' => 'body',
                'id' => 'body',
                'class' => 'form-control',
                'rows' => '5',
            ],
            '/textarea',
            'span' => ['class' => 'input-group-btn'],
            'button' => ['type' => 'submit', 'class' => 'btn btn-default'],
            'GO',
            '/button',
            '/span',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testButtonPrependedInput()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('title', ['prepend' => $this->Form->button('GO')]);
        $expected = [
            'div' => ['class' => 'form-group text required'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            ['div' => ['class' => 'input-group']],
            'span' => ['class' => 'input-group-btn'],
            'button' => ['type' => 'submit', 'class' => 'btn btn-default'],
            'GO',
            '/button',
            '/span',
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' =>'this.setCustomValidity(&#039;&#039;)',
                'class' => 'form-control',
                'required' => 'required',
            ],
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testButtonAppendedInput()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('title', ['append' => $this->Form->button('GO')]);
        $expected = [
            'div' => ['class' => 'form-group text required'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            ['div' => ['class' => 'input-group']],
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' =>'this.setCustomValidity(&#039;&#039;)',
                'class' => 'form-control',
                'required' => 'required',
            ],
            'span' => ['class' => 'input-group-btn'],
            'button' => ['type' => 'submit', 'class' => 'btn btn-default'],
            'GO',
            '/button',
            '/span',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testBasicRadioInput()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('published', [
            'type' => 'radio',
            'options' => ['Yes', 'No'],
        ]);
        $expected = [
            ['div' => ['class' => 'form-group radio']],
            ['label' => true],
            'Published',
            '/label',
            ['input' => [
                'type' => 'hidden',
                'name' => 'published',
                'value' => '',
            ]],
            ['div' => ['class' => 'radio']],
            ['label' => ['for' => 'published-0']],
            ['input' => [
                'type' => 'radio',
                'name' => 'published',
                'value' => 0,
                'id' => 'published-0',
            ]],
            'Yes',
            '/label',
            '/div',
            ['div' => ['class' => 'radio']],
            ['label' => ['for' => 'published-1']],
            ['input' => [
                'type' => 'radio',
                'name' => 'published',
                'value' => 1,
                'id' => 'published-1',
            ]],
            'No',
            '/label',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testInlineRadioInput()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('published', [
            'inline' => true,
            'type' => 'radio',
            'options' => ['Yes', 'No'],
        ]);
        $expected = [
            ['div' => ['class' => 'form-group radio']],
            ['label' => true],
            'Published',
            '/label',
            ['div' => ['class' => 'radio-inline-wrapper']],
            ['input' => [
                'type' => 'hidden',
                'name' => 'published',
                'value' => '',
            ]],
            ['label' => [
                'class' => 'radio-inline',
                'for' => 'published-0',
            ]],
            ['input' => [
                'type' => 'radio',
                'name' => 'published',
                'value' => 0,
                'id' => 'published-0',
            ]],
            'Yes',
            '/label',
            ['label' => [
                'class' => 'radio-inline',
                'for' => 'published-1',
            ]],
            ['input' => [
                'type' => 'radio',
                'name' => 'published',
                'value' => 1,
                'id' => 'published-1',
            ]],
            'No',
            '/label',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testRadioInputHorizontal()
    {
        $this->Form->create($this->article, [
            'align' => [
                'sm' => [
                    'left' => 5,
                    'middle' => 7,
                ],
            ],
        ]);

        $result = $this->Form->control('published', [
            'type' => 'radio',
            'options' => ['Yes', 'No'],
        ]);
        $expected = [
            ['div' => ['class' => 'form-group']],
            ['label' => ['class' => 'control-label col-sm-5']],
            'Published',
            '/label',
            ['div' => ['class' => 'col-sm-7']],
            'input' => [
                'type' => 'hidden',
                'name' => 'published',
                'value' => '',
            ],
            ['div' => ['class' => 'radio']],
            ['label' => ['for' => 'published-0']],
            ['input' => [
                'type' => 'radio',
                'name' => 'published',
                'value' => 0,
                'id' => 'published-0',
            ]],
            'Yes',
            '/label',
            '/div',
            ['div' => ['class' => 'radio']],
            ['label' => ['for' => 'published-1']],
            ['input' => [
                'type' => 'radio',
                'name' => 'published',
                'value' => 1,
                'id' => 'published-1',
            ]],
            'No',
            '/label',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testInlineRadioInputHorizontal()
    {
        $this->Form->create($this->article, [
            'align' => [
                'sm' => [
                    'left' => 5,
                    'middle' => 7,
                ],
            ],
        ]);

        $result = $this->Form->control('published', [
            'inline' => true,
            'type' => 'radio',
            'options' => ['Yes', 'No'],
        ]);
        $expected = [
            ['div' => ['class' => 'form-group']],
            ['label' => ['class' => 'control-label col-sm-5']],
            'Published',
            '/label',
            ['div' => ['class' => 'col-sm-7']],
            'input' => [
                'type' => 'hidden',
                'name' => 'published',
                'value' => '',
            ],
            ['label' => [
                'class' => 'radio-inline',
                'for' => 'published-0',
            ]],
            ['input' => [
                'type' => 'radio',
                'name' => 'published',
                'value' => 0,
                'id' => 'published-0',
            ]],
            'Yes',
            '/label',
            ['label' => [
                'class' => 'radio-inline',
                'for' => 'published-1',
            ]],
            ['input' => [
                'type' => 'radio',
                'name' => 'published',
                'value' => 1,
                'id' => 'published-1',
            ]],
            'No',
            '/label',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * https://github.com/FriendsOfCake/bootstrap-ui/pull/113
     *
     * @return void
     */
    public function testRadioInputCustomTemplate()
    {
        $templates = [
            'radioNestingLabel' => '<div class="radio custom-class">{{hidden}}<label{{attrs}}>{{input}}{{text}}</label></div>',
        ];

        $this->Form->create($this->article);

        $result = $this->Form->control('published', [
            'type' => 'radio',
            'options' => ['Yes', 'No'],
            'templates' => $templates,
        ]);
        $expected = [
            ['div' => ['class' => 'form-group radio']],
            ['label' => true],
            'Published',
            '/label',
            ['input' => [
                'type' => 'hidden',
                'name' => 'published',
                'value' => '',
            ]],
            ['div' => ['class' => 'radio custom-class']],
            ['label' => ['for' => 'published-0']],
            ['input' => [
                'type' => 'radio',
                'name' => 'published',
                'value' => 0,
                'id' => 'published-0',
            ]],
            'Yes',
            '/label',
            '/div',
            ['div' => ['class' => 'radio custom-class']],
            ['label' => ['for' => 'published-1']],
            ['input' => [
                'type' => 'radio',
                'name' => 'published',
                'value' => 1,
                'id' => 'published-1',
            ]],
            'No',
            '/label',
            '/div',
            '/div',
        ];

        $this->assertHtml($expected, $result);
    }

    public function testBasicCheckboxInput()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('published');
        $expected = [
            'div' => ['class' => 'checkbox'],
            'input' => [
                'type' => 'hidden',
                'name' => 'published',
                'value' => 0,
            ],
            'label' => ['for' => 'published'],
            ['input' => [
                'type' => 'checkbox',
                'name' => 'published',
                'id' => 'published',
                'value' => 1,
            ]],
            'Published',
            '/label',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testInlineCheckboxInput()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('published', ['inline' => true]);
        $expected = [
            'input' => [
                'type' => 'hidden',
                'name' => 'published',
                'value' => 0,
            ],
            'label' => ['class' => 'checkbox-inline', 'for' => 'published'],
            ['input' => [
                'type' => 'checkbox',
                'name' => 'published',
                'id' => 'published',
                'value' => 1,
            ]],
            'Published',
            '/label',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testBasicFormCreate()
    {
        $result = $this->Form->create($this->article);
        $expected = [
            'form' => [
                'method' => 'post',
                'accept-charset' => 'utf-8',
                'role' => 'form',
                'action' => '/articles/add',
            ],
        ];
        $this->assertHtml($expected, $result);
    }

    public function testFormCreateWithTemplatesFile()
    {
        unset($this->article['required']['title']);
        $this->Form->create($this->article, ['templates' => 'custom_templates']);

        $result = $this->Form->control('title');
        $expected = [
            'div' => ['class' => 'custom-container form-group'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'class' => 'form-control',
            ],
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testInlineFormCreate()
    {
        $result = $this->Form->create($this->article, ['class' => 'form-inline']);
        $expected = [
            'form' => [
                'method' => 'post',
                'accept-charset' => 'utf-8',
                'role' => 'form',
                'action' => '/articles/add',
                'class' => 'form-inline',
            ],
        ];

        $this->assertHtml($expected, $result);
    }

    public function testHorizontalFormCreate()
    {
        $result = $this->Form->create($this->article, ['align' => 'horizontal']);
        $expected = [
            'form' => [
                'method' => 'post',
                'accept-charset' => 'utf-8',
                'role' => 'form',
                'action' => '/articles/add',
                'class' => 'form-horizontal',
            ],
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Form->control('title');
        $expected = [
            'div' => ['class' => 'form-group text required'],
            'label' => [
                'class' => 'control-label col-md-2',
                'for' => 'title',
            ],
            'Title',
            '/label',
            ['div' => ['class' => 'col-md-6']],
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' => 'this.setCustomValidity(&#039;&#039;)',
                'class' => 'form-control',
                'required' => 'required',
            ],
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Form->control('published');
        $expected = [
            'div' => ['class' => 'form-group'],
            ['div' => ['class' => 'col-md-offset-2 col-md-6']],
            ['div' => ['class' => 'checkbox']],
            'input' => [
                'type' => 'hidden',
                'name' => 'published',
                'value' => 0,
            ],
            'label' => ['for' => 'published'],
            ['input' => [
                'type' => 'checkbox',
                'name' => 'published',
                'id' => 'published',
                'value' => 1,
            ]],
            'Published',
            '/label',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testCustomGrid()
    {
        $this->Form->create($this->article, [
            'align' => [
                'left' => 3,
                'middle' => 5,
                'right' => 4,
            ],
        ]);

        $result = $this->Form->control('title');
        $expected = [
            'div' => ['class' => 'form-group text required'],
            'label' => [
                'class' => 'control-label col-md-3',
                'for' => 'title',
            ],
            'Title',
            '/label',
            ['div' => ['class' => 'col-md-5']],
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' => 'this.setCustomValidity(&#039;&#039;)',
                'class' => 'form-control',
                'required' => 'required',
            ],
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testHorizontalFormCreateFromConfig()
    {
        $this->Form->setConfig([
            'align' => 'horizontal',
            'templateSet' => [
                'horizontal' => [
                    'checkboxFormGroup' => '<div class="%s"><div class="my-checkbox">{{label}}</div>{{error}}{{help}}</div>',
                ],
            ],
        ]);
        $result = $this->Form->create($this->article);
        $expected = [
            'form' => [
                'method' => 'post',
                'accept-charset' => 'utf-8',
                'role' => 'form',
                'action' => '/articles/add',
                'class' => 'form-horizontal',
            ],
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Form->control('title');
        $expected = [
            'div' => ['class' => 'form-group text required'],
            'label' => [
                'class' => 'control-label col-md-2',
                'for' => 'title',
            ],
            'Title',
            '/label',
            ['div' => ['class' => 'col-md-6']],
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' => 'this.setCustomValidity(&#039;&#039;)',
                'class' => 'form-control',
                'required' => 'required',
            ],
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Form->control('published');
        $expected = [
            'div' => ['class' => 'form-group'],
            ['div' => ['class' => 'col-md-offset-2 col-md-6']],
            ['div' => ['class' => 'my-checkbox']],
            'input' => [
                'type' => 'hidden',
                'name' => 'published',
                'value' => 0,
            ],
            'label' => ['for' => 'published'],
            ['input' => [
                'type' => 'checkbox',
                'name' => 'published',
                'id' => 'published',
                'value' => 1,
            ]],
            'Published',
            '/label',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testBasicButton()
    {
        $result = $this->Form->button('Submit');
        $expected = [
            'button' => ['class' => 'btn btn-default', 'type' => 'submit'],
            'Submit',
            '/button',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testBasicFormSubmit()
    {
        $result = $this->Form->submit('Submit');
        $expected = [
            'div' => ['class' => 'submit'],
            'input' => [
                'type' => 'submit',
                'value' => 'Submit',
                'class' => 'btn btn-default',
            ],
        ];
        $this->assertHtml($expected, $result);
    }

    public function testStyledFormSubmit()
    {
        $result = $this->Form->submit('Submit', ['class' => 'btn btn-block']);
        $expected = [
            'div' => ['class' => 'submit'],
            'input' => [
                'type' => 'submit',
                'value' => 'Submit',
                'class' => 'btn btn-block btn-default',
            ],
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Form->submit('Submit', ['class' => ['btn', 'btn-block']]);
        $expected = [
            'div' => ['class' => 'submit'],
            'input' => [
                'type' => 'submit',
                'value' => 'Submit',
                'class' => 'btn btn-block btn-default',
            ],
        ];
        $this->assertHtml($expected, $result);
    }

    public function testHorizontalFormSubmit()
    {
        $this->Form->create($this->article, ['align' => 'horizontal']);

        $result = $this->Form->submit('Submit');
        $expected = [
            'div' => ['class' => 'form-group'],
            ['div' => ['class' => 'col-md-offset-2 col-md-6']],
            'input' => [
                'type' => 'submit',
                'value' => 'Submit',
                'class' => 'btn btn-default',
            ],
        ];

        $this->assertHtml($expected, $result);
    }

    public function testStyledButton()
    {
        $result = $this->Form->button('Submit', ['class' => 'success']);
        $expected = [
            'button' => ['class' => 'btn-success btn', 'type' => 'submit'],
            'Submit',
            '/button',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testPrimaryStyledButton()
    {
        $result = $this->Form->button('Submit', ['class' => 'primary']);
        $expected = [
            'button' => ['class' => 'btn-primary btn', 'type' => 'submit'],
            'Submit',
            '/button',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testSelectMultipleCheckbox()
    {
        $options = [
            'Value 1' => 'Label 1',
            'Value 2' => 'Label 2',
        ];
        $result = $this->Form->select('field', $options, ['multiple' => 'checkbox']);

        $expected = [
            'input' => [
                'type' => 'hidden',
                'name' => 'field',
                'value' => '',
            ],
            ['div' => ['class' => 'checkbox']],
            ['label' => ['for' => 'field-value-1']],
            [
                'input' => [
                    'type' => 'checkbox',
                    'name' => 'field[]',
                    'id' => 'field-value-1',
                    'value' => 'Value 1',
                ],
            ],
            'Label 1',
            '/label',
            '/div',
            ['div' => ['class' => 'checkbox']],
            ['label' => ['for' => 'field-value-2']],
            [
                'input' => [
                    'type' => 'checkbox',
                    'name' => 'field[]',
                    'id' => 'field-value-2',
                    'value' => 'Value 2',
                ],
            ],
            'Label 2',
            '/label',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testMultipleCheckboxInput()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('users', [
            'multiple' => 'checkbox',
            'options' => [
                1 => 'User 1',
                2 => 'User 2',
            ],
        ]);
        $expected = [
            ['div' => ['class' => 'form-group multicheckbox']],
            ['label' => []],
            'Users',
            '/label',
            'input' => [
                'type' => 'hidden',
                'name' => 'users',
                'value' => '',
            ],
            ['div' => ['class' => 'checkbox']],
            ['label' => ['for' => 'users-1']],
            [
                'input' => [
                    'type' => 'checkbox',
                    'name' => 'users[]',
                    'id' => 'users-1',
                    'value' => 1,
                ],
            ],
            'User 1',
            '/label',
            '/div',
            ['div' => ['class' => 'checkbox']],
            ['label' => ['for' => 'users-2']],
            [
                'input' => [
                    'type' => 'checkbox',
                    'name' => 'users[]',
                    'id' => 'users-2',
                    'value' => 2,
                ],
            ],
            'User 2',
            '/label',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testHelpText()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('title', ['help' => 'help text']);
        $expected = [
            'div' => ['class' => 'form-group text required'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' => 'this.setCustomValidity(&#039;&#039;)',
                'class' => 'form-control',
                'required' => 'required',
            ],
            ['div' => ['class' => 'help-block']],
            'help text',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Form->control('published', ['help' => 'help text']);
        $expected = [
            'div' => ['class' => 'checkbox'],
            'input' => [
                'type' => 'hidden',
                'name' => 'published',
                'value' => 0,
            ],
            'label' => ['for' => 'published'],
            ['input' => [
                'type' => 'checkbox',
                'name' => 'published',
                'id' => 'published',
                'value' => 1,
            ]],
            'Published',
            '/label',
            ['div' => ['class' => 'help-block']],
            'help text',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        $this->article['errors'] = [
            'title' => ['error message'],
        ];
        $this->Form->create($this->article);

        $result = $this->Form->control('title', ['help' => 'help text']);
        $expected = [
            'div' => ['class' => 'form-group text required has-error'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' => 'this.setCustomValidity(&#039;&#039;)',
                'class' => 'error form-control',
                'required' => 'required',
            ],
            ['div' => ['class' => 'help-block']],
            'error message',
            '/div',
            ['div' => ['class' => 'help-block']],
            'help text',
            '/div',
            '/div',
        ];

        $this->assertHtml($expected, $result);

        $result = $this->Form->control('title', [
            'help' => 'help text',
            'templates' => ['help' => '<div class="custom-help-block">{{content}}</div>'],
        ]);
        $expected = [
            'div' => ['class' => 'form-group text required has-error'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            '/label',
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' => 'this.setCustomValidity(&#039;&#039;)',
                'class' => 'error form-control',
                'required' => 'required',
            ],
            ['div' => ['class' => 'help-block']],
            'error message',
            '/div',
            ['div' => ['class' => 'custom-help-block']],
            'help text',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testHelpTextHorizontal()
    {
        $this->Form->create($this->article, ['align' => 'horizontal']);

        $result = $this->Form->control('title', ['help' => 'help text']);
        $expected = [
            'div' => ['class' => 'form-group text required'],
            'label' => ['class' => 'control-label col-md-2', 'for' => 'title'],
            'Title',
            '/label',
            ['div' => ['class' => 'col-md-6']],
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' => 'this.setCustomValidity(&#039;&#039;)',
                'class' => 'form-control',
                'required' => 'required',
            ],
            ['div' => ['class' => 'help-block']],
            'help text',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Form->control('published', ['help' => 'help text']);
        $expected = [
            'div' => ['class' => 'form-group'],
            ['div' => ['class' => 'col-md-offset-2 col-md-6']],
            ['div' => ['class' => 'checkbox']],
            'input' => [
                'type' => 'hidden',
                'name' => 'published',
                'value' => 0,
            ],
            'label' => ['for' => 'published'],
            ['input' => [
                'type' => 'checkbox',
                'name' => 'published',
                'id' => 'published',
                'value' => 1,
            ]],
            'Published',
            '/label',
            '/div',
            ['div' => ['class' => 'help-block']],
            'help text',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        $this->article['errors'] = [
            'title' => ['error message'],
        ];
        $this->Form->create($this->article, ['align' => 'horizontal']);

        $result = $this->Form->control('title', ['help' => 'help text']);
        $expected = [
            'div' => ['class' => 'form-group text required has-error'],
            'label' => ['class' => 'control-label col-md-2', 'for' => 'title'],
            'Title',
            '/label',
            ['div' => ['class' => 'col-md-6']],
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' => 'this.setCustomValidity(&#039;&#039;)',
                'class' => 'error form-control',
                'required' => 'required',
            ],
            ['div' => ['class' => 'help-block']],
            'error message',
            '/div',
            ['div' => ['class' => 'help-block']],
            'help text',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * @return void
     */
    public function testTooltip()
    {
        $this->Form->create($this->article);

        $result = $this->Form->control('title', ['tooltip' => 'Some important additional notes.']);
        $expected = [
            'div' => ['class' => 'form-group text required'],
            'label' => ['class' => 'control-label', 'for' => 'title'],
            'Title',
            'span' => ['data-toggle' => 'tooltip', 'title' => 'Some important additional notes.', 'class' => 'glyphicon glyphicon-info-sign'],
            '/span',
            '/label',
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' => 'this.setCustomValidity(&#039;&#039;)',
                'class' => 'form-control',
                'required' => 'required',
            ],
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * @return void
     */
    public function testTooltipHorizontal()
    {
        $this->Form->create($this->article, ['align' => 'horizontal']);

        $result = $this->Form->control('title', ['tooltip' => 'Some important additional notes.']);
        $expected = [
            'div' => ['class' => 'form-group text required'],
            'label' => ['class' => 'control-label col-md-2', 'for' => 'title'],
            'Title ',
            'span' => ['data-toggle' => 'tooltip', 'title' => 'Some important additional notes.', 'class' => 'glyphicon glyphicon-info-sign'],
            '/span',
            '/label',
            ['div' => ['class' => 'col-md-6']],
            'input' => [
                'type' => 'text',
                'name' => 'title',
                'id' => 'title',
                'data-validity-message' => 'This field cannot be left empty',
                'oninvalid' => 'this.setCustomValidity(&#039;&#039;); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)',
                'oninput' => 'this.setCustomValidity(&#039;&#039;)',
                'class' => 'form-control',
                'required' => 'required',
            ],
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * Test that "form-control" class is added when using methods for specific input.
     *
     * @return void
     */
    public function testFormControlClassInjection()
    {
        $result = $this->Form->text('foo');
        $this->assertContains('class="form-control"', $result);

        $result = $this->Form->text('foo', ['class' => 'custom']);
        $this->assertContains('class="custom form-control"', $result);

        $result = $this->Form->select('foo');
        $this->assertContains('class="form-control"', $result);

        $result = $this->Form->textarea('foo');
        $this->assertContains('class="form-control"', $result);

        $result = $this->Form->dateTime('foo');

        $this->assertContains('<select name="foo[year]" class="form-control"', $result);
        $this->assertContains('<select name="foo[month]" class="form-control"', $result);
        $this->assertContains('<select name="foo[day]" class="form-control"', $result);
        $this->assertContains('<select name="foo[hour]" class="form-control"', $result);
        $this->assertContains('<select name="foo[minute]" class="form-control"', $result);
        $this->assertContains('<select name="foo[second]" class="form-control"', $result);

        $result = $this->Form->file('foo');
        $this->assertNotContains('"form-control"', $result);

        $result = $this->Form->checkbox('foo');
        $this->assertNotContains('"form-control"', $result);

        $result = $this->Form->radio('foo', ['1' => 'Opt 1', '2' => 'Opt 2']);
        $this->assertNotContains('"form-control"', $result);
    }
}
