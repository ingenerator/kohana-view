<?php

namespace Ingenerator\KohanaViewMigrationTool;

use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\PrettyPrinter\Standard;

final class ViewModelConverter
{
    private readonly Parser $parser;
    private readonly PrettyPrinter $printer;

    public function __construct()
    {
        $this->parser = new ParserFactory()->createForNewestSupportedVersion();
        $this->printer = new Standard();
    }

    public function convert(string $source): string
    {
        $source_ast = $this->parser->parse($source);

        $dumper = new NodeDumper();
        file_put_contents(__DIR__.'/dump.txt', $dumper->dump($source_ast));
        $traverser = new NodeTraverser(
            new ConvertDisplayVariablesVisitor(),
            new ConvertComputedPropertiesVisitor(),
            // @todo convert default display values
        );
        $updated = $traverser->traverse($source_ast);

        return $this->printer->prettyPrintFile($updated);
    }
}