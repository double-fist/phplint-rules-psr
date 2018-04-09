<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr2Rules\Rules;

use PhpLint\Ast\SourceContext;
use PhpLint\Linter\LintResult;
use PhpLint\Rules\AbstractRule;
use PhpLint\Rules\RuleDescription;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;

class ClassBracesRule extends AbstractRule
{
    const RULE_IDENTIFIER = 'class-braces';

    const MESSAGE_CLOSING_BRACE_INDENTATION = 'The closing brace of a class must have the same indentation as the class delcaration.';
    const MESSAGE_CLOSING_BRACE_OWN_LINE = 'The closing brace of a class must be on a line of its own.';
    const MESSAGE_OPENING_BRACE_INDENTATION = 'The opening brace of a class must have the same indentation as the class delcaration.';
    const MESSAGE_OPENING_BRACE_NEXT_LINE = 'The opening brace of a class must go on the next line after the end of the class declaration.';
    const MESSAGE_OPENING_BRACE_OWN_LINE = 'The opening brace of a class must be on a line of its own.';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(
            RuleDescription::forRuleWithIdentifier(self::RULE_IDENTIFIER)
                ->explainedBy('Enforces that the opening brace of a class goes on the next line after class definition and the closing brace goes on the next line after the class body.')
                ->rejectsExamples([
                    RuleDescription::createPhpCodeExample('class MyClass {}'),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass {',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '  {',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '',
                        '{',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{ private $test = 10;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{private $test = 10;}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    private $test = 10;}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    private $test = 10;',
                        '  }'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    private $test = 10;',
                        '} // A comment'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass extends ParentClass implements AnInterface {',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass extends ParentClass implements',
                        '    AnInterface,',
                        '    AnotherInterface {',
                        '}'
                    ),
                ])
                ->acceptsExamples([
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '    private $test = 10;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '',
                        '    private $test = 10;',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '',
                        '    private $test = 10;',
                        '',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass',
                        '{',
                        '}',
                        '// A comment'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass extends ParentClass implements AnInterface',
                        '{',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'class MyClass extends ParentClass implements',
                        '    AnInterface,',
                        '    AnotherInterface',
                        '{',
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
            Class_::class,
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

        // Find the opening brace token, which directly preceeds the first token of the class statements or the closing
        // brace token, if the class is empty
        $statements = $node->stmts;
        $openingTokenIndex = (count($statements) > 0) ? ($statements[0]->getStartTokenPos() - 1) : $closingTokenIndex - 1;
        while ($openingTokenIndex > $node->getStartTokenPos() && $tokens[$openingTokenIndex]->getValue() !== '{') {
            $openingTokenIndex -= 1;
        }
        $openingToken = $tokens[$openingTokenIndex];
        $openingLocation = $openingToken->getSourceRange()->getStart();

        // Check the position (line and indentation) of the opening brace in relation to the class declaration
        $expectedOpeningLocation = $node->getStartLine() + 1;
        if ((count($node->implements) > 0)) {
            // The list of implemented interface might span several lines, hence we have to adjust the expected opening
            // line to be the one after the last interface name
            $expectedOpeningLocation = $node->implements[count($node->implements) - 1]->getStartLine() + 1;
        }
        if ($openingLocation->getLine() !== $expectedOpeningLocation) {
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
        // class body (or the opnening brace, if the body is empty) as well as the class delcaration
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
