<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * YiiRequirementChecker allows checking, if current system meets the requirements for running the application.
 *
 * @property array|null $result the check results.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class YiiRequirementChecker
{
	function check($requirements)
	{
		if (!is_array($requirements)) {
			$this->usageError("Requirements must be an array!");
		}
		$summary = array(
			'total' => 0,
			'errors' => 0,
			'warnings' => 0,
		);
		foreach ($requirements as $key => $rawRequirement) {
			$requirement = $this->normalizeRequirement($rawRequirement, $key);

			$summary['total']++;
			if (!$requirement['condition']) {
				if ($requirement['mandatory']) {
					$requirement['error'] = true;
					$requirement['warning'] = true;
					$summary['errors']++;
				} else {
					$requirement['error'] = false;
					$requirement['warning'] = true;
					$summary['warnings']++;
				}
			} else {
				$requirement['error'] = false;
				$requirement['warning'] = false;
			}
			$requirements[$key] = $requirement;
		}
		$result = array(
			'summary' => $summary,
			'requirements' => $requirements,
		);
		$this->result = $result;
		return $this;
	}

	/**
	 * Return the check results.
	 * @return array|null check results.
	 */
	function getResult()
	{
		if (isset($this->result)) {
			return $this->result;
		} else {
			return null;
		}
	}

	/**
	 * Renders the requirements check result.
	 * The output will vary depending is a script running from web or from console.
	 */
	function render()
	{
		if (isset($this->result)) {
			$this->usageError('Nothing to render!');
		}
		// @todo render
	}

	/**
	 * Normalizes requirement ensuring it has correct format.
	 * @param array $requirement raw requirement.
	 * @param int $requirementKey requirement key in the list.
	 * @return array normalized requirement.
	 */
	function normalizeRequirement($requirement, $requirementKey=0)
	{
		if (!is_array($requirement)) {
			$this->usageError('Requirement must be an array!');
		}
		if (!array_key_exists('condition', $requirement)) {
			$this->usageError("Requirement '{$requirementKey}' has no condition!");
		} else {
			$evalPrefix = 'eval:';
			if (is_string($requirement['condition']) && strpos($requirement['condition'], $evalPrefix)===0) {
				$expression = substr($requirement['condition'], strlen($evalPrefix));
				$requirement['condition'] = $this->evaluateExpression($expression);
			}
		}
		if (!array_key_exists('name', $requirement)) {
			$requirement['name'] = is_numeric($requirementKey) ? 'Requirement #'.$requirementKey : $requirementKey;
		}
		if (!array_key_exists('mandatory', $requirement)) {
			if (array_key_exists('required', $requirement)) {
				$requirement['mandatory'] = $requirement['required'];
			} else {
				$requirement['mandatory'] = false;
			}
		}
		if (!array_key_exists('by', $requirement)) {
			$requirement['by'] = 'Unknown';
		}
		if (!array_key_exists('memo', $requirement)) {
			$requirement['memo'] = '';
		}
		return $requirement;
	}

	/**
	 * Displays a usage error.
	 * This method will then terminate the execution of the current application.
	 * @param string $message the error message
	 */
	function usageError($message)
	{
		echo "Error: $message\n\n";
		exit(1);
	}

	/**
	 * Evaluates a PHP expression under the context of this class.
	 * @param string $expression a PHP expression to be evaluated.
	 * @return mixed the expression result.
	 */
	function evaluateExpression($expression)
	{
		return eval('return '.$expression.';');
	}
}
