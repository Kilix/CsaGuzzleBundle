<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Csa Guzzle Extension
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class CsaGuzzleExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if ($config['profiler']) {
            $loader->load('collector.xml');
            $loader->load('twig.xml');
        }

        $loader->load('log.xml');
        $loader->load('factory.xml');

        $this->buildLogSubscriber($config['log'], $container);
    }

    protected function buildLogSubscriber(array $config, ContainerBuilder $container)
    {
        $definition = $container->getDefinition('csa_guzzle.subscriber.log');
        if ($container->hasDefinition($config['logger'])) {
            $definition->replaceArgument(0, new Reference($config['logger']));
        }

        if (!empty($config['format'])) {
            $definition->addArgument($config['format']);
        }

        if (!empty($config['channel'])) {
            $definition->clearTag('monolog.logger');
            $definition->addTag('monolog.logger', array(
                'channel' => $config['channel'],
            ));

        }
    }
}
