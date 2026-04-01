<?php
/**
 * Sniff to enforce the Categories header in WordPress block pattern files.
 *
 * Checks that pattern files contain a valid file-level docblock with a
 * "Categories:" property that includes at least one required category.
 */

namespace PatternCategory\Sniffs\Patterns;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class PatternCategorySniff implements Sniff {


	/**
	 * A required category that must appear in the Categories header.
	 *
	 * Set via the PHPCS ruleset configuration:
	 * <property name="base_category" value="my-base-category" />
	 *
	 * @var string
	 */
	public $base_category = '';

	/**
	 * A comma-separated list of allowed required categories.
	 *
	 * Set via the PHPCS ruleset configuration:
	 * <property name="base_categories" value="cat-one, cat-two" />
	 *
	 * If configured, the pattern must include at least one of these categories.
	 *
	 * @var string
	 */
	public $base_categories = '';

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array<int|string>
	 */
	public function register() {
		return [ T_OPEN_TAG ];
	}

	/**
	 * Processes this sniff when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack.
	 *
	 * @return int Return the end of the file to prevent further processing.
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		// Only process once per file — skip if this is not the first open tag.
		if ( $stackPtr !== 0 ) {
			return $phpcsFile->numTokens;
		}

		$tokens      = $phpcsFile->getTokens();
		$fileContent = $phpcsFile->getTokensAsString( 0, $phpcsFile->numTokens );

		// Look for the file-level docblock (/** ... */) near the top.
		if ( ! preg_match( '/\/\*\*.*?\*\//s', $fileContent, $docblock ) ) {
			$phpcsFile->addError(
				'Pattern file is missing a file-level docblock with pattern metadata (Title, Slug, Categories).',
				$stackPtr,
				'MissingDocblock'
			);
			return $phpcsFile->numTokens;
		}

		// Check for the Categories line.
		if ( ! preg_match( '/^\s*\*\s*Categories:\s*(.+)$/m', $docblock[0], $matches ) ) {
			$phpcsFile->addError(
				'Pattern file docblock is missing the "Categories" property.',
				$stackPtr,
				'MissingCategories'
			);
			return $phpcsFile->numTokens;
		}

		$categoriesRaw       = $matches[1];
		$categories          = array_filter( array_map( 'trim', explode( ',', $categoriesRaw ) ) );
		$normalizedCategories = array_map( 'strtolower', $categories );
		$requiredCategories   = $this->getRequiredCategories();

		// Check that at least one configured category is present.
		if ( [] !== $requiredCategories && [] === array_intersect( $requiredCategories, $normalizedCategories ) ) {
			$phpcsFile->addError(
				'Pattern file Categories must include at least one required category: %s. Found: %s',
				$stackPtr,
				'MissingBaseCategory',
				[ implode( ', ', $requiredCategories ), $categoriesRaw ]
			);
		}

		return $phpcsFile->numTokens;
	}

	/**
	 * Returns the configured required categories in normalized form.
	 *
	 * Supports both the legacy single-category property and the new
	 * comma-separated multi-category property.
	 *
	 * @return string[]
	 */
	private function getRequiredCategories() {
		$requiredCategories = [];

		if ( '' !== trim( $this->base_category ) ) {
			$requiredCategories[] = trim( strtolower( $this->base_category ) );
		}

		if ( '' !== trim( $this->base_categories ) ) {
			$requiredCategories = array_merge(
				$requiredCategories,
				array_filter(
					array_map(
						'trim',
						array_map( 'strtolower', explode( ',', $this->base_categories ) )
					)
				)
			);
		}

		return array_values( array_unique( $requiredCategories ) );
	}
}
