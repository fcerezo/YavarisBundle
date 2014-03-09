<?php

namespace Yavaris\YavarisCommandBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerDebugCommand;

/**
 * YavarisCommand
 *
 * @author Francisco Cerezo <francisco@yavaris.com>
 */
class UpdateProjectCommand extends ContainerDebugCommand
{
    /**
     * {@inherit}
     */
    protected function configure()
    {
        $this
            ->setName('yavaris:project:update')
            ->setDescription('Actualiza el proyecto actual (bases de datos, fixtures, assets y caché)')
            ->setHelp(
            <<<EOF
El comando <info>yavaris:project:update</info> actualiza el proyecto actual creando la estructura de <comment>bases de datos</comment>,
cargando los <comment>fixtures</comment>, generando los <comment>assets</comment> y vaciando la <comment>caché</comment>.
EOF
        );
    }

    /**
     * {@inherit}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $progress = $this->getHelperSet()->get('progress');
        $progress->setFormat(ProgressHelper::FORMAT_VERBOSE);
        $progress->setBarWidth(60);


        $output->writeln("<info>Actualizando el proyecto</info>");
        $progress->start($output, 100);

        exec('php app/console doctrine:schema:drop --force');
        $progress->advance(15);

        exec('php app/console doctrine:schema:create');
        $progress->advance(15);

        exec('php app/console doctrine:fixtures:load --no-interaction');
        $progress->advance(20);

        exec('php app/console assets:install web');
        $progress->advance(15);
        exec('php app/console assetic:dump');
        $progress->advance(15);

        exec('php app/console cache:clear -env=prod');
        exec('php app/console cache:clear -env=dev');
        $progress->advance(20);

        $progress->finish();
        $output->writeln("<comment>¡Proyecto actualizado!</comment>");
    }
}
