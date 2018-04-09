<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr2Rules\Rules;

use PhpLint\Ast\NodeTraverser;
use PhpLint\Ast\SourceContext;
use PhpLint\Linter\LintResult;
use PhpLint\Rules\AbstractRule;
use PhpLint\Rules\RuleDescription;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;

class BlankLineAfterNamespaceRule extends AbstractRule
{
    const RULE_IDENTIFIER = 'blank-line-after-namespace';

    const MESSAGE_MISSING_BLANK_LINE_AFTER_NAMESPACE_DECLARATION = 'A "namespace" declaration must be followed by a blank line.';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(
            RuleDescription::forRuleWithIdentifier(self::RULE_IDENTIFIER)
                ->explainedBy('Enforces that a "namespace" declaration is followed by a blank line.')
                ->rejectsExamples([
                    RuleDescription::createPhpCodeExample(
                        'namespace PhpLint\Rules;',
                        'class MyClass {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'namespace PhpLint\Rules;',
                        '// Still no blank line',
                        ''
                    ),
                ])
                ->acceptsExamples([
                    RuleDescription::createPhpCodeExample(
                        'namespace PhpLint\Rules;'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'namespace PhpLint\Rules;',
                        '',
                        'class MyClass {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'namespace PhpLint\Rules;',
                        '',
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
            Namespace_::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function validate(Node $node, SourceContext $context, LintResult $result)
    {
        // The PHP parser includes all whitespace following the namespace declaration in its source range. Hence we can
        // just check whether the node spans at least two lines. However, if the namespace declaration is not followed
        // by anything else, it is also valid, but might not span that many lines.
        $spansLessThanTwoLines = ($node->getEndLine() - $node->getStartLine()) < 2;
        $siblings = NodeTraverser::getSiblings($node, true);
        $children = NodeTraverser::getChildren($node);
        $isFinalStatement = array_search($node, $siblings) === (count($siblings) - 1) && count($children) === 1;
        if ($spansLessThanTwoLines && !$isFinalStatement) {
            $result->reportViolation(
                $this,
                self::MESSAGE_MISSING_BLANK_LINE_AFTER_NAMESPACE_DECLARATION,
                $context->getSourceRangeOfNode($node)->getStart()
            );
        }
    }
}
