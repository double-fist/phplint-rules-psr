<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr1Rules;

use PhpLint\Plugin\AbstractRulesPlugin;
use PhpLint\Rules\Rule;
use PhpLint\Rules\RuleException;
use PhpLint\Rules\RuleLoader;

class Rules extends AbstractRulesPlugin
{
    /**
     * @var array
     */
    private $loadedRules = [];

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'DoubleFist/Psr1Rules';
    }

    /**
     * @inheritdoc
     */
    public function getPlugins(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function hasRule(string $ruleId): bool
    {
        return class_exists(self::createFullClassNameForRuleId($ruleId));
    }

    /**
     * @inheritdoc
     */
    public function loadRule(string $ruleId): Rule
    {
        if (isset($this->loadedRules[$ruleId])) {
            return $this->loadedRules[$ruleId];
        }
        if (!$this->hasRule($ruleId)) {
            throw RuleException::ruleNotFound($ruleId);
        }

        $ruleClassName = self::createFullClassNameForRuleId($ruleId);
        $this->loadedRules[$ruleId] = new $ruleClassName();

        return $this->loadedRules[$ruleId];
    }

    /**
     * @param string $ruleId
     * @return string
     */
    private static function createFullClassNameForRuleId(string $ruleId): string
    {
        return __NAMESPACE__ . '\\Rules\\' . RuleLoader::createClassNameForRuleId($ruleId);
    }
}
