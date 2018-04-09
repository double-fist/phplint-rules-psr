<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr2Rules\Rules;

use PhpLint\Ast\SourceContext;
use PhpLint\Ast\Token;
use PhpLint\Linter\LintResult;
use PhpLint\Rules\AbstractRule;
use PhpLint\Rules\RuleDescription;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

class ClassMethodModifiersRule extends AbstractRule
{
    const RULE_IDENTIFIER = 'class-method-modifiers';

    const MESSAGE_ABSTRACT_FINAL_MODIFIER_POSITION = 'The modifier "%s" of class method must be defined right before its visibility.';
    const MESSAGE_STATIC_MODIFIER_POSITION = 'The "static" modifier of class method must be defined right after its visibility.';
    const MESSAGE_VISIBLITY_MUST_BE_DEFINED = 'The visibility of a class method must be defined.';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(
            RuleDescription::forRuleWithIdentifier(self::RULE_IDENTIFIER)
                ->explainedBy('Enforces the order of class method modifiers to follow the pattern "(abstract|final)? (public|protected|private) (static)?".')
                ->rejectsExamples([
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    abstract function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    final function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    static function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public abstract function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public final function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    static public function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public abstract static function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public final static function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    abstract static public function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    final static public function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                ])
                ->acceptsExamples([
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    abstract public function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    final public function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public static function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    abstract public static function test()',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    final public static function test()',
                        '    {',
                        '    }',
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
            ClassMethod::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function validate(Node $node, SourceContext $context, LintResult $result)
    {
        // Find all modifier tokens
        $allTokens = $context->getTokens();
        $tokenIndex = $node->getStartTokenPos();
        $modifierTokens = [];
        while ($allTokens[$tokenIndex]->getType() !== 'T_FUNCTION') {
            if ($allTokens[$tokenIndex]->getType() !== 'T_WHITESPACE') {
                $modifierTokens[] = $allTokens[$tokenIndex];
            }
            $tokenIndex += 1;
        }
        $functionTokenIndex = $tokenIndex;

        // Check that the modifier tokens 'abstract' and 'final' always come first, if defined
        if ($node->isAbstract() && $modifierTokens[0]->getType() !== 'T_ABSTRACT') {
            $result->reportViolation(
                $this,
                sprintf(self::MESSAGE_ABSTRACT_FINAL_MODIFIER_POSITION, 'abstract'),
                self::findToken($modifierTokens, 'T_ABSTRACT')->getSourceRange()->getStart()
            );
        } elseif ($node->isFinal() && $modifierTokens[0]->getType() !== 'T_FINAL') {
            $result->reportViolation(
                $this,
                sprintf(self::MESSAGE_ABSTRACT_FINAL_MODIFIER_POSITION, 'final'),
                self::findToken($modifierTokens, 'T_FINAL')->getSourceRange()->getStart()
            );
        }

        // Check that a visibility modifier is defined
        $visibilityToken = array_filter(
            $modifierTokens,
            function (Token $token) {
                return in_array($token->getType(), ['T_PUBLIC', 'T_PROTECTED', 'T_PRIVATE']);
            }
        );
        $visibilityToken = array_shift($visibilityToken);
        if (!$visibilityToken) {
            $result->reportViolation(
                $this,
                self::MESSAGE_VISIBLITY_MUST_BE_DEFINED,
                $allTokens[$functionTokenIndex + 1]->getSourceRange()->getStart()
            );
        }

        // Check that the modifier token 'static' always comes last, if defined
        if ($node->isStatic() && $modifierTokens[count($modifierTokens) - 1]->getType() !== 'T_STATIC') {
            $result->reportViolation(
                $this,
                self::MESSAGE_STATIC_MODIFIER_POSITION,
                self::findToken($modifierTokens, 'T_STATIC')->getSourceRange()->getStart()
            );
        }
    }

    /**
     * @param Token[] $tokens
     * @param string $type
     * @return Token|null
     */
    private static function findToken(array $tokens, string $type)
    {
        $filteredTokens = array_filter(
            $tokens,
            function (Token $token) use ($type) {
                return $token->getType() === $type;
            }
        );

        return array_shift($filteredTokens);
    }
}
