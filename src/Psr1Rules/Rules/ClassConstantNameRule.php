<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr1Rules\Rules;

use PhpLint\Ast\SourceContext;
use PhpLint\Linter\LintResult;
use PhpLint\Rules\AbstractRule;
use PhpLint\Rules\RuleDescription;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;

class ClassConstantNameRule extends AbstractRule
{
    const RULE_IDENTIFIER = 'class-constant-name';

    const MESSAGE_CLASS_CONSTANT_NAME_NOT_ALL_UPPER_CASE = 'Class constants must be declared in all upper case with underscore separators.';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(
            RuleDescription::forRuleWithIdentifier(self::RULE_IDENTIFIER)
                ->explainedBy('Enforces that all class constants must be declared in all upper case with underscore separators, e.g. \'ALL_UPPER_CASE\'.')
                ->rejectsExamples([
                    RuleDescription::createPhpCodeExample(
                        'class AnyClass',
                        '{',
                        "\t" . 'const myConst = 0;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class AnyClass',
                        '{',
                        "\t" . 'const my_const = 0;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class AnyClass',
                        '{',
                        "\t" . 'const MY_cONST = 0;',
                        '}'
                    ),
                ])
                ->acceptsExamples([
                    RuleDescription::createPhpCodeExample(
                        'class AnyClass',
                        '{',
                        "\t" . 'const MY = 0;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class AnyClass',
                        '{',
                        "\t" . 'const MY_CONST = 0;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class AnyClass',
                        '{',
                        "\t" . 'const _MY_CONST = 0;',
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
            ClassConst::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function validate(Node $node, SourceContext $context, LintResult $result)
    {
        $const = $node->consts[0];
        if (empty($const->name)) {
            return;
        }

        $constNamePattern = '/^_?([A-Z]+_?)+$/';
        if (preg_match($constNamePattern, $const->name->name) !== 1) {
            $result->reportViolation(
                $this,
                self::MESSAGE_CLASS_CONSTANT_NAME_NOT_ALL_UPPER_CASE,
                $context->getSourceRangeOfNode($const->name)->getStart(),
                $context
            );
        }
    }
}
