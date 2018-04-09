<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr2Rules\Rules;

use PhpLint\Ast\NodeTraverser;
use PhpLint\Ast\SourceContext;
use PhpLint\Linter\LintResult;
use PhpLint\Rules\AbstractRule;
use PhpLint\Rules\RuleDescription;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;

class BlankLineAfterUseDeclarationsRule extends AbstractRule
{
    const RULE_IDENTIFIER = 'blank-line-after-use-declarations';

    const MESSAGE_MISSING_BLANK_LINE_AFTER_USE_DECLARATIONS_BLOCK = 'A block of "use" declarations must be followed by a blank line.';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(
            RuleDescription::forRuleWithIdentifier(self::RULE_IDENTIFIER)
                ->explainedBy('Enforces that a block of "use" declarations is followed by a blank line.')
                ->rejectsExamples([
                    RuleDescription::createPhpCodeExample(
                        'use PhpLint\Rules;',
                        'class MyClass {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'use PhpLint\Rules;',
                        'use PhpLint\Util;',
                        'class MyClass {}'
                    ),
                ])
                ->acceptsExamples([
                    RuleDescription::createPhpCodeExample(
                        'use PhpLint\Rules;'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'use PhpLint\Rules;',
                        '',
                        'class MyClass {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'use PhpLint\Rules;',
                        'use PhpLint\Util;',
                        '',
                        'class MyClass {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'use PhpLint\Rules;',
                        'use PhpLint\Util;',
                        '',
                        'use PhpLint\Plugin;',
                        '',
                        'class MyClass {}'
                    ),
                ])
        );
    }

    /**
     * @inheritdoc
     */
    public function getTypes(): array
    {
        return [
            Use_::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function validate(Node $node, SourceContext $context, LintResult $result)
    {
        // Find all siblings following the given node
        $succeedingSiblings = NodeTraverser::getSiblings($node, true);
        array_splice($succeedingSiblings, 0, (array_search($node, $succeedingSiblings) + 1));

        // Use declarations that are followed by another use declaration ot nothing at all are always valid
        $isFollowedByUseDeclaration = count($succeedingSiblings) > 0 && $succeedingSiblings[0] instanceof Use_;
        $isFinalStatement = count($succeedingSiblings) === 0;
        if ($isFollowedByUseDeclaration || $isFinalStatement) {
            return;
        }

        // Check that the following sibling starts at least two lines below the use declaration
        if (($succeedingSiblings[0]->getStartLine() - $node->getEndLine()) < 2) {
            $result->reportViolation(
                $this,
                self::MESSAGE_MISSING_BLANK_LINE_AFTER_USE_DECLARATIONS_BLOCK,
                $context->getSourceRangeOfNode($node)->getStart()
            );
        }
    }
}
