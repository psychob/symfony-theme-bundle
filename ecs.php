<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Alias\BacktickToShellExecFixer;
use PhpCsFixer\Fixer\Alias\NoAliasLanguageConstructCallFixer;
use PhpCsFixer\Fixer\Alias\NoMixedEchoPrintFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer;
use PhpCsFixer\Fixer\ArrayNotation\NormalizeIndexBraceFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use PhpCsFixer\Fixer\AttributeNotation\AttributeEmptyParenthesesFixer;
use PhpCsFixer\Fixer\AttributeNotation\OrderedAttributesFixer;
use PhpCsFixer\Fixer\Basic\BracesPositionFixer;
use PhpCsFixer\Fixer\Basic\EncodingFixer;
use PhpCsFixer\Fixer\Basic\NoMultipleStatementsPerLineFixer;
use PhpCsFixer\Fixer\Basic\NonPrintableCharacterFixer;
use PhpCsFixer\Fixer\Basic\NoTrailingCommaInSinglelineFixer;
use PhpCsFixer\Fixer\Basic\NumericLiteralSeparatorFixer;
use PhpCsFixer\Fixer\Basic\OctalNotationFixer;
use PhpCsFixer\Fixer\Basic\PsrAutoloadingFixer;
use PhpCsFixer\Fixer\Basic\SingleLineEmptyBodyFixer;
use PhpCsFixer\Fixer\Casing\ClassReferenceNameCasingFixer;
use PhpCsFixer\Fixer\Casing\ConstantCaseFixer;
use PhpCsFixer\Fixer\Casing\IntegerLiteralCaseFixer;
use PhpCsFixer\Fixer\Casing\LowercaseKeywordsFixer;
use PhpCsFixer\Fixer\Casing\LowercaseStaticReferenceFixer;
use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\Casing\MagicMethodCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeTypeDeclarationCasingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\CastNotation\LowercaseCastFixer;
use PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer;
use PhpCsFixer\Fixer\CastNotation\NoUnsetCastFixer;
use PhpCsFixer\Fixer\CastNotation\ShortScalarCastFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\ClassNotation\NoNullPropertyInitializationFixer;
use PhpCsFixer\Fixer\ClassNotation\NoPhp4ConstructorFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedInterfacesFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedTraitsFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedTypesFixer;
use PhpCsFixer\Fixer\ClassNotation\PhpdocReadonlyClassCommentToKeywordFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleTraitInsertPerStatementFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Comment\MultilineCommentOpeningClosingFixer;
use PhpCsFixer\Fixer\Comment\NoTrailingWhitespaceInCommentFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentSpacingFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer;
use PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer;
use PhpCsFixer\Fixer\ControlStructure\ControlStructureBracesFixer;
use PhpCsFixer\Fixer\ControlStructure\ControlStructureContinuationPositionFixer;
use PhpCsFixer\Fixer\ControlStructure\ElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\EmptyLoopBodyFixer;
use PhpCsFixer\Fixer\ControlStructure\EmptyLoopConditionFixer;
use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\ControlStructure\NoAlternativeSyntaxFixer;
use PhpCsFixer\Fixer\ControlStructure\NoBreakCommentFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\SwitchCaseSemicolonToColonFixer;
use PhpCsFixer\Fixer\ControlStructure\SwitchCaseSpaceFixer;
use PhpCsFixer\Fixer\ControlStructure\SwitchContinueToBreakFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\FunctionNotation\CombineNestedDirnameFixer;
use PhpCsFixer\Fixer\FunctionNotation\DateTimeCreateFromFormatCallFixer;
use PhpCsFixer\Fixer\FunctionNotation\FopenFlagOrderFixer;
use PhpCsFixer\Fixer\FunctionNotation\FunctionDeclarationFixer;
use PhpCsFixer\Fixer\FunctionNotation\ImplodeCallFixer;
use PhpCsFixer\Fixer\FunctionNotation\LambdaNotUsedImportFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoSpacesAfterFunctionNameFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoUnreachableDefaultArgumentValueFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoUselessSprintfFixer;
use PhpCsFixer\Fixer\FunctionNotation\NullableTypeDeclarationForDefaultNullValueFixer;
use PhpCsFixer\Fixer\FunctionNotation\UseArrowFunctionsFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\NoUnneededImportAliasFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer;
use PhpCsFixer\Fixer\Import\SingleLineAfterImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveIssetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareParenthesesFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DirConstantFixer;
use PhpCsFixer\Fixer\LanguageConstruct\ExplicitIndirectVariableFixer;
use PhpCsFixer\Fixer\LanguageConstruct\FunctionToConstantFixer;
use PhpCsFixer\Fixer\LanguageConstruct\GetClassToClassKeywordFixer;
use PhpCsFixer\Fixer\LanguageConstruct\IsNullFixer;
use PhpCsFixer\Fixer\LanguageConstruct\NoUnsetOnPropertyFixer;
use PhpCsFixer\Fixer\LanguageConstruct\NullableTypeDeclarationFixer;
use PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAroundConstructFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLineAfterNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLinesBeforeNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\CleanNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\AssignNullCoalescingToCoalesceEqualFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\LogicalOperatorsFixer;
use PhpCsFixer\Fixer\Operator\LongToShorthandOperatorFixer;
use PhpCsFixer\Fixer\Operator\NewWithParenthesesFixer;
use PhpCsFixer\Fixer\Operator\NoSpaceAroundDoubleColonFixer;
use PhpCsFixer\Fixer\Operator\NoUselessNullsafeOperatorFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\OperatorLinebreakFixer;
use PhpCsFixer\Fixer\Operator\StandardizeIncrementFixer;
use PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\TernaryToElvisOperatorFixer;
use PhpCsFixer\Fixer\Operator\TernaryToNullCoalescingFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\AlignMultilineCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAddMissingParamAnnotationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocArrayTypeFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocListTypeFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoUselessInheritdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocParamOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSummaryFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTagCasingFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimConsecutiveBlankLineSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer;
use PhpCsFixer\Fixer\PhpTag\EchoTagSyntaxFixer;
use PhpCsFixer\Fixer\PhpTag\FullOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\NoClosingTagFixer;
use PhpCsFixer\Fixer\ReturnNotation\NoUselessReturnFixer;
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use PhpCsFixer\Fixer\ReturnNotation\SimplifiedNullReturnFixer;
use PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use PhpCsFixer\Fixer\Semicolon\SpaceAfterSemicolonFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\SimpleToComplexStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBetweenImportGroupsFixer;
use PhpCsFixer\Fixer\Whitespace\CompactNullableTypeDeclarationFixer;
use PhpCsFixer\Fixer\Whitespace\HeredocIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\LineEndingFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use PhpCsFixer\Fixer\Whitespace\SingleBlankLineAtEofFixer;
use PhpCsFixer\Fixer\Whitespace\StatementIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\TypeDeclarationSpacesFixer;
use PhpCsFixer\Fixer\Whitespace\TypesSpacesFixer;
use Symplify\CodingStandard\Fixer\Commenting\RemoveUselessDefaultCommentFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\CodingStandard\Fixer\Spacing\StandaloneLineConstructorParamFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/ecs.php'])
    ->withRules([// alias
        BacktickToShellExecFixer::class, NoAliasLanguageConstructCallFixer::class, NoMixedEchoPrintFixer::class,

        // array notation
        NoMultilineWhitespaceAroundDoubleArrowFixer::class, NoWhitespaceBeforeCommaInArrayFixer::class,
        NormalizeIndexBraceFixer::class, TrimArraySpacesFixer::class, WhitespaceAfterCommaInArrayFixer::class,

        // attributes
        AttributeEmptyParenthesesFixer::class,

        // basics
        BracesPositionFixer::class, EncodingFixer::class, NoMultipleStatementsPerLineFixer::class,
        NoTrailingCommaInSinglelineFixer::class, NonPrintableCharacterFixer::class, OctalNotationFixer::class,
        PsrAutoloadingFixer::class, SingleLineEmptyBodyFixer::class,

        // casing
        ClassReferenceNameCasingFixer::class, IntegerLiteralCaseFixer::class, LowercaseKeywordsFixer::class,
        LowercaseStaticReferenceFixer::class, MagicConstantCasingFixer::class, MagicMethodCasingFixer::class,
        NativeFunctionCasingFixer::class, NativeTypeDeclarationCasingFixer::class,

        // cast notation
        LowercaseCastFixer::class, NoShortBoolCastFixer::class, NoUnsetCastFixer::class,
        ShortScalarCastFixer::class,

        // class notation fixers
        ClassDefinitionFixer::class, NoBlankLinesAfterClassOpeningFixer::class,
        NoNullPropertyInitializationFixer::class, NoPhp4ConstructorFixer::class,
        PhpdocReadonlyClassCommentToKeywordFixer::class, SingleClassElementPerStatementFixer::class,
        SingleTraitInsertPerStatementFixer::class, VisibilityRequiredFixer::class,

        // comments
        MultilineCommentOpeningClosingFixer::class, NoTrailingWhitespaceInCommentFixer::class,
        SingleLineCommentSpacingFixer::class,

        // constant notation
        NativeConstantInvocationFixer::class,

        // control structure
        ControlStructureBracesFixer::class, ElseifFixer::class, IncludeFixer::class,
        NoAlternativeSyntaxFixer::class, NoUselessElseFixer::class, SwitchCaseSemicolonToColonFixer::class,
        SwitchCaseSpaceFixer::class, SwitchContinueToBreakFixer::class,

        // function notation
        CombineNestedDirnameFixer::class, DateTimeCreateFromFormatCallFixer::class, FopenFlagOrderFixer::class,
        ImplodeCallFixer::class, LambdaNotUsedImportFixer::class, NativeFunctionInvocationFixer::class,
        NoSpacesAfterFunctionNameFixer::class, NoUnreachableDefaultArgumentValueFixer::class,
        NoUselessSprintfFixer::class, NullableTypeDeclarationForDefaultNullValueFixer::class,
        UseArrowFunctionsFixer::class, VoidReturnFixer::class,

        // import notation
        NoLeadingImportSlashFixer::class, NoUnneededImportAliasFixer::class, NoUnusedImportsFixer::class,
        SingleLineAfterImportsFixer::class,

        // language constructs
        CombineConsecutiveIssetsFixer::class, CombineConsecutiveUnsetsFixer::class,
        DeclareEqualNormalizeFixer::class, DeclareParenthesesFixer::class, DirConstantFixer::class,
        ExplicitIndirectVariableFixer::class, FunctionToConstantFixer::class, GetClassToClassKeywordFixer::class,
        IsNullFixer::class, NoUnsetOnPropertyFixer::class, SingleSpaceAroundConstructFixer::class,

        // namespace notation
        BlankLineAfterNamespaceFixer::class, CleanNamespaceFixer::class, NoLeadingNamespaceWhitespaceFixer::class,

        // operator
        AssignNullCoalescingToCoalesceEqualFixer::class, LogicalOperatorsFixer::class,
        LongToShorthandOperatorFixer::class, NewWithParenthesesFixer::class, NoSpaceAroundDoubleColonFixer::class,
        NoUselessNullsafeOperatorFixer::class, ObjectOperatorWithoutWhitespaceFixer::class,
        StandardizeIncrementFixer::class, StandardizeNotEqualsFixer::class, TernaryOperatorSpacesFixer::class,
        TernaryToElvisOperatorFixer::class, TernaryToNullCoalescingFixer::class, UnaryOperatorSpacesFixer::class,

        // php tag
        EchoTagSyntaxFixer::class, FullOpeningTagFixer::class, LinebreakAfterOpeningTagFixer::class,
        NoClosingTagFixer::class,

        // phpdoc notation
        AlignMultilineCommentFixer::class, NoBlankLinesAfterPhpdocFixer::class, NoEmptyPhpdocFixer::class,
        NoSuperfluousPhpdocTagsFixer::class, PhpdocAddMissingParamAnnotationFixer::class, PhpdocAlignFixer::class,
        PhpdocAnnotationWithoutDotFixer::class, PhpdocArrayTypeFixer::class, PhpdocIndentFixer::class,
        PhpdocLineSpanFixer::class, PhpdocListTypeFixer::class, PhpdocNoEmptyReturnFixer::class,
        PhpdocNoUselessInheritdocFixer::class, PhpdocParamOrderFixer::class, PhpdocScalarFixer::class,
        PhpdocSeparationFixer::class, PhpdocSummaryFixer::class, PhpdocTagCasingFixer::class,
        PhpdocTrimConsecutiveBlankLineSeparationFixer::class, PhpdocTrimFixer::class, PhpdocTypesFixer::class,

        // return notation
        NoUselessReturnFixer::class, ReturnAssignmentFixer::class, SimplifiedNullReturnFixer::class,

        // semicolon
        NoEmptyStatementFixer::class, NoSinglelineWhitespaceBeforeSemicolonsFixer::class,
        SemicolonAfterInstructionFixer::class, SpaceAfterSemicolonFixer::class,

        // strict
        DeclareStrictTypesFixer::class,

        // string notation
        ExplicitStringVariableFixer::class, SimpleToComplexStringVariableFixer::class, SingleQuoteFixer::class,

        // whitespace notation
        ArrayIndentationFixer::class, BlankLineBetweenImportGroupsFixer::class,
        CompactNullableTypeDeclarationFixer::class, HeredocIndentationFixer::class, LineEndingFixer::class,
        MethodChainingIndentationFixer::class, NoSpacesAroundOffsetFixer::class, NoTrailingWhitespaceFixer::class,
        NoWhitespaceInBlankLineFixer::class, SingleBlankLineAtEofFixer::class, StatementIndentationFixer::class,
        TypeDeclarationSpacesFixer::class, TypesSpacesFixer::class,

        // ecs
        MethodChainingNewlineFixer::class, RemoveUselessDefaultCommentFixer::class,
        StandaloneLineConstructorParamFixer::class, ])
    ->withConfiguredRule(ArraySyntaxFixer::class, ['syntax' => 'short'])
    ->withConfiguredRule(OrderedAttributesFixer::class, ['sort_algorithm' => 'alpha'])
    ->withConfiguredRule(
        NumericLiteralSeparatorFixer::class,
        ['strategy' => 'use_separator', 'override_existing' => true]
    )
    ->withConfiguredRule(ConstantCaseFixer::class, ['case' => 'lower'])
    ->withConfiguredRule(CastSpacesFixer::class, ['space' => 'none'])
    ->withConfiguredRule(
        ClassAttributesSeparationFixer::class,
        ['elements' => ['const' => 'one', 'method' => 'one', 'property' => 'only_if_meta', 'trait_import' => 'none',
            'case'              => 'none', ], ]
    )
    ->withConfiguredRule(
        OrderedInterfacesFixer::class,
        ['case_sensitive' => false, 'direction' => 'ascend', 'order' => 'alpha']
    )
    ->withConfiguredRule(OrderedTraitsFixer::class, ['case_sensitive' => false])
    ->withConfiguredRule(OrderedTypesFixer::class, [

        'case_sensitive' => false, 'sort_algorithm' => 'alpha', 'null_adjustment' => 'always_last', ])
    ->withConfiguredRule(SingleLineCommentStyleFixer::class, ['comment_types' => ['hash', 'asterisk']])
    ->withConfiguredRule(ControlStructureContinuationPositionFixer::class, ['position' => 'same_line'])
    ->withConfiguredRule(EmptyLoopBodyFixer::class, ['style' => 'braces'])
    ->withConfiguredRule(EmptyLoopConditionFixer::class, ['style' => 'while'])
    ->withConfiguredRule(NoBreakCommentFixer::class, ['comment_text' => 'fallthrough'])
    ->withConfiguredRule(TrailingCommaInMultilineFixer::class, ['elements' => ['arrays'], 'after_heredoc' => true])
    ->withConfiguredRule(
        FunctionDeclarationFixer::class,
        ['trailing_comma_single_line' => false, 'closure_function_spacing' => 'none',
            'closure_fn_spacing'      => 'none', ]
    )
    ->withConfiguredRule(MethodArgumentSpaceFixer::class, [

        'after_heredoc'                    => true, 'on_multiline' => 'ensure_fully_multiline', 'attribute_placement' => 'same_line',
        'keep_multiple_spaces_after_comma' => false, ])
    ->withConfiguredRule(FullyQualifiedStrictTypesFixer::class, ['import_symbols' => true])
    ->withConfiguredRule(
        GlobalNamespaceImportFixer::class,
        ['import_classes' => true, 'import_constants' => true, 'import_functions' => false]
    )
    ->withConfiguredRule(
        OrderedImportsFixer::class,
        ['case_sensitive'   => false, 'sort_algorithm' => 'alpha',
            'imports_order' => ['const', 'class', 'function'], ]
    )
    ->withConfiguredRule(SingleImportPerStatementFixer::class, ['group_to_single_imports' => true])
    ->withConfiguredRule(NullableTypeDeclarationFixer::class, ['syntax' => 'question_mark'])
    ->withConfiguredRule(ListSyntaxFixer::class, ['syntax' => 'short'])
    ->withConfiguredRule(BlankLinesBeforeNamespaceFixer::class, ['min_line_breaks' => 2, 'max_line_breaks' => 2])
    ->withConfiguredRule(
        BinaryOperatorSpacesFixer::class,
        ['default' => 'single_space', 'operators' => ['=>' => 'align_single_space_minimal']]
    )
    ->withConfiguredRule(ConcatSpaceFixer::class, ['spacing' => 'one'])
    ->withConfiguredRule(OperatorLinebreakFixer::class, ['position' => 'beginning'])
    ->withConfiguredRule(
        MultilineWhitespaceBeforeSemicolonsFixer::class,
        ['strategy' => 'new_line_for_chained_calls']
    )
    ->withConfiguredRule(
        BlankLineBeforeStatementFixer::class,
        ['statements' => ['throw', 'try', 'if', 'for', 'foreach', 'while', 'switch', 'return']]
    )
    ->withConfiguredRule(LineLengthFixer::class, ['line_length' => 120, 'break_long_lines' => true])
;
