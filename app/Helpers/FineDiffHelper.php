<?php

namespace App\Helpers;

abstract class FineDiffOp 
{
	abstract public function getFromLen();
	abstract public function getToLen();
	abstract public function getOpcode();
}

class FineDiffDeleteOp extends FineDiffOp 
{

    public function __construct($len) 
    {
		$this->fromLen = $len;
	}

	public function getFromLen() 
    {
		return $this->fromLen;
	}

	public function getToLen() 
    {
		return 0;
	}

	public function getOpcode() 
    {
		if ( $this->fromLen === 1 ) 
        {
			return 'ddd';
		}
		return "ddd{$this->fromLen}";
	}
}

class FineDiffInsertOp extends FineDiffOp
{

	public function __construct($text) 
    {
		$this->text = $text;
	}

	public function getFromLen() 
    {
		return 0;
	}

	public function getToLen() 
    {
		return strlen($this->text);
	}

	public function getText() 
    {
		return $this->text;
	}

	public function getOpcode() {
		$to_len = strlen($this->text);
		if ( $to_len === 1 )
        {
			return "iii:::{$this->text}";
		}
		return "iii{$to_len}:::{$this->text}";
	}
}

class FineDiffReplaceOp extends FineDiffOp 
{
	
    public function __construct($fromLen, $text) 
    {
		$this->fromLen = $fromLen;
		$this->text = $text;
	}

	public function getFromLen()
    {
		return $this->fromLen;
	}

	public function getToLen()
    {
		return strlen($this->text);
	}

	public function getText()
    {
		return $this->text;
	}

	public function getOpcode()
    {
		if ( $this->fromLen === 1 ) 
        {
			$del_opcode = 'ddd';
		}
		else
        {
			$del_opcode = "ddd{$this->fromLen}";
		}

		$to_len = strlen($this->text);
		if ( $to_len === 1 )
        {
			return "{$del_opcode}iii:::{$this->text}";
		}
		return "{$del_opcode}iii{$to_len}:::{$this->text}";
	}
}

class FineDiffCopyOp extends FineDiffOp
{
	
    public function __construct($len)
    {
		$this->len = $len;
	}

	public function getFromLen()
    {
		return $this->len;
	}

	public function getToLen()
    {
		return $this->len;
	}

	public function getOpcode()
    {
		if ( $this->len === 1 )
        {
			return 'ccc';
		}
		return "ccc{$this->len}";
	}

	public function increase($size)
    {
		return $this->len += $size;
	}
}


/**
* FineDiff ops
*
* Collection of ops
*/
class FineDiffOps 
{
	public function appendOpcode($opcode, $from, $from_offset, $from_len)
    {
		if ( $opcode === 'ccc' )
        {
			$edits[] = new FineDiffCopyOp($from_len);
		}
		else if ( $opcode === 'ddd' ) 
        {
			$edits[] = new FineDiffDeleteOp($from_len);
		}
		else /* if ( $opcode === 'iii' ) */
        {
			$edits[] = new FineDiffInsertOp(substr($from, $from_offset, $from_len));
		}
	}

	public $edits = array();
}

/**
* FineDiff class
*
* TODO: Document
*
*/
class FineDiffHelper
{

	/**------------------------------------------------------------------------
	*
	* Public section
	*
	*/

	/**
	* Constructor
	* ...
	* The $granularityStack allows FineDiff to be configurable so that
	* a particular stack tailored to the specific content of a document can
	* be passed.
	*/
	public function __construct($from_text = '', $to_text = '', $granularityStack = null)
    {
		// setup stack for generic text documents by default
		$this->granularityStack = $granularityStack ? $granularityStack : FineDiffHelper::$characterGranularity;
		$this->edits = array();
		$this->from_text = $from_text;
		$this->doDiff($from_text, $to_text);
	}

	public function getOps()
    {
		return $this->edits;
	}

	public function getOpcodes()
    {
		$opcodes = array();
		foreach ( $this->edits as $edit ) {
			$opcodes[] = $edit->getOpcode();
			}
		return implode('', $opcodes);
	}

	public function renderDiffToHTML()
    {
		$in_offset = 0;
		ob_start();
		foreach ( $this->edits as $edit )
        {
			$n = $edit->getFromLen();
			if ( $edit instanceof FineDiffCopyOp )
            {
				FineDiffHelper::renderDiffToHTMLFromOpcode('ccc', $this->from_text, $in_offset, $n);
			}
			else if ( $edit instanceof FineDiffDeleteOp )
            {
				FineDiffHelper::renderDiffToHTMLFromOpcode('ddd', $this->from_text, $in_offset, $n);
			}
			else if ( $edit instanceof FineDiffInsertOp )
            {
				FineDiffHelper::renderDiffToHTMLFromOpcode('iii', $edit->getText(), 0, $edit->getToLen());
			}
			else /* if ( $edit instanceof FineDiffReplaceOp ) */
            {
				FineDiffHelper::renderDiffToHTMLFromOpcode('ddd', $this->from_text, $in_offset, $n);
				FineDiffHelper::renderDiffToHTMLFromOpcode('iii', $edit->getText(), 0, $edit->getToLen());
			}
			$in_offset += $n;
		}
		return ob_get_clean();
	}

	/**------------------------------------------------------------------------
	* Return an opcodes string describing the diff between a "From" and a
	* "To" string
	*/
	public static function getDiffOpcodes($from, $to, $granularities = null)
    {
		$diff = new FineDiffHelper($from, $to, $granularities);
		return $diff->getOpcodes();
	}

	/**------------------------------------------------------------------------
	* Return an iterable collection of diff ops from an opcodes string
	*/
	public static function getDiffOpsFromOpcodes($opcodes)
    {
		$diffops = new FineDiffOps();
		FineDiffHelper::renderFromOpcodes(null, $opcodes, array($diffops,'appendOpcode'));
		return $diffops->edits;
	}

	/**------------------------------------------------------------------------
	* Re-create the "To" string from the "From" string and an "Opcodes" string
	*/
	public static function renderToTextFromOpcodes($from, $opcodes)
    {
		ob_start();
		FineDiffHelper::renderFromOpcodes($from, $opcodes, array('FineDiffHelper','renderToTextFromOpcode'));
		return ob_get_clean();
	}

	/**------------------------------------------------------------------------
	* Render the diff to an HTML string
	*/
	public static function renderDiffToHTMLFromOpcodes($from, $opcodes)
    {
		ob_start();
		FineDiffHelper::renderFromOpcodes($from, $opcodes, array('FineDiffHelper','renderDiffToHTMLFromOpcode'));
		return ob_get_clean();
	}

	/**------------------------------------------------------------------------
	* Generic opcodes parser, user must supply callback for handling
	* single opcode
	*/
	public static function renderFromOpcodes($from, $opcodes, $callback)
    {
		if ( !is_callable($callback) )
        {
			return;
		}
		$opcodes_len = strlen($opcodes);
		$from_offset = $opcodes_offset = 0;
		while ( $opcodes_offset <  $opcodes_len )
        {
			$opcode = substr($opcodes, $opcodes_offset, 1);
			$opcodes_offset++;
			$n = intval(substr($opcodes, $opcodes_offset));
			if ( $n )
            {
				$opcodes_offset += strlen(strval($n));
			}
			else
            {
				$n = 1;
			}
			if ( $opcode === 'ccc' )
            { // copy n characters from source
				call_user_func($callback, 'ccc', $from, $from_offset, $n, '');
				$from_offset += $n;
			}
			else if ( $opcode === 'ddd' )
            { // delete n characters from source
				call_user_func($callback, 'ddd', $from, $from_offset, $n, '');
				$from_offset += $n;
			}
			else /* if ( $opcode === 'iii' ) */
            { // insert n characters from opcodes
				call_user_func($callback, 'iii', $opcodes, $opcodes_offset + 1, $n);
				$opcodes_offset += 1 + $n;
			}
		}
	}

	/**
	* Stock granularity stacks and delimiters
	*/

	const paragraphDelimiters = "\n\r";
	public static $paragraphGranularity = [
		FineDiffHelper::paragraphDelimiters
    ];
	const sentenceDelimiters = ".\n\r";
	public static $sentenceGranularity = [
		FineDiffHelper::paragraphDelimiters,
		FineDiffHelper::sentenceDelimiters
    ];
	const wordDelimiters = " \t.\n\r";
	public static $wordGranularity = [
		FineDiffHelper::paragraphDelimiters,
		FineDiffHelper::sentenceDelimiters,
		FineDiffHelper::wordDelimiters
    ];
	const characterDelimiters = "";

	public static $characterGranularity = [
		FineDiffHelper::paragraphDelimiters,
		FineDiffHelper::sentenceDelimiters,
		FineDiffHelper::wordDelimiters,
		FineDiffHelper::characterDelimiters
    ];

	public static $textStack = [
		".",
		" \t.\n\r",
		""
    ];

	/**------------------------------------------------------------------------
	*
	* Private section
	*
	*/

	/**
	* Entry point to compute the diff.
	*/
	private function doDiff($from_text, $to_text)
    {
		$this->last_edit = false;
		$this->stackpointer = 0;
		$this->from_text = $from_text;
		$this->from_offset = 0;
		// can't diff without at least one granularity specifier
		if ( empty($this->granularityStack) )
        {
			return;
		}
		$this->_processGranularity($from_text, $to_text);
	}

	/**
	* This is the recursive function which is responsible for
	* handling/increasing granularity.
	*
	* Incrementally increasing the granularity is key to compute the
	* overall diff in a very efficient way.
	*/
	private function _processGranularity($from_segment, $to_segment)
    {
		$delimiters = $this->granularityStack[$this->stackpointer++];
		$has_next_stage = $this->stackpointer < count($this->granularityStack);
		foreach ( FineDiffHelper::doFragmentDiff($from_segment, $to_segment, $delimiters) as $fragment_edit )
        {
			// increase granularity
			if ( $fragment_edit instanceof FineDiffReplaceOp && $has_next_stage )
            {
				$this->_processGranularity(
					substr($this->from_text, $this->from_offset, $fragment_edit->getFromLen()),
					$fragment_edit->getText()
					);
			}
			// fuse copy ops whenever possible
			else if ( $fragment_edit instanceof FineDiffCopyOp && $this->last_edit instanceof FineDiffCopyOp )
            {
				$this->edits[count($this->edits)-1]->increase($fragment_edit->getFromLen());
				$this->from_offset += $fragment_edit->getFromLen();
			}
			else
            {
				/* $fragment_edit instanceof FineDiffCopyOp */
				/* $fragment_edit instanceof FineDiffDeleteOp */
				/* $fragment_edit instanceof FineDiffInsertOp */
				$this->edits[] = $this->last_edit = $fragment_edit;
				$this->from_offset += $fragment_edit->getFromLen();
			}
		}
		$this->stackpointer--;
	}

	/**
	* This is the core algorithm which actually perform the diff itself,
	* fragmenting the strings as per specified delimiters.
	*
	* This function is naturally recursive, however for performance purpose
	* a local job queue is used instead of outright recursivity.
	*/
	private static function doFragmentDiff($from_text, $to_text, $delimiters)
    {
		// Empty delimiter means character-level diffing.
		// In such case, use code path optimized for character-level
		// diffing.
		if ( empty($delimiters) )
        {
			return FineDiffHelper::doCharDiff($from_text, $to_text);
		}

		$result = array();

		// fragment-level diffing
		$from_text_len = strlen($from_text);
		$to_text_len = strlen($to_text);
		$from_fragments = FineDiffHelper::extractFragments($from_text, $delimiters);
		$to_fragments = FineDiffHelper::extractFragments($to_text, $delimiters);

		$jobs = array(array(0, $from_text_len, 0, $to_text_len));

		$cached_array_keys = array();

		while ( $job = array_pop($jobs) )
        {

			// get the segments which must be diff'ed
			list($from_segment_start, $from_segment_end, $to_segment_start, $to_segment_end) = $job;

			// catch easy cases first
			$from_segment_length = $from_segment_end - $from_segment_start;
			$to_segment_length = $to_segment_end - $to_segment_start;
			if ( !$from_segment_length || !$to_segment_length )
            {
				if ( $from_segment_length )
                {
					$result[$from_segment_start * 4] = new FineDiffDeleteOp($from_segment_length);
				}
				else if ( $to_segment_length )
                {
					$result[$from_segment_start * 4 + 1] = new FineDiffInsertOp(substr($to_text, $to_segment_start, $to_segment_length));
				}
				continue;
			}

			// find longest copy operation for the current segments
			$best_copy_length = 0;

			$from_base_fragment_index = $from_segment_start;

			$cached_array_keys_for_current_segment = array();

			while ( $from_base_fragment_index < $from_segment_end )
            {
				$from_base_fragment = $from_fragments[$from_base_fragment_index];
				$from_base_fragment_length = strlen($from_base_fragment);
				// performance boost: cache array keys
				if ( !isset($cached_array_keys_for_current_segment[$from_base_fragment]) )
                {
					if ( !isset($cached_array_keys[$from_base_fragment]) )
                    {
						$to_all_fragment_indices = $cached_array_keys[$from_base_fragment] = array_keys($to_fragments, $from_base_fragment, true);
					}
					else
                    {
						$to_all_fragment_indices = $cached_array_keys[$from_base_fragment];
					}
					// get only indices which falls within current segment
					if ( $to_segment_start > 0 || $to_segment_end < $to_text_len )
                    {
						$to_fragment_indices = array();
						foreach ( $to_all_fragment_indices as $to_fragment_index )
                        {
							if ( $to_fragment_index < $to_segment_start ) { continue; }
							if ( $to_fragment_index >= $to_segment_end ) { break; }
							$to_fragment_indices[] = $to_fragment_index;
						}
						$cached_array_keys_for_current_segment[$from_base_fragment] = $to_fragment_indices;
					}
					else
                    {
						$to_fragment_indices = $to_all_fragment_indices;
					}
				}
				else
                {
					$to_fragment_indices = $cached_array_keys_for_current_segment[$from_base_fragment];
				}
				// iterate through collected indices
				foreach ( $to_fragment_indices as $to_base_fragment_index ) 
                {
					$fragment_index_offset = $from_base_fragment_length;
					// iterate until no more match
					for (;;) 
                    {
						$fragment_from_index = $from_base_fragment_index + $fragment_index_offset;
						if ( $fragment_from_index >= $from_segment_end )
                        {
							break;
						}
						$fragment_to_index = $to_base_fragment_index + $fragment_index_offset;
						if ( $fragment_to_index >= $to_segment_end )
                        {
							break;
						}
						if ( $from_fragments[$fragment_from_index] !== $to_fragments[$fragment_to_index] )
                        {
							break;
						}
						$fragment_length = strlen($from_fragments[$fragment_from_index]);
						$fragment_index_offset += $fragment_length;
					}
					if ( $fragment_index_offset > $best_copy_length )
                    {
						$best_copy_length = $fragment_index_offset;
						$best_from_start = $from_base_fragment_index;
						$best_to_start = $to_base_fragment_index;
					}
				}
				$from_base_fragment_index += strlen($from_base_fragment);
				// If match is larger than half segment size, no point trying to find better
				// TODO: Really?
				if ( $best_copy_length >= $from_segment_length / 2)
                {
					break;
				}
				// no point to keep looking if what is left is less than
				// current best match
				if ( $from_base_fragment_index + $best_copy_length >= $from_segment_end )
                {
					break;
				}
			}

			if ( $best_copy_length ) 
            {
				$jobs[] = array($from_segment_start, $best_from_start, $to_segment_start, $best_to_start);
				$result[$best_from_start * 4 + 2] = new FineDiffCopyOp($best_copy_length);
				$jobs[] = array($best_from_start + $best_copy_length, $from_segment_end, $best_to_start + $best_copy_length, $to_segment_end);
			}
			else
            {
				$result[$from_segment_start * 4 ] = new FineDiffReplaceOp($from_segment_length, substr($to_text, $to_segment_start, $to_segment_length));
			}
		}

		ksort($result, SORT_NUMERIC);
		return array_values($result);
	}

	/**
	* Perform a character-level diff.
	*
	* The algorithm is quite similar to doFragmentDiff(), except that
	* the code path is optimized for character-level diff -- strpos() is
	* used to find out the longest common subequence of characters.
	*
	* We try to find a match using the longest possible subsequence, which
	* is at most the length of the shortest of the two strings, then incrementally
	* reduce the size until a match is found.
	*
	* I still need to study more the performance of this function. It
	* appears that for long strings, the generic doFragmentDiff() is more
	* performant. For word-sized strings, doCharDiff() is somewhat more
	* performant.
	*/
	private static function doCharDiff($from_text, $to_text)
    {
		$result = array();
		$jobs = array(array(0, strlen($from_text), 0, strlen($to_text)));

		while ( $job = array_pop($jobs) )
        {
			// get the segments which must be diff'ed
			list($from_segment_start, $from_segment_end, $to_segment_start, $to_segment_end) = $job;
			$from_segment_len = $from_segment_end - $from_segment_start;
			$to_segment_len = $to_segment_end - $to_segment_start;

			// catch easy cases first
			if ( !$from_segment_len || !$to_segment_len )
            {
				if ( $from_segment_len )
                {
					$result[$from_segment_start * 4 + 0] = new FineDiffDeleteOp($from_segment_len);
				}
				else if ( $to_segment_len )
                {
					$result[$from_segment_start * 4 + 1] = new FineDiffInsertOp(substr($to_text, $to_segment_start, $to_segment_len));
				}
				continue;
			}
			if ( $from_segment_len >= $to_segment_len )
            {
				$copy_len = $to_segment_len;
				while ( $copy_len )
                {
					$to_copy_start = $to_segment_start;
					$to_copy_start_max = $to_segment_end - $copy_len;
					while ( $to_copy_start <= $to_copy_start_max )
                    {
						$from_copy_start = strpos(substr($from_text, $from_segment_start, $from_segment_len), substr($to_text, $to_copy_start, $copy_len));
						if ( $from_copy_start !== false )
                        {
							$from_copy_start += $from_segment_start;
							break 2;
						}
						$to_copy_start++;
					}
					$copy_len--;
				}
			}
			else 
            {
				$copy_len = $from_segment_len;
				while ( $copy_len )
                {
					$from_copy_start = $from_segment_start;
					$from_copy_start_max = $from_segment_end - $copy_len;
					while ( $from_copy_start <= $from_copy_start_max )
                    {
						$to_copy_start = strpos(substr($to_text, $to_segment_start, $to_segment_len), substr($from_text, $from_copy_start, $copy_len));
						if ( $to_copy_start !== false )
                        {
							$to_copy_start += $to_segment_start;
							break 2;
						}
						$from_copy_start++;
					}
					$copy_len--;
				}
			}
			// match found
			if ( $copy_len )
            {
				$jobs[] = array($from_segment_start, $from_copy_start, $to_segment_start, $to_copy_start);
				$result[$from_copy_start * 4 + 2] = new FineDiffCopyOp($copy_len);
				$jobs[] = array($from_copy_start + $copy_len, $from_segment_end, $to_copy_start + $copy_len, $to_segment_end);
			}
			// no match,  so delete all, insert all
			else
            {
				$result[$from_segment_start * 4] = new FineDiffReplaceOp($from_segment_len, substr($to_text, $to_segment_start, $to_segment_len));
			}
		}
		ksort($result, SORT_NUMERIC);
		return array_values($result);
	}

	/**
	* Efficiently fragment the text into an array according to
	* specified delimiters.
	* No delimiters means fragment into single character.
	* The array indices are the offset of the fragments into
	* the input string.
	* A sentinel empty fragment is always added at the end.
	* Careful: No check is performed as to the validity of the
	* delimiters.
	*/
	private static function extractFragments($text, $delimiters)
    {
		// special case: split into characters
		if ( empty($delimiters) )
        {
			$chars = str_split($text, 1);
			$chars[strlen($text)] = '';
			return $chars;
		}

		$fragments = array();
		$start = $end = 0;

		for (;;) 
        {
			$end += strcspn($text, $delimiters, $end);
			$end += strspn($text, $delimiters, $end);
			if ( $end === $start )
            {
				break;
			}
			$fragments[$start] = substr($text, $start, $end - $start);
			$start = $end;
		}
		$fragments[$start] = '';
		return $fragments;
	}

	/**
	* Stock opcode renderers
	*/
	private static function renderToTextFromOpcode($opcode, $from, $from_offset, $from_len)
    {
		if ( $opcode === 'ccc' || $opcode === 'iii' )
        {
			echo substr($from, $from_offset, $from_len);
		}
	}

	private static function renderDiffToHTMLFromOpcode($opcode, $from, $from_offset, $from_len)
    {
		if ( $opcode === 'ccc' )
        {
			echo htmlentities(substr($from, $from_offset, $from_len));
		}
		else if ( $opcode === 'ddd' )
        {
			$deletion = substr($from, $from_offset, $from_len);
			if ( strcspn($deletion, " \n\r") === 0 )
            {
				$deletion = str_replace(array("\n","\r"), array('\n','\r'), $deletion);
			}
			echo '<del>', htmlentities($deletion), '</del>';
		}
		else /* if ( $opcode === 'iii' ) */ 
        {
 			echo '<ins>', htmlentities(substr($from, $from_offset, $from_len)), '</ins>';
		}
	}
}