<?php

namespace Drupal\Tests\simplesamlphp_auth\Unit\Service;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\simplesamlphp_auth\Service\SimplesamlphpAuthManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Extension\ModuleHandlerInterface;
use SimpleSAML\Auth\Simple;
use SimpleSAML\Configuration;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

/**
 * SimplesamlphpAuthManager unit tests.
 *
 * @ingroup simplesamlphp_auth
 *
 * @group simplesamlphp_auth
 *
 * @coversDefaultClass \Drupal\simplesamlphp_auth\Service\SimplesamlphpAuthManager
 */
class SimplesamlphpAuthManagerTest extends UnitTestCase {

  /**
   * A mocked config factory instance.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $configFactory;

  /**
   * A mocked SimpleSAML configuration instance.
   *
   * @var \SimpleSAML\Configuration|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $simplesamlConfig;

  /**
   * A mocked SimpleSAML instance.
   *
   * @var \SimpleSAML\Auth\Simple|\PHPUnit_Framework_MockObject_MockObject
   */
  public $instance;

  /**
   * A mocked current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * A mocked AdminContext.
   *
   * @var \Drupal\Core\Routing\AdminContext|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $adminContext;

  /**
   * A mocked ModuleHandlerInterface.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * A mocked RequestStack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $requestStack;

  /**
   * A mocked messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Set up default test configuration Mock object.
    $this->configFactory = $this->getConfigFactoryStub([
      'simplesamlphp_auth.settings' => [
        'auth_source' => 'default-sp',
        'register_users' => TRUE,
        'activate' => 1,
        'user_name' => 'name',
        'mail_attr' => 'mail',
        'unique_id' => 'uid',
      ],
    ]);

    $this->instance = $this->getMockBuilder(Simple::class)
      ->setMethods([
        'isAuthenticated',
        'requireAuth',
        'getAttributes',
        'logout',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $this->currentUser = $this->getMockBuilder(AccountInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->adminContext = $this->getMockBuilder(AdminContext::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->moduleHandler = $this->getMockBuilder(ModuleHandlerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->moduleHandler->expects($this->any())
      ->method('getImplementations')
      ->with($this->equalTo('simplesamlphp_auth_allow_login'))
      ->will($this->returnValue([]));

    $this->requestStack = $this->getMockBuilder(RequestStack::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->messenger = $this->getMockBuilder(MessengerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->simplesamlConfig = $this->getMockBuilder(Configuration::class)
      ->setMethods(['getValue'])
      ->disableOriginalConstructor()
      ->getMock();

    $container = new ContainerBuilder();
    $request = $this->getMockBuilder(Request::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->requestStack->expects($this->any())
      ->method('getCurrentRequest')
      ->will($this->returnValue($request));
    $container->set('request_stack', $this->requestStack);
    \Drupal::setContainer($container);

  }

  /**
   * Get a new manager instance using mocked constructor arguments.
   *
   * @return \Drupal\simplesamlphp_auth\Service\SimplesamlphpAuthManager
   *   A mocked manager.
   */
  protected function getManagerInContext() {
    return new SimplesamlphpAuthManager(
      $this->configFactory,
      $this->currentUser,
      $this->adminContext,
      $this->moduleHandler,
      $this->requestStack,
      $this->messenger,
      $this->instance,
      $this->simplesamlConfig
    );
  }

  /**
   * Tests isActivated() method.
   *
   * @covers ::__construct
   * @covers ::isActivated
   */
  public function testIsActivated() {
    $simplesaml = $this->getManagerInContext();
    $return = $simplesaml->isActivated();
    $this->assertTrue($return);
  }

  /**
   * Tests isAuthenticated() method.
   *
   * @covers ::__construct
   * @covers ::isAuthenticated
   */
  public function testIsAuthenticated() {
    // Set expectations for instance.
    $this->instance->expects($this->once())
      ->method('isAuthenticated')
      ->will($this->returnValue(TRUE));

    // Test isAuthenticated() method.
    $simplesaml = $this->getManagerInContext();
    $return = $simplesaml->isAuthenticated();
    $this->assertTrue($return);
  }

  /**
   * Tests externalAuthenticate() method.
   *
   * @covers ::__construct
   * @covers ::externalAuthenticate
   */
  public function testExternalAuthenticate() {
    // Set expectations for instance.
    $this->instance->expects($this->once())
      ->method('requireAuth');

    // Test externalAuthenticate() method.
    $simplesaml = $this->getManagerInContext();
    $simplesaml->externalAuthenticate();
  }

  /**
   * Tests getStorage() method.
   *
   * @covers ::__construct
   * @covers ::getStorage
   */
  public function testGetStorage() {
    // Set expectations for config.
    $this->simplesamlConfig->expects($this->any())
      ->method('getValue')
      ->with($this->equalTo('store.type'))
      ->will($this->returnValue('sql'));

    // Test getStorage() method.
    $simplesaml = $this->getManagerInContext();
    $return = $simplesaml->getStorage();
    $this->assertEquals('sql', $return);
  }

  /**
   * Tests attributes assignment logic.
   *
   * @covers ::__construct
   * @covers ::getAttributes
   * @covers ::getAttribute
   * @covers ::getAuthname
   * @covers ::getDefaultName
   * @covers ::getDefaultEmail
   */
  public function testAttributes() {
    $data = [
      'uid' => ['ext_user_123'],
      'name' => ['External User'],
      'mail' => ['ext_user_123@example.com'],
      'roles' => [['employee', 'webmaster']],
    ];

    // Set expectations for instance.
    $this->instance->expects($this->any())
      ->method('getAttributes')
      ->will($this->returnValue($data));

    // Test attribute methods.
    $simplesaml = $this->getManagerInContext();
    $this->assertEquals('ext_user_123', $simplesaml->getAuthname());
    $this->assertEquals('External User', $simplesaml->getDefaultName());
    $this->assertEquals('ext_user_123@example.com', $simplesaml->getDefaultEmail());
    $this->assertEquals(['employee', 'webmaster'], $simplesaml->getAttribute('roles'));
  }

  /**
   * Tests attribute assignment logic throwing exceptions.
   *
   * @covers ::__construct
   * @covers ::getAttribute
   *
   * @expectedException \Drupal\simplesamlphp_auth\Exception\SimplesamlphpAttributeException
   *
   * @expectedExceptionMessage Error in simplesamlphp_auth.module: no valid "name" attribute set.
   */
  public function testAttributesException() {
    // Set expectations for instance.
    $this->instance->expects($this->any())
      ->method('getAttributes')
      ->will($this->returnValue(['uid' => ['ext_user_123']]));

    $simplesaml = $this->getManagerInContext();
    $simplesaml->getAttribute('name');
  }

  /**
   * Tests allowUserByAttribute() method.
   *
   * @covers ::__construct
   * @covers ::allowUserByAttribute
   */
  public function testAllowUserByAttribute() {
    $data = [
      'uid' => ['ext_user_123'],
      'name' => ['External User'],
      'mail' => ['ext_user_123@example.com'],
      'roles' => [['employee', 'webmaster']],
    ];

    // Set expectations for instance.
    $this->instance->expects($this->any())
      ->method('getAttributes')
      ->will($this->returnValue($data));

    // Test allowUserByAttribute method.
    $simplesaml = $this->getManagerInContext();
    $this->assertTrue($simplesaml->allowUserByAttribute());
  }

  /**
   * Tests logout() method.
   *
   * @covers ::__construct
   * @covers ::logout
   */
  public function testLogout() {
    $redirect_path = '<front>';

    // Set expectations for instance.
    $this->instance->expects($this->once())
      ->method('logout')
      ->with($this->equalTo($redirect_path));

    // Test logout() method.
    $simplesaml = $this->getManagerInContext();
    $simplesaml->logout($redirect_path);
  }

}
