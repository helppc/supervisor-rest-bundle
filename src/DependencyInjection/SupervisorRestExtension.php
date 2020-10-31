<?php declare(strict_types=1);

namespace HelpPC\Bundle\SupervisorRestBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class SupervisorRestExtension extends Extension
{
    /**
     * @param mixed[] $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration( new Configuration(), $configs);

        $container->setParameter('supervisor.servers', $config['servers'][$config['default_environment']]);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }
}
