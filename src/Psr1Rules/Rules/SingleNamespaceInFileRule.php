<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr1Rules\Rules;

use PhpLint\Ast\NodeTraverser;
use PhpLint\Ast\SourceContext;
use PhpLint\Linter\LintResult;
use PhpLint\Rules\AbstractRule;
use PhpLint\Rules\RuleDescription;
use PhpLint\Rules\RuleSeverity;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;

class SingleNamespaceInFileRule extends AbstractRule
{
    const RULE_IDENTIFIER = 'single-namespace-in-file';

    const MESSAGE_MULTIPLE_NAMESPACE_DECLARATIONS_IN_FILE = 'Each file must declare at most one namespace.';

    public function __construct()
    {
        $this->setDescription(
            RuleDescription::forRuleWithIdentifier(self::RULE_IDENTIFIER)
                ->explainedBy('Enforces that each file must contain at most one \'namespace\' declaration.')
                ->rejectsExamples([
                    RuleDescription::createPhpCodeExample(
                        'namespace PhpLint\Rules;',
                        'namespace PhpLint\Rules\Violation;'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'namespace PhpLint\Rules;',
                        'class AnyClass {}',
                        'namespace PhpLint\Rules\Violation;',
                        'class AnyOtherClass {}'
                    ),
                ])
                ->acceptsExamples([
                    RuleDescription::createPhpCodeExample('class AnyClass {}'),
                    RuleDescription::createPhpCodeExample(
                        'namespace PhpLint\Rules;',
                        'class AnyClass {}'
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
    public function validate(Node $node, SourceContext $context, $ruleConfig, LintResult $result)
    {
        $parent = NodeTraverser::getParent($node);
        if (!$parent) {
            return;
        }

        // Check the AST for other namespace declarations BEFORE the given node, which is why we explicitly traverse the
        // parents children and stop once we reached the given node without finding a violation.
        $siblings = NodeTraverser::getChildren($parent);
        foreach ($siblings as $sibling) {
            if (!($sibling instanceof Namespace_)) {
                continue;
            }

            if ($sibling === $node) {
                // Node is first namespace
                return;
            }

            // Found namespace before the given node
            $result->reportViolation(
                $this,
                RuleSeverity::getRuleSeverity($ruleConfig),
                self::MESSAGE_MULTIPLE_NAMESPACE_DECLARATIONS_IN_FILE,
                $context->getSourceRangeOfNode($node)->getStart(),
                $context
            );

            break;
        }
    }
}
