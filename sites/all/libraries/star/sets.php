<?php
/**
 * Functions for using arrays as sets.
 * For an array to "look like a set", it must:
 * 1. have no duplicate values
 * 2. have keys numbered from 0, 1, ...
 */

function set() {
	// creates a new set containing the values of the parameters:
	return array_to_set(func_get_args());
}

function array_to_set($arr) {
	// returns set of all unique values in the array $arr:
	return array_values(array_unique($arr));
}

function in($aa, $A) {
	// returns true if $aa in $A:
	// (alias for in_array)
	return is_array($A) && in_array($aa, $A);
}

function add_element(&$A, $bb) {
	// adds $bb to $A if $bb is not already in $A:
	// $A = $A union {$bb}
	// If you want to add an element without changing the input set, you can do this:
	//		$C = union($A, set($bb));
	// or alternatively:
	//		$C = $A; add_element($C, $bb);
	// NB: $A will look like a set at the end even if it doesn't at the start
	$C = array();
	$alreadyIn = false;
	foreach ($A as $aa) {
		if (!in($aa, $C))
			$C[] = $aa;
		if ($aa == $bb)
			$alreadyIn = true;
	}
	if (!$alreadyIn)
		$C[] = $bb;
	$A = $C;
}

function remove_element(&$A, $bb) {
	// removes $bb from $A if $bb is in $A:
	// $A = $A - {$bb}
	// If you want to remove an element without changing the input set, you can do this:
	//		$C = diff($A, set($bb));
	// or alternatively:
	//		$C = $A; remove_element($C, $bb);
	// NB: $A will look like a set at the end even if it doesn't at the start
	$C = array();
	foreach ($A as $aa) {
		if (!in($aa, $C) && $aa != $bb) {
			$C[] = $aa;
		}
	}
	$A = $C;
}

function union($A, $B) {
	// returns set with all unique values from both sets A and B:
	// $C = $A union $B
	// NB: the result will look like a set even if $A and $B don't
	$C = array();
	foreach ($A as $aa)
	{
		if (!in($aa, $C))
			$C[] = $aa;
	}
	foreach ($B as $bb)
	{
		if (!in($bb, $C))
			$C[] = $bb;
	}
	return $C;
}

function diff($A, $B) {
	// returns set with all values that are in $A but not in $B:
	// $C = $A - $B
	// NB: the result will look like a set even if $A and $B don't
	$C = array();
	foreach ($A as $aa)
	{
		if (!in($aa, $B) && !in($aa, $C))
			$C[] = $aa;
	}
	return $C;
}

function intersect($A, $B) {
	// returns set with all values common to both sets A and B:
	// $C = $A intersect $B
	// NB: the result will look like a set even if $A and $B don't
	$C = array();
	foreach ($A as $aa)
	{
		if (in($aa, $B) && !in($aa, $C))
			$C[] = $aa;
	}
	return $C;
}

function equal_sets($A, $B) {
	// returns true if $A is equal to $B (element order not important)
	// $A == $B
	// make sure they're both arrays:
	if (!is_array($A) || !is_array($B))
		return false;
	// convert both to sets in case they aren't:
	$A = array_to_set($A);
	$B = array_to_set($B);
	// check number of elements:
	if (count($A) != count($B))
		return false;
	// check elements match:
	foreach ($A as $aa)
	{
		if (!in($aa, $B))
			return false;
	}
	return true;
}

function subset($A, $B) {
	// return true if $A is a subset of $B, i.e. $B contains all elements in $A:
	// $A <= $B
	// make sure they're both arrays:
	if (!is_array($A) || !is_array($B))
		return false;
	$isSubset = true;
	foreach ($A as $aa)
	{
		if (!in($aa, $B))
		{
			$isSubset = false;
			break;
		}
	}
	return $isSubset;
}

function superset($A, $B) {
	// return true if $A is a superset of $B, i.e. $A contains all elements in $B:
	// $A >= $B
	// make sure they're both arrays:
	if (!is_array($A) || !is_array($B))
		return false;
	$isSuperset = true;
	foreach ($B as $bb)
	{
		if (!in($bb, $A))
		{
			$isSuperset = false;
			break;
		}
	}
	return $isSuperset;
}

function proper_subset($A, $B) {
	// returns true if $A is a proper subset of $B:
	// $A < $B
	// make sure they're both arrays:
	if (!is_array($A) || !is_array($B))
		return false;
	return subset($A, $B) && !equal_sets($A, $B);
}

function proper_superset($A, $B) {
	// returns true if $A is a proper superset of $B:
	// $A > $B
	// make sure they're both arrays:
	if (!is_array($A) || !is_array($B))
		return false;
	return superset($A, $B) && !equal_sets($A, $B);
}

function array_is_set($arr) {
	// returns true if the array looks like a set:
	// check it's an array:
	if (!is_array($arr))
		return false;
	$ok = true;
	$n = count($arr);
	$C = array();
	for ($i = 0; $i < $n; $i++)
	{
		if (!isset($arr[$i]) || in($arr[$i], $C))
		{
			$ok = false;
			break;
		}
		$C[$i] = $arr[$i];
	}
	return $ok;
}

// constants and array for bracket types, as used in set_to_string:
define("BRACKETS_NONE", 0);
define("BRACKETS_ROUND", 1);
define("BRACKETS_CURLY", 2);
define("BRACKETS_ANGLE", 3);
define("BRACKETS_SQUARE", 4);
$brackets = array(
	BRACKETS_NONE => array('', ''),
	BRACKETS_ROUND => array('(', ')'),
	BRACKETS_CURLY => array('{', '}'),
	BRACKETS_ANGLE => array('<', '>'),
	BRACKETS_SQUARE => array('[', ']'),
);

function set_to_string($A, $glue = ", ", $quoted = false, $bracketType = BRACKETS_NONE) {
	// creates $glue-seperated list from set elements:
	global $brackets;
	if ($quoted)
	{
		// put single-quotes around each element:
		foreach ($A as $i => $aa)
			$A[$i] = "'$aa'";
	}
	return $brackets[$bracketType][0].implode($glue, $A).$brackets[$bracketType][1];
}
