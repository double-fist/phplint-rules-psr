<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr2Rules\Rules;

use PhpLint\Ast\SourceContext;
use PhpLint\Linter\LintResult;
use PhpLint\Rules\AbstractRule;
use PhpLint\Rules\RuleDescription;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;

class ClassPropertyVisibilityRule extends AbstractRule
{
    const RULE_IDENTIFIER = 'class-property-visibility';

    const MESSAGE_VISIBLITY_MUST_BE_DEFINED = 'The visibility of a class property must be defined.';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(
            RuleDescription::forRuleWithIdentifier(self::RULE_IDENTIFIER)
                ->explainedBy('Enforces that the visibility of all class properties is defined and is the first modifier of the property.')
                ->rejectsExamples([
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    var $property;',
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
                        '    static public $property;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    static protected $property;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    static private $property;',
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
                        '    public static $property;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    protected static $property;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    private static $property;',
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
        // Check that the visibility of the property is defined and is the first modifier
        $tokens = $context->getTokens();
        $firstPropertyToken = $tokens[$node->getStartTokenPos()];
        if (!in_array($firstPropertyToken->getType(), ['T_PUBLIC', 'T_PROTECTED', 'T_PRIVATE'])) {
            $result->reportViolation(
                $this,
                self::MESSAGE_VISIBLITY_MUST_BE_DEFINED,
                $firstPropertyToken->getSourceRange()->getStart()
            );
        }
    }
}
