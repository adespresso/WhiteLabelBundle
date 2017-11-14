<?php

namespace Tests\DependencyInjection;

use Ae\WhiteLabelBundle\DependencyInjection\AeWhiteLabelExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class AeWhiteLabelExtensionTest extends AbstractExtensionTestCase
{

    /**
     * @test
     */
    public function after_loading_the_correct_parameter_has_been_set()
    {
        $default = [
            'default_website' => 'whiteLabelTwo',
            'websites' => [
                'whiteLabelOne' => [
                    'label' => 'One',
                    'host' => 'host-one.example.com',
                    'method' => 'byHost',
                    'priority' => 3,
                ],
                'whiteLabelTwo' => [
                    'label' => 'Two',
                    'host' => 'host-two.example.com',
                    'method' => 'byUserParam',
                    'user_param' => [
                        'key' => 'the-key',
                        'value' => 'the-value',
                    ],
                    'priority' => 1,
                ],
                'whiteLabelThree' => [
                    'label' => 'Three',
                    'host' => 'host-three.example.com',
                    'method' => 'byHost',
                    'custom_params' => [
                        'custom_key' => 'custom-key-value',
                    ],
                    'priority' => 2,
                ],
            ],
        ];

        $this->load($default);

        $this->assertContainerBuilderHasParameter('ae_white_label.default', 'whiteLabelTwo');

        $expectedWebsites = [
            'whiteLabelTwo' => 'ae_white_label.website.whiteLabelTwo',
            'whiteLabelThree' => 'ae_white_label.website.whiteLabelThree',
            'whiteLabelOne' => 'ae_white_label.website.whiteLabelOne',
        ];

        $this->assertSame(
            $this->container->getParameter('ae_white_label.websites'),
            $expectedWebsites,
            'Check sorting by the whitelabel priority'
        );

        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelOne.label', 'One');
        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelOne.host', 'host-one.example.com');
        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelOne.method', 'byHost');
        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelOne.priority', 3);

        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelTwo.label', 'Two');
        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelTwo.host', 'host-two.example.com');
        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelTwo.method', 'byUserParam');
        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelTwo.user_param.key', 'the-key');
        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelTwo.user_param.value', 'the-value');
        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelTwo.priority', 1);

        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelThree.label', 'Three');
        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelThree.host', 'host-three.example.com');
        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelThree.method', 'byHost');
        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelThree.priority', 2);
        $this->assertContainerBuilderHasParameter('ae_white_label.website.whiteLabelThree.custom_key', 'custom-key-value');
    }

    /**
     * @test
     * @expectedException \Ae\WhiteLabelBundle\Exception\PriorityValueAlreadyUsedException
     * @expectedExceptionMessage priority 3 for website whiteLabelTwo is already used by whiteLabelOne
     */
    public function throw_exception_on_priority_value_already_used()
    {
        $default = [
            'default_website' => 'whiteLabelTwo',
            'websites' => [
                'whiteLabelOne' => [
                    'label' => 'One',
                    'host' => 'host-one.example.com',
                    'method' => 'byHost',
                    'priority' => 3,
                ],
                'whiteLabelTwo' => [
                    'label' => 'Two',
                    'host' => 'host-two.example.com',
                    'method' => 'byUserParam',
                    'user_param' => [
                        'key' => 'the-key',
                        'value' => 'the-value',
                    ],
                    'priority' => 3,
                ],
            ],
        ];

        $this->load($default);
    }

    /**
     * @test
     */
    public function it_should_load_whitelabel_with_the_correct_priority()
    {
        $default = [
            'default_website' => 'fourthSite',
            'websites' => [
                'thirdSite' => [
                    'label' => 'Third Site',
                    'host' => 'third.example.com',
                    'method' => 'byHost',
                    'priority' => 3,
                ],
                'secondSite' => [
                    'label' => 'Second Site',
                    'method' => 'byUserParam',
                    'user_param' => [
                        'key' => 'the-key',
                        'value' => 'the-value',
                    ],
                    'priority' => 2,
                ],
                'fourthSite' => [
                    'label' => 'Fourth Site',
                    'host' => 'fourth.example.com',
                    'method' => 'byHost',
                    'priority' => 4,
                ],
                'firstSite' => [
                    'label' => 'First Site',
                    'host' => 'first.example.com',
                    'method' => 'byUserParam',
                    'user_param' => [
                        'key' => 'the-key',
                        'value' => 'the-value',
                        ],
                    'priority' => 1,
                ],
            ],
        ];

        $this->load($default);

        $expectedWebsites = [
            'firstSite' => 'ae_white_label.website.firstSite',
            'secondSite' => 'ae_white_label.website.secondSite',
            'thirdSite' => 'ae_white_label.website.thirdSite',
            'fourthSite' => 'ae_white_label.website.fourthSite',
        ];

        $this->assertSame(
            $this->container->getParameter('ae_white_label.websites'),
            $expectedWebsites,
            'Check sorting by the whitelabel priority'
        );
    }
        /**
     * Return an array of container extensions you need to be registered for each test (usually just the container
     * extension you are testing.
     *
     * @return ExtensionInterface[]
     */
    protected function getContainerExtensions()
    {
        return [
            new AeWhiteLabelExtension(),
        ];
    }
}
