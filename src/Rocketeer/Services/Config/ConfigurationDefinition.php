<?php
namespace Rocketeer\Services\Config;

use Rocketeer\Services\Config\TreeBuilder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigurationDefinition implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root    = $builder->root('rocketeer');

        $root
            ->children()
            ->append($this->getCoreConfiguration())
            ->append($this->getScmConfiguration())
            ->append($this->addHooksConfiguration())
            ->append($this->getRemoteConfiguration())
            ->append($this->getPathsDefinition())
            ->append($this->getStagesConfiguration())
            ->append($this->getStrategiesConfiguration())
            ->end();

        return $builder;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getCoreConfiguration()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('config', 'array', new NodeBuilder());

        return $node
            ->info('The main configuration of your application')
            ->children()
                ->scalarNode('application_name')
                    ->info("The name of the application to deploy\nThis will create a folder of the same name in the root directory")
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('plugins')
                    ->info('The plugins to load')
                    ->example(['Rocketeer\\Plugins\\Slack\\RocketeerSlack'])
                    ->prototype('scalar')->end()
                ->end()
                ->closureNode('logs')
                    ->info('The schema to use to name log files')
                    ->defaultValue(function (ConnectionsHandler $connections) {
                        return sprintf('%s-%s.log', $connections->getCurrentConnection(), date('Ymd'));
                    })
                ->end()
                ->arrayNode('default')
                    ->info('The default remote connection(s) to execute tasks on')
                    ->beforeNormalization()
                    ->ifString()
                    ->then(function ($default) {
                        return [$default];
                    })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('connections')
                    ->info('The various connections')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('username')->end()
                            ->scalarNode('password')->end()
                            ->scalarNode('key')->end()
                            ->scalarNode('keyphrase')->end()
                            ->scalarNode('agent')->end()
                            ->booleanNode('db_role')->end()
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('use_roles')->defaultFalse()->end()
                ->arrayNode('on')
                    ->children()
                        ->arrayNode('stages')->end()
                        ->arrayNode('connections')->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getScmConfiguration()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('scm', 'array', new NodeBuilder());

        return $node
            ->info('The configuration of your repository')
            ->children()
                ->enumNode('scm')
                    ->info('The SCM used')
                    ->values(['git', 'svn', 'hg'])
                    ->isRequired()
                    ->defaultValue('git')
                ->end()
                    ->scalarNode('repository')
                    ->info('The SSH/HTTPS address to your repository')
                    ->example('https://github.com/vendor/website.git')
                ->end()
                ->scalarNode('username')->end()
                ->scalarNode('password')->end()
                ->scalarNode('branch')
                    ->info('The branch to deploy')
                    ->defaultValue('master')
                ->end()
                ->scalarNode('shallow')
                    ->info("Whether your SCM should do a \"shallow\" clone of the repository or not - this means a clone with just the latest state of your application (no history).\nIf you're having problems cloning, try setting this to false")
                    ->defaultTrue()
                ->end()
                ->scalarNode('submodules')
                    ->info("Recursively pull in submodules.\nWorks only with Git")
                    ->defaultTrue()
                ->end()
            ->end();
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getStagesConfiguration()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('stages', 'array', new NodeBuilder());

        return $node
            ->info('Here you can configure your stages')
            ->children()
                ->arrayNode('stages')
                    ->info("Adding entries to this array will split the remote folder in stages\nExample: /var/www/yourapp/staging and /var/www/yourapp/production")
                    ->example(['staging', 'production'])
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('default')
                    ->info("The default stage to execute tasks on when --stage is not provided.\nFalsey means all of them")
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($default) {
                            return [$default];
                        })
                    ->end()
                    ->prototype('scalar')
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getStrategiesConfiguration()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('strategies', 'array', new NodeBuilder());

        return $node
            ->info("Here you can configure in a modular way which tasks to use to execute various core parts of your deployment's flow")
            ->children()
                ->scalarNode('check')
                    ->info("Which strategy to use to check the server")
                    ->defaultValue('Php')
                ->end()
                ->scalarNode('deploy')
                    ->info("Which strategy to use to create a new release")
                    ->defaultValue('Clone')
                ->end()
                ->scalarNode('test')
                    ->info("Which strategy to use to test your application")
                    ->defaultValue('Phpunit')
                ->end()
                ->scalarNode('migrate')
                    ->info("Which strategy to use to migrate your database")
                ->end()
                ->scalarNode('dependencies')
                    ->info("Which strategy to use to install your application's dependencies")
                    ->defaultValue('Polyglot')
                ->end()
                ->closureNode('primer')
                    ->defaultValue(function (Primer $task) {
                        return array(
                            // $task->executeTask('Test'),
                            // $task->binary('grunt')->execute('lint'),
                        );
                    })
                ->end()
            ->end();
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getRemoteConfiguration()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('remote', 'array', new NodeBuilder());

        return $node
            ->info("Options related to the remote server")
            ->children()
                ->arrayNode('variables')
                    ->info('Variables about the servers')
                    ->children()
                        ->scalarNode('directory_separator')->defaultValue('/')->end()
                        ->scalarNode('line_endings')->defaultValue("\n")->end()
                    ->end()
                ->end()
                ->integerNode('keep_releases')
                    ->info('The number of releases to keep on server at all times')
                    ->min(0)
                    ->defaultValue(4)
                ->end()
                ->scalarNode('root_directory')
                    ->info("The root directory where your applications will be deployed.\nThis path needs to start at the root, ie. start with a /")
                    ->defaultValue('/home/www/')
                ->end()
                ->scalarNode('app_directory')
                    ->info("The folder the application will be cloned in.\nLeave empty to use `application_name` as your folder name")
                ->end()
                ->scalarNode('subdirectory')
                    ->info("If the core of your application (ie. where dependencies/migrations/etc need to be run is in a subdirectory, specify it there (per example 'my_subdirectory')")
                ->end()
                ->arrayNode('shared')
                    ->info('A list of folders/file to be shared between releases')
                    ->example(['logs', 'public/uploads'])
                    ->prototype('scalar')->end()
                ->end()
                ->enumNode('symlink')
                    ->info('The way symlinks are created')
                    ->values(['absolute', 'relative'])
                    ->defaultValue('absolute')
                ->end()
                ->booleanNode('shell')
                    ->info("If enabled will force a shell to be created which is required for some tools like RVM or NVM")
                    ->defaultTrue()
                ->end()
                ->arrayNode('shelled')
                    ->info('An array of commands to run under shell')
                    ->defaultValue(['which', 'ruby', 'npm', 'bower', 'bundle', 'grunt'])
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('permissions')
                    ->info('Files permissions related settings')
                    ->children()
                        ->arrayNode('files')
                            ->info('The folders and files to set as web writable')
                            ->example(['storage', 'public'])
                            ->prototype('scalar')->end()
                        ->end()
                        ->closureNode('callback')
                            ->info("what actions will be executed to set permissions on the folder above")
                            ->defaultValue(function ($task, $file) {
                                return array(
                                    sprintf('chmod -R 755 %s', $file),
                                    sprintf('chmod -R g+s %s', $file),
                                    sprintf('chown -R www-data:www-data %s', $file),
                                );
                            })
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getPathsDefinition()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('paths', 'array', new NodeBuilder());

        return $node
            ->info("Here you can manually set paths to some commands Rocketeer might try to use.\nIf you leave those empty it will try to find them manually or assume they're in the root folder")
            ->defaultValue(array(
                'app'      => getcwd(),
                'php'      => null,
                'composer' => null,
            ))
            ->prototype('scalar')
            ->end();
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function addHooksConfiguration()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('hooks', 'array', new NodeBuilder());

        return $node
            ->info("Here you can customize Rocketeer by adding tasks, strategies, etc.")
            ->children()
                ->arrayNode('before')
                    ->prototype('array')->end()
                ->end()
                ->arrayNode('after')
                    ->prototype('array')->end()
                ->end()
                ->arrayNode('custom')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('roles')
                    ->prototype('scalar')->end()
                ->end()
            ->end();
    }
}
