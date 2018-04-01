<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr1Rules\Rules;

use PhpLint\Ast\NodeTraverser;
use PhpLint\Ast\SourceContext;
use PhpLint\Linter\LintResult;
use PhpLint\Rules\AbstractRule;
use PhpLint\Rules\RuleDescription;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;

class ClassNamespaceRule extends AbstractRule
{
    const RULE_IDENTIFIER = 'class-namespace';

    const MESSAGE_CLASS_NOT_NAMESPACED = 'A class must be in a namespace of at least one level.';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(
            RuleDescription::forRuleWithIdentifier(self::RULE_IDENTIFIER)
                ->explainedBy('Enforces that all classes must be contained in a PSR-4 namespace.')
                ->rejectsExamples([
                    RuleDescription::createPhpCodeExample('class AnyClass {}'),
                ])
                ->acceptsExamples([
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
            Class_::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function validate(Node $node, SourceContext $context, LintResult $result)
    {
        $parentNode = NodeTraverser::getParent($node);
        if (!$parentNode || !($parentNode instanceof Namespace_)) {
            $result->reportViolation(
                $this,
                self::MESSAGE_CLASS_NOT_NAMESPACED,
                $context->getSourceRangeOfNode($node->name)->getStart()
            );
        }
    }
}
