<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr1Rules\Test;

use PhpLint\Plugin\DoubleFist\Psr1Rules\Rules;
use PhpLint\TestHelpers\Rules\AbstractRuleTestCase;
use PhpLint\TestHelpers\Rules\RuleAssertionsDataProvider;

class AllRulesTest extends AbstractRuleTestCase
{
    /**
     * @inheritdoc
     */
    public function ruleAssertionsProvider(): RuleAssertionsDataProvider
    {
        return new RuleAssertionsDataProvider([
            new Rules\ClassConstantNameRule(),
            new Rules\ClassNameRule(),
            new Rules\ClassNamespaceRule(),
            new Rules\MethodNameRule(),
            new Rules\SingleClassInFileRule(),
            new Rules\SingleNamespaceInFileRule(),
        ]);
    }
}
