<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr2Rules\Rules;

use PhpLint\Ast\SourceContext;
use PhpLint\Linter\LintResult;
use PhpLint\Rules\AbstractRule;
use PhpLint\Rules\RuleDescription;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;

class NoVarClassPropertyRule extends AbstractRule
{
    const RULE_IDENTIFIER = 'no-var-class-property';

    const MESSAGE_NO_VAR_KEYWORD = 'Class properties must not be declared using the "var" keyword.';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(
            RuleDescription::forRuleWithIdentifier(self::RULE_IDENTIFIER)
                ->explainedBy('Enforces that the "var" keyword is not used to delcare a class property.')
                ->rejectsExamples([
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    var $property;',
                        '}'
                    ),
                ])
                ->acceptsExamples([
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public $property;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    protected $property;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    private $property;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    static $property;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public static $property;',
                        '}'
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
            Property::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function validate(Node $node, SourceContext $context, LintResult $result)
    {
        // Check that the 'var' keyword is not part of the property delcaration
        $allTokens = $context->getTokens();
        $firstPropertyToken = $allTokens[$node->getStartTokenPos()];
        if ($firstPropertyToken->getType() === 'T_VAR') {
            $result->reportViolation(
                $this,
                self::MESSAGE_NO_VAR_KEYWORD,
                $firstPropertyToken->getSourceRange()->getStart()
            );
        }
    }
}
