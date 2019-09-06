<?php

namespace Tests\Service;

use Ae\WhiteLabelBundle\Exception\OperatorNotValidException;
use Ae\WhiteLabelBundle\Exception\WebsiteNotValidException;
use Ae\WhiteLabelBundle\Model\Website;
use Ae\WhiteLabelBundle\Service\WhiteLabel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class WhiteLabelTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    private $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private $logger;

    protected function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * @test
     */
    public function enabled_with_invalid_operator()
    {
        $this->expectException(
            OperatorNotValidException::class
        );

        $this->container
            ->expects($this->once())
            ->method('getParameter')
            ->with('ae_white_label.websites')
            ->willReturn(
                $this->getWebSites()
            );

        $service = new WhiteLabel(
            $this->container,
            $this->logger
        );

        $service->enabled(
            [
                'AdEspresso'
            ],
            'fake-operator'
        );
    }

    /**
     * @test
     */
    public function enabled_default()
    {
        $this->container
            ->method('getParameter')
            ->withConsecutive(
                [
                    'ae_white_label.websites',
                ],
                [
                    'ae_white_label.default',
                ],
                [
                    'ae_white_label.website.AdEspresso.method'
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getWebSites(),
                'AdEspresso',
                'byHost'
            );

        /** @var WhiteLabel|\PHPUnit_Framework_MockObject_MockObject $service */
        $service = $this->getMockBuilder(WhiteLabel::class)
            ->setConstructorArgs(
                [
                    $this->container,
                    $this->logger
                ]
            )
            ->setMethods(
                [
                    'byHost'
                ]
            )
            ->getMock();

        $service
            ->expects($this->once())
            ->method('byHost')
            ->willReturn(true);

        $this->assertTrue(
            $service->enabled(
                [],
                'or'
            )
        );
    }

    public function enabledDataProvider()
    {
        return [
            [
                'method' => 'byHost',
                'methodResult' => true,
                'operator' => 'or',
                'expected' => true
            ],
            [
                'method' => 'byHost',
                'methodResult' => true,
                'operator' => null,
                'expected' => true
            ],
            [
                'method' => 'byHost',
                'methodResult' => false,
                'operator' => 'not',
                'expected' => true
            ],
            [
                'method' => 'byHost',
                'methodResult' => false,
                'operator' => 'and',
                'expected' => false
            ],
        ];
    }

    /**
     * @param $method
     * @param $methodResult
     * @param $operator
     * @param $expected
     *
     * @test
     * @dataProvider enabledDataProvider
     */
    public function enabled(
        $method,
        $methodResult,
        $operator,
        $expected
    )
    {
        $this->container
            ->method('getParameter')
            ->withConsecutive(
                [
                    'ae_white_label.websites',
                ],
                [
                    'ae_white_label.website.AdEspresso.method'
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getWebSites(),
                $method
            );

        /** @var WhiteLabel|\PHPUnit_Framework_MockObject_MockObject $service */
        $service = $this->getMockBuilder(WhiteLabel::class)
            ->setConstructorArgs(
                [
                    $this->container,
                    $this->logger
                ]
            )
            ->setMethods(
                [
                    $method
                ]
            )
            ->getMock();

        $service
            ->expects($this->once())
            ->method($method)
            ->willReturn($methodResult);

        $this->assertEquals(
            $expected,
            $service->enabled(
                [
                    'AdEspresso'
                ],
                $operator
            )
        );
    }

    /**
     * @test
     */
    public function get_website()
    {
        $this->container
            ->method('getParameter')
            ->withConsecutive(
                [
                    'ae_white_label.websites',
                ],
                [
                    'ae_white_label.website.AdEspresso.model'
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getWebSites(),
                $this->getDefaultModel()
            );

        /** @var WhiteLabel|\PHPUnit_Framework_MockObject_MockObject $service */
        $service = $this->getMockBuilder(WhiteLabel::class)
            ->setConstructorArgs(
                [
                    $this->container,
                    $this->logger
                ]
            )
            ->setMethods(
                [
                    'enabled'
                ]
            )
            ->getMock();

        $service
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(true);

        $webSite = $service->getWebsite();

        $this->assertInstanceOf(Website::class, $webSite);;

        $this->assertEquals('byHost', $webSite->getMethod());
        $this->assertEquals('AdEspresso', $webSite->getLabel());
        $this->assertEquals('app.adespresso.com', $webSite->getHost());
        $this->assertEquals(
            [
                'key' => 'profile.origin',
                'value' => 'AdEspresso'
            ],
            $webSite->getUserParam()
        );
        $this->assertEquals(
            [
                'key' => 'value',
            ],
            $webSite->getCustomParams()
        );
        $this->assertEquals('value', $webSite->getCustomParamByName('key'));
    }

    /**
     * @test
     */
    public function get_invalid_website_by_name()
    {
        $this->expectException(WebsiteNotValidException::class);

        $this->container
            ->method('getParameter')
            ->withConsecutive(
                [
                    'ae_white_label.websites',
                ],
                [
                    'ae_white_label.website.AdEspresso.model'
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getWebSites(),
                [
                    'AdEspresso' => $this->getDefaultModel()
                ]
            );

        $service = new WhiteLabel(
            $this->container,
            $this->logger
        );

        $service->getWebsiteByName('fake');
    }

    /**
     * @test
     */
    public function get_website_by_name()
    {
        $this->expectException(WebsiteNotValidException::class);

        $this->container
            ->method('getParameter')
            ->withConsecutive(
                [
                    'ae_white_label.websites',
                ],
                [
                    'ae_white_label.website.AdEspresso.model'
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getWebSites(),
                [
                    'AdEspresso' => $this->getDefaultModel()
                ]
            );

        $service = new WhiteLabel(
            $this->container,
            $this->logger
        );

        $website = $service->getWebsiteByName('AdEspresso');

        $this->assertInstanceOf(Website::class, $website);
    }

    public function byHostDataProvider()
    {
        return [
            [
                'model_host' => 'app.adespresso.com',
                'request_host' => 'app.adespresso.com',
                'expected' => true,
            ],
            [
                'model_host' => 'app.adespresso.com',
                'request_host' => 'facebook.com',
                'expected' => false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider byHostDataProvider
     *
     * @param string $modelHost,
     * @param string $requestHost,
     * @param bool   $expected
     */
    public function by_host(
        $modelHost,
        $requestHost,
        $expected
    )
    {
        $this->container
            ->expects($this->once())
            ->method('getParameter')
            ->with('ae_white_label.website.AdEspresso.host')
            ->willReturn($modelHost);

        $router = $this->createMock(Router::class);

        $context = $this->createMock(RequestContext::class);
        $context
            ->expects($this->once())
            ->method('getHost')
            ->willReturn($requestHost);

        $router
            ->expects($this->once())
            ->method('getContext')
            ->willReturn($context);

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('router')
            ->willReturn($router);

        $service = new WhiteLabel(
            $this->container,
            $this->logger
        );

        $this->assertEquals(
            $expected,
            $service->byHost('AdEspresso')
        );
    }

    /**
     * @test
     */
    public function get_white_label_url_by_origin()
    {
        /** @var WhiteLabel|\PHPUnit_Framework_MockObject_MockObject $service */
        $service = $this->getMockBuilder(WhiteLabel::class)
            ->setConstructorArgs(
                [
                    $this->container,
                    $this->logger
                ]
            )
            ->setMethods(
                [
                    'getWebsiteByName'
                ]
            )
            ->getMock();

        $webSite = new Website('AdEspresso', $this->getDefaultModel());

        $service
            ->expects($this->any())
            ->method('getWebsiteByName')
            ->willReturn($webSite);

        $this->assertEquals(
            'https://app.adespresso.com',
            $service->getWhitelabelUrlByOrigin('AdEspresso', 'https://adespresso.com')
        );

        $this->assertEquals(
            'http://app.adespresso.com',
            $service->getWhitelabelUrlByOrigin('AdEspresso')
        );

        $this->container
            ->expects($this->once())
            ->method('hasParameter')
            ->with('router.request_context.scheme')
            ->willReturn(true);

        $this->container
            ->expects($this->once())
            ->method('getParameter')
            ->with('router.request_context.scheme')
            ->willReturn('https');

        $this->assertEquals(
            'https://app.adespresso.com',
            $service->getWhitelabelUrlByOrigin('AdEspresso')
        );
    }

    /**
     * @test
     */
    public function by_user_param()
    {
        $this->container
            ->method('getParameter')
            ->withConsecutive(
                [
                    'ae_white_label.website.AdEspresso.user_param.key',
                ],
                [
                    'ae_white_label.website.AdEspresso.user_param.value'
                ],
                [
                    'ae_white_label.website.AdEspresso.user_param.key',
                ],
                [
                    'ae_white_label.website.AdEspresso.user_param.value'
                ]
            )
            ->willReturnOnConsecutiveCalls(
                'profile.origin',
                'AdEspresso',
                'profile.origin',
                'AdEspresso'
            );

        $tokenStorage = $this->createMock(TokenStorage::class);

        $token = $this->createMock(TokenInterface::class);

        $user = new User();

        $token
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $tokenStorage
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->container
            ->expects($this->any())
            ->method('get')
            ->with('security.token_storage')
            ->willReturn($tokenStorage);

        $service = new WhiteLabel(
            $this->container,
            $this->logger
        );

        $this->assertFalse($service->byUserParam('AdEspresso'));

        $user
            ->getProfile()
            ->setOrigin('AdEspresso');

        $this->assertTrue($service->byUserParam('AdEspresso'));
    }

    /**
     * @return array
     */
    private function getWebSites()
    {
        return [
            'AdEspresso' => [
                // Configs
            ]
        ];
    }

    /**
     * @return array
     */
    private function getDefaultModel()
    {
        return [
            'method' => 'byHost',
            'label' => 'AdEspresso',
            'host' => 'app.adespresso.com',
            'user_param' => [
                'key' => 'profile.origin',
                'value' => 'AdEspresso',
            ],
            'custom_params' => [
                'key' => 'value'
            ]
        ];
    }
}

class Profile
{
    /**
     * @var string
     */
    private $origin;

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param mixed $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }
}

class User implements UserInterface
{
    /**
     * @var Profile
     */
    private $profile;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->profile = new Profile();
    }

    /**
     * @return mixed
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param mixed $profile
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    public function getRoles()
    {
        return [];
    }

    public function getPassword()
    {
        return '';
    }

    public function getSalt()
    {
        return '';
    }

    public function getUsername()
    {
        return 'fake';
    }

    public function eraseCredentials()
    {
        //
    }
}
