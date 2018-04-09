<?php
declare(strict_types=1);

namespace PhpLint\Plugin\DoubleFist\Psr2Rules\Rules;

use PhpLint\Ast\SourceContext;
use PhpLint\Ast\Token;
use PhpLint\Linter\LintResult;
use PhpLint\Rules\AbstractRule;
use PhpLint\Rules\RuleDescription;
use PhpParser\Node;
use PhpParser\Node\Stmt;

class ControlStructureKeywordSpacingRule extends AbstractRule
{
    const RULE_IDENTIFIER = 'control-structure-keyword-spacing';

    const MESSAGE_SINGLE_SPACE_BEFORE_KEYWORD = 'Inlined control structure keywords must be preceeded by a single space.';
    const MESSAGE_SINGLE_SPACE_AFTER_KEYWORD = 'Control structure keywords must be succeeded by a single space.';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(
            RuleDescription::forRuleWithIdentifier(self::RULE_IDENTIFIER)
                ->explainedBy('Enforces the all control structure keywords ("if", "for" etc.) are followed by a single space as well as preceeded by a single space, if inlined.')
                ->rejectsExamples([
                    RuleDescription::createPhpCodeExample('if(true) {}'),
                    RuleDescription::createPhpCodeExample('if  (true) {}'),
                    RuleDescription::createPhpCodeExample("if\t(true) {}"),
                    RuleDescription::createPhpCodeExample(
                        'if',
                        '(true) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        '} elseif(true) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        '} elseif  (true) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        "} elseif\t(true) {}"
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        '} elseif',
                        '(true) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        '}elseif (true) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        '}  elseif (true) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        "}\telseif (true) {}"
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        '}',
                        'elseif (true) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        '} else{}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        '} else  {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        "} else\t{}"
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        '} else',
                        '{}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        '}else {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        '}  else {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        "}\telse {}"
                    ),
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        '}',
                        'else {}'
                    ),
                    RuleDescription::createPhpCodeExample('for($i = 0; $i < 10; $i += 1) {}'),
                    RuleDescription::createPhpCodeExample('for  ($i = 0; $i < 10; $i += 1) {}'),
                    RuleDescription::createPhpCodeExample("for\t(\$i = 0; \$i < 10; \$i += 1) {}"),
                    RuleDescription::createPhpCodeExample(
                        'for',
                        '($i = 0; $i < 10; $i += 1) {}'
                    ),
                    RuleDescription::createPhpCodeExample('foreach([] as $key => $value) {}'),
                    RuleDescription::createPhpCodeExample('foreach  ([] as $key => $value) {}'),
                    RuleDescription::createPhpCodeExample("foreach\t([] as \$key => \$value) {}"),
                    RuleDescription::createPhpCodeExample(
                        'foreach',
                        '([] as $key => $value) {}'
                    ),
                    RuleDescription::createPhpCodeExample('while(false) {}'),
                    RuleDescription::createPhpCodeExample('while  (false) {}'),
                    RuleDescription::createPhpCodeExample("while\t(false) {}"),
                    RuleDescription::createPhpCodeExample(
                        'while',
                        '(false) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'do{',
                        '} while (false);'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'do  {',
                        '} while (false);'
                    ),
                    RuleDescription::createPhpCodeExample(
                        "do\t{",
                        '} while (false);'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'do',
                        '{',
                        '} while (false);'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'do {',
                        '} while(false);'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'do {',
                        '} while  (false);'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'do {',
                        "} while\t(false);"
                    ),
                    RuleDescription::createPhpCodeExample(
                        'do {',
                        '} while',
                        '(false);'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'do {',
                        '}while (false);'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'do {',
                        '}  while (false);'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'do {',
                        "}\twhile (false);"
                    ),
                    RuleDescription::createPhpCodeExample(
                        'do {',
                        '}',
                        'while (false);'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'switch($a) {',
                        '    case \'A\':',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'switch  ($a) {',
                        '    case \'A\':',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        "switch\t(\$a) {",
                        '    case \'A\':',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'switch',
                        '($a) {',
                        '    case \'A\':',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'switch ($a) {',
                        '    case\'A\':',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'switch ($a) {',
                        '    case  \'A\':',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'switch ($a) {',
                        "    case\t'A':",
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'switch ($a) {',
                        '    case',
                        '\'A\':',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try{',
                        '} catch (\Exception $e) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try  {',
                        '} catch (\Exception $e) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        "try{\t",
                        '} catch (\Exception $e) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try',
                        '{',
                        '} catch (\Exception $e) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '} catch(\Exception $e) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '} catch  (\Exception $e) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        "} catch\t(\\Exception \$e) {}"
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '}',
                        'catch(\Exception $e) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '}catch (\Exception $e) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '}  catch (\Exception $e) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        "}\tcatch (\\Exception \$e) {}"
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '}',
                        'catch (\Exception $e) {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '} catch (\Exception $e) {',
                        '} finally{}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '} catch (\Exception $e) {',
                        '} finally  {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '} catch (\Exception $e) {',
                        "} finally\t{}"
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '} catch (\Exception $e) {',
                        '} finally',
                        '{}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '} catch (\Exception $e) {',
                        '}finally {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '} catch (\Exception $e) {',
                        '}  finally {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '} catch (\Exception $e) {',
                        "}\tfinally {}"
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '} catch (\Exception $e) {',
                        '}',
                        'finally {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'goto  a;',
                        'a:',
                        '// This is a...'
                    ),
                    RuleDescription::createPhpCodeExample(
                        "goto\ta;",
                        'a:',
                        '// This is a...'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'goto',
                        'a;',
                        'a:',
                        '// This is a...'
                    ),
                ])
                ->acceptsExamples([
                    RuleDescription::createPhpCodeExample(
                        'if (true) {',
                        '} elseif (true) {',
                        '} else {}'
                    ),
                    RuleDescription::createPhpCodeExample('for ($i = 0; $i < 10; $i += 1) {}'),
                    RuleDescription::createPhpCodeExample('foreach ([] as $key => $value) {}'),
                    RuleDescription::createPhpCodeExample('while (false) {}'),
                    RuleDescription::createPhpCodeExample(
                        'do {',
                        '} while (false);'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'switch ($a) {',
                        '    case \'A\':',
                        '}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'try {',
                        '} catch (\Exception $e) {',
                        '} finally {}'
                    ),
                    RuleDescription::createPhpCodeExample(
                        'goto a;',
                        'a:',
                        '// This is a...'
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
            Stmt\Case_::class,
            Stmt\Catch_::class,
            Stmt\Do_::class,
            Stmt\Else_::class,
            Stmt\ElseIf_::class,
            Stmt\Finally_::class,
            Stmt\For_::class,
            Stmt\Foreach_::class,
            Stmt\Goto_::class,
            Stmt\If_::class,
            Stmt\Switch_::class,
            Stmt\TryCatch::class,
            Stmt\While_::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function validate(Node $node, SourceContext $context, LintResult $result)
    {
        $allTokens = $context->getTokens();
        $keywordToken = $allTokens[$node->getStartTokenPos()];
        $this->checkInitialKeywordToken($allTokens, $keywordToken, $result);

        if ($node instanceof Stmt\Else_ || $node instanceof Stmt\ElseIf_ || $node instanceof Stmt\Catch_ || $node instanceof Stmt\Finally_) {
            $this->checkInlineKeywordToken($allTokens, $keywordToken, $result);
        }

        if ($node instanceof Stmt\Do_) {
            // Find the respective 'while' node
            $whileToken = $context->findSucceedingNonWhitespaceToken($node->getStartTokenPos(), \T_WHILE);
            if ($whileToken) {
                $this->checkInitialKeywordToken($allTokens, $whileToken, $result);
                $this->checkInlineKeywordToken($allTokens, $whileToken, $result);
            }
        }
    }

    /**
     * Reports a violation if the given $keywordToken is not directly succeeded by a single whitespace character.
     *
     * @param Token[] $allTokens
     * @param Token $keywordToken
     * @param LintResult $result
     */
    private function checkInitialKeywordToken(array $allTokens, Token $keywordToken, LintResult $result)
    {
        $keywordTokenIndex = array_search($keywordToken, $allTokens);
        $succeedingToken = $allTokens[$keywordTokenIndex + 1];
        if ($succeedingToken->getType() !== 'T_WHITESPACE' || $succeedingToken->getValue() !== ' ') {
            $result->reportViolation(
                $this,
                self::MESSAGE_SINGLE_SPACE_AFTER_KEYWORD,
                $succeedingToken->getSourceRange()->getStart()
            );
        }
    }

    /**
     * Reports a violation if the given, inlined $keywordToken is not directly preceeded by a single whitespace
     * character.
     *
     * @param Token[] $allTokens
     * @param Token $keywordToken
     * @param LintResult $result
     */
    private function checkInlineKeywordToken(array $allTokens, Token $keywordToken, LintResult $result)
    {
        $keywordTokenIndex = array_search($keywordToken, $allTokens);
        $preceedingToken = $allTokens[$keywordTokenIndex - 1];
        $keywordStartsNewLine = $preceedingToken->getSourceRange()->getStart()->getLine() < $keywordToken->getSourceRange()->getStart()->getLine();
        if (!$keywordStartsNewLine && $preceedingToken->getType() !== 'T_WHITESPACE' || $preceedingToken->getValue() !== ' ') {
            $result->reportViolation(
                $this,
                self::MESSAGE_SINGLE_SPACE_BEFORE_KEYWORD,
                $preceedingToken->getSourceRange()->getStart()
            );
        }
    }
}
