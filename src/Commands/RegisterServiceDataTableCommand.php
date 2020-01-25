<?php

namespace Laravel\DataTables\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;

class RegisterServiceDataTableCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $signature = 'datatable:register {name : The name for the datatable service to be created} {model : The name of the model that datatable service is associated with}';

    /**
     * @var string
     */
    protected $type = "Datatable";

    /**
     * @param $name
     */
    public function replaceModelName($name)
    {
        return str_replace($this->getNamespace($name) . '\\', '', $name);
    }

    /**
     * @param $stub
     * @param $name
     * @return mixed
     */
    public function replaceNamespace(&$stub, $name)
    {
        $modelName = $this->replaceModelName($this->argument('model'));
        $stub = str_replace(
            ['NamespacedDummyModel', 'DummyNamespace', 'DummyModel', 'dummies'],
            [$this->repalceModelNamespace($this->argument('model')), $this->getNamespace($name), $modelName, strtolower(Str::plural($modelName))],
            $stub
        );

        return $this;
    }

    /**
     * @param $rootNamespace
     * @return mixed
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Services';
    }

    protected function getStub()
    {
        return __DIR__ . '/stubs/DatatableService.stub';
    }

    /**
     * @param $stub
     * @param $name
     */
    protected function repalceModelNamespace($name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        if ($namespace = $this->getNamespace($name)) {
            return sprintf('%s\\%s', $namespace, $class);
        }

        return sprintf('%s\\%s', "App", $class);
    }

    /**
     * @param $stub
     * @param $name
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        return str_replace('DummiesDatatableService', $class, parent::replaceClass($stub, $name));
    }
}
