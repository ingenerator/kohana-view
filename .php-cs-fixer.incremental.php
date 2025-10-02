<?php

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->registerCustomFixers([
        new class extends AbstractFixer {
            public function getName(): string
            {
                return 'Custom/remove_kohana_banner';
            }

            protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
            {
                $sequence = $tokens->findSequence(
                    [
                        [T_STRING, 'defined'],
                        '(',
                        [T_CONSTANT_ENCAPSED_STRING, "'SYSPATH'"],
                        ')',
                        [T_LOGICAL_OR],
                        [T_EXIT],
                        '(',
                        [T_CONSTANT_ENCAPSED_STRING],
                        ')',
                        ';',
                    ]
                );

                if ($sequence === null) {
                    // file contains an exit for some other reason
                    return;
                }

                $startIndex = array_key_first($sequence);

                // It might be a `\defined` in which case we also need to remove the leading `\`
                $previousToken = $tokens->getPrevMeaningfulToken($startIndex);
                if ($previousToken && $tokens[$previousToken]->isGivenKind([T_NS_SEPARATOR])) {
                    $tokens->clearTokenAndMergeSurroundingWhitespace($previousToken);
                }
                foreach (array_keys($sequence) as $tokenIndex) {
                    $tokens->clearTokenAndMergeSurroundingWhitespace($tokenIndex);
                }
            }

            public function isCandidate(Tokens $tokens): bool
            {
                return $tokens->isTokenKindFound(\T_EXIT);
            }

            public function getDefinition(): FixerDefinitionInterface
            {
                return new FixerDefinition(
                    'Fixes kohana files',
                    [],
                    null,
                    false
                );
            }
        },
    ])
    ->setRules(require __DIR__.'/.php-cs-fixer.incremental-rules.php')
    ->setFinder($finder);
