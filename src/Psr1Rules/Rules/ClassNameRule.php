<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr1Rules\Rules;

use PhpLint\Ast\SourceContext;
use PhpLint\Linter\LintResult;
use PhpLint\Rules\AbstractRule;
use PhpLint\Rules\RuleDescription;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;

class ClassNameRule extends AbstractRule
{
    const RULE_IDENTIFIER = 'class-name';

    const MESSAGE_CLASS_NAME_NOT_IN_STUDLY_CAPS = 'Class names MUST be declared in StudlyCaps.';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(
            RuleDescription::forRuleWithIdentifier(self::RULE_IDENTIFIER)
                ->explainedBy('Enforces that all class names must be declared in \'StudlyCaps\' aka \'PascalCase\'.')
                ->rejectsExamples([
                    RuleDescription::createPhpCodeExample('class acme {}'),
                    RuleDescription::createPhpCodeExample('class aCme {}'),
                    RuleDescription::createPhpCodeExample('class Any_Class {}'),
                    RuleDescription::createPhpCodeExample('class any_class {}'),
                    RuleDescription::createPhpCodeExample('class hTML_parser {}'),
                ])
                ->acceptsExamples([
                    RuleDescription::createPhpCodeExample('class Acme {}'),
                    RuleDescription::createPhpCodeExample('class AnyClass {}'),
                    RuleDescription::createPhpCodeExample('class HTMLParser {}'),
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
        $className = $node->name;
        if (!$className || mb_strlen($className->name) === 0) {
            return;
        }

        $classNamePattern = '/^[A-Z][A-Za-z]*$/';
        if (preg_match($classNamePattern, $className->name) !== 1) {
            $result->reportViolation(
                $this,
                self::MESSAGE_CLASS_NAME_NOT_IN_STUDLY_CAPS,
                $context->getSourceRangeOfNode($className)->getStart()
            );
        }
    }
}
