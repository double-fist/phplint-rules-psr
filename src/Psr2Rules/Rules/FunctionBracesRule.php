<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr2Rules\Rules;

use PhpLint\Ast\SourceContext;
use PhpLint\Linter\LintResult;
use PhpLint\Rules\AbstractRule;
use PhpLint\Rules\RuleDescription;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

class FunctionBracesRule extends AbstractRule
{
    const RULE_IDENTIFIER = 'method-braces';

    const MESSAGE_CLOSING_BRACE_INDENTATION = 'The closing brace of a method must have the same indentation as the method delcaration.';
    const MESSAGE_CLOSING_BRACE_OWN_LINE = 'The closing brace of a method must be on a line of its own.';
    const MESSAGE_OPENING_BRACE_INDENTATION = 'The opening brace of a method must have the same indentation as the method delcaration.';
    const MESSAGE_OPENING_BRACE_NEXT_LINE = 'The opening brace of a method must go on the next line after the method definition.';
    const MESSAGE_OPENING_BRACE_OWN_LINE = 'The opening brace of a method must be on a line of its own.';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(
            RuleDescription::forRuleWithIdentifier(self::RULE_IDENTIFIER)
                ->explainedBy('Enforces that the opening brace of a class method goes on the next line after method definition and the closing brace goes on the next line after the method body.')
                ->rejectsExamples([
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a){}',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a) {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '    {}',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '      {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '    { // A comment',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '    {$b = $a;}',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '    {',
                        '        $b = $a;}',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '    {',
                        '        $b = $a;',
                        '  }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '    {',
                        '        $b = $a;',
                        '    } // A comment',
                        '}'
                    ),
                ])
                ->acceptsExamples([
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '    {',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '    {',
                        '        $b = $a;',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '    {',
                        '',
                        '        $b = $a;',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '    {',
                        '        $b = $a;',
                        '',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '    {',
                        '',
                        '    }',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    public function test($a)',
                        '    {',
                        '    }',
                        '    // A comment',
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
        $tokens = $context->getTokens();
        $nodeSourceRange = $context->getSourceRangeOfNode($node);

        // Find the closing brace token, which is the last token of the node
        $closingTokenIndex = $node->getEndTokenPos();
        $closingToken = $tokens[$node->getEndTokenPos()];
        $closingLocation = $closingToken->getSourceRange()->getStart();

        // Find the opening brace token, which directly preceeds the first token of the method statements or the closing
        // brace token, if the method body is empty
        $statements = $node->getStmts();
        $openingTokenIndex = (count($statements) > 0) ? ($statements[0]->getStartTokenPos() - 1) : $closingTokenIndex - 1;
        while ($openingTokenIndex > $node->getStartTokenPos() && $tokens[$openingTokenIndex]->getValue() !== '{') {
            $openingTokenIndex -= 1;
        }
        $openingToken = $tokens[$openingTokenIndex];
        $openingLocation = $openingToken->getSourceRange()->getStart();

        // Check the position (line and indentation) of the opening brace in relation to the function statement
        if ($openingLocation->getLine() !== ($node->getStartLine() + 1)) {
            $result->reportViolation(
                $this,
                self::MESSAGE_OPENING_BRACE_NEXT_LINE,
                $openingLocation
            );
        } elseif ($openingLocation->getColumn() !== $nodeSourceRange->getStart()->getColumn()) {
            $result->reportViolation(
                $this,
                self::MESSAGE_OPENING_BRACE_INDENTATION,
                $openingLocation
            );
        }

        // Check that the next non-whitespace token starts on the next line after the opening brace
        $tokenAfterOpening = $context->findSucceedingNonWhitespaceToken($openingTokenIndex);
        if ($tokenAfterOpening->getSourceRange()->getStart()->getLine() === $openingLocation->getLine()) {
            $result->reportViolation(
                $this,
                self::MESSAGE_OPENING_BRACE_OWN_LINE,
                $openingLocation
            );
        }

        // Check the position (line and indentation) of the closing brace in relation to the last statement of the
        // function body (or the opnening brace, if the body is empty) as well as the function statement
        $tokenBeforeClosing = $context->findPrecedingNonWhitespaceToken($closingTokenIndex);
        if ($tokenBeforeClosing->getSourceRange()->getEnd()->getLine() === $closingLocation->getLine()) {
            $result->reportViolation(
                $this,
                self::MESSAGE_CLOSING_BRACE_OWN_LINE,
                $closingLocation
            );
        } elseif ($closingLocation->getColumn() !== $nodeSourceRange->getStart()->getColumn()) {
            $result->reportViolation(
                $this,
                self::MESSAGE_CLOSING_BRACE_INDENTATION,
                $closingLocation
            );
        }

        // Check that the next non-whitespace token starts on the next line after the closing brace
        $tokenAfterClosing = $context->findSucceedingNonWhitespaceToken($closingTokenIndex);
        if ($tokenAfterClosing && $tokenAfterClosing->getSourceRange()->getStart()->getLine() === $closingLocation->getLine()) {
            $result->reportViolation(
                $this,
                self::MESSAGE_CLOSING_BRACE_OWN_LINE,
                $closingLocation
            );
        }
    }
}
