<?php

namespace test\mock\ViewModel;

use Ingenerator\KohanaView\ViewModel;
use PHPUnit\Framework\Assert;

use function class_exists;
use function sprintf;
use function strlen;
use function strrchr;
use function substr;
use function trim;

class ViewModelDummy implements ViewModel
{
    /**
     * Create an instance with any arbitrary class name.
     *
     * @param string $class_name
     *
     * @return ViewModelDummy
     */
    public static function make($class_name)
    {
        if (class_exists($class_name)) {
            $instance = new $class_name();
            Assert::assertInstanceOf(__CLASS__, $instance);

            return $instance;
        }

        $simple_class = trim(strrchr($class_name, '\\') ?: $class_name, '\\');
        $namespace = trim(substr($class_name, 0, -strlen($simple_class)), '\\');
        $definition = sprintf(
            '%s class %s extends %s {}',
            $namespace !== '' ? "namespace $namespace;" : '',
            $simple_class,
            '\\'.__CLASS__
        );
        eval($definition);

        return new $class_name();
    }

    public function display(array $variables)
    {
    }
}
