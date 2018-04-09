<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr2Rules\Test;

use PhpLint\Plugin\DoubleFist\Psr2Rules\Rules;
use PhpLint\TestHelpers\Rules\AbstractRuleTestCase;
use PhpLint\TestHelpers\Rules\RuleAssertionsDataProvider;

/**
 * @group Psr2Rules
 */
class AllRulesTest extends AbstractRuleTestCase
{
    /**
     * @inheritdoc
     */
    public function ruleAssertionsProvider(): RuleAssertionsDataProvider
    {
        return new RuleAssertionsDataProvider([
            new Rules\BlankLineAfterNamespaceRule(),
            new Rules\BlankLineAfterUseDeclarationsRule(),
            new Rules\ClassBracesRule(),
            new Rules\ClassMethodModifiersRule(),
            new Rules\ClassPropertyVisibilityRule(),
            new Rules\ControlStructureKeywordSpacingRule(),
            new Rules\FunctionBracesRule(),
            new Rules\NoVarClassPropertyRule(),
            new Rules\SingleClassPropertyPerStatementRule(),
        ]);
    }
}
