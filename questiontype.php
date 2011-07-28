<?php  // $Id: questiontype.php,v 1.1 2009/06/23 13:39:49 arborrow Exp $

/////////////////
///   FLASH   ///
/////////////////

// Recoded from true/false question type

require_once($CFG->libdir . '/questionlib.php');

/**
 * @package questionbank
 * @subpackage questiontypes
 */

/// QUESTION TYPE CLASS //////////////////
class question_flash_qtype extends default_questiontype {

    function name() {
        return 'flash';
    }

    function save_question_options($question) {
        if ($options = get_record('question_flash', 'question', $question->id)) {
            //$options->question = $question->id;
            $options->width = $question->flashwidth;
            $options->height = $question->flashheight;
            @$options->optionalfile = $question->optionalfile;
            $options->optionaldata = $question->optionaldata;
            if (!update_record('question_flash', $options)) {
                $result->error = "Could not update quiz flash options! (id=$options->id)";
                return $result;
            }
        } else {
            unset($options);
            $options->question = $question->id;
            $options->width = $question->flashwidth;
            $options->height = $question->flashheight;
            @$options->optionalfile = $question->optionalfile;
            $options->optionaldata = $question->optionaldata;
            if (!insert_record('question_flash', $options)) {
                $result->error = 'Could not insert quiz flash options!';
                return $result;
            }
        }
        return true;
    }

    /**
    * Loads the question type specific options for the question.
    */
    function get_question_options(&$question) {
        // Get additional information from database
        // and attach it to the question object
        if (!$options = get_record('question_flash', 'question', $question->id)) {
            return false;
        }
        $question->flashwidth = $options->width;
        $question->flashheight = $options->height;
        @$question->optionalfile = $options->optionalfile;
        @$question->optionaldata = $options->optionaldata;
        // Load the answers
        $question->options->answers = get_records('question_answers', 'question', $question->id);

        return true;
    }

    /**
    * Deletes question from the question-type specific tables
    *
    * @return boolean Success/Failure
    * @param object $question  The question being deleted
//    */
//    function delete_question($questionid) {
//        delete_records("question_flash", "question", $questionid);
//        return true;
//    }

    function delete_states($stateslist) {
        /// The default question type does not have any tables of its own
        // therefore there is nothing to delete

    	delete_records_select('question_flash_states', "stateid IN ($stateslist)");
        return true;
    }

    function get_correct_responses(&$question, &$state) {
//        foreach ($question->options->answers as $answer) {
//            if (((int) $answer->fraction) === 1) {
//                return array('' => $answer->id);
//            }
//        }
		//$state->options->fillcorrect = true;
        //return array('' => '100%');
        return null;
    }

    // for moodle v1.9 and higher (dlnsk)
    function get_html_head_contributions(&$question, &$state) {
        global $CFG;
        // Load flash interface libraries
        require_js($CFG->wwwroot.'/question/type/flash/flash_tag.js');
        require_js($CFG->wwwroot.'/question/type/flash/interface.js');

        $contributions = parent::get_html_head_contributions($question, $state);

        return $contributions;
    }

    /**
    * Prints the main content of the question including any interactions
    */
    function print_question_formulation_and_controls(&$question, &$state,
            $cmoptions, $options) {
        global $CFG;

        $formatoptions = new stdClass;
        $formatoptions->noclean = true;
        $formatoptions->para = false;

        $qName = 'q'.$question->id;
        $readonly = $options->readonly ? '&qRO=1' : '';
        $adaptive = ($cmoptions->optionflags & QUESTION_ADAPTIVE) ?  '&qAM=1' : '';
        $fillcorrect = $options->correct_responses ? '&qFC=1' : '';
        if ($options->responses) {
        	$flashdata = !empty($state->options->flashdata) ? addslashes_js('&flData='.$state->options->flashdata) : '';
        }
		$description = !empty($state->options->answer) ? addslashes_js('&qDesc='.$state->options->answer) : '';
		$grade = '&qGr='.$state->raw_grade;

        $width  = $question->flashwidth;
        $height = $question->flashheight;
        $optionalfile = !empty($question->optionalfile) ? '&optFile='.get_file_url("{$cmoptions->course}/FlashQuestions/{$question->optionalfile}") : '';
        $optionaldata = !empty($question->optionaldata) ? addslashes_js('&optData='.$question->optionaldata) : '';
		
        // Print question formulation
        $questiontext = format_text($question->questiontext,
                         $question->questiontextformat,
                         $formatoptions, $cmoptions->course);
        $image = get_question_image($question, $cmoptions->course);

        include("$CFG->dirroot/question/type/flash/display.html");
    }

    function grade_responses(&$question, &$state, $cmoptions) {
        // Only allow one attempt at the question
        //$state->penalty = 0;
		if (isset($state->responses['grade'])) {
            $gr = (float)$state->responses['grade'];
            $gr = $gr < 0 ? 0 : $gr;
            $gr = $gr > 1 ? 1 : $gr;
			$state->raw_grade = $gr * $question->maxgrade;
		} 
        // mark the state as graded
        $state->event = ($state->event == QUESTION_EVENTCLOSE) ? QUESTION_EVENTCLOSEANDGRADE : QUESTION_EVENTGRADE;
        return true;
    }

    function response_summary($question, $state, $length=80) {
        if (isset($question->options->answers[$state->answer])) {
            $responses = $question->options->answers[$state->answer]->answer;
        } else {
            $responses = '';
        }
        return $responses;
    }

    function get_all_responses(&$question, &$state) {
    	
    	$result = parent::get_all_responses($question, $state);
    	foreach ($result->responses as $res) {
    		if ($res->credit == 1) {
    			return $result;
    		}
    	}
        $r = new stdClass;
        $r->answer = '100%';
        $r->credit = 1;
        $result->responses[0] = $r;
        return $result;
    }
    
    function get_actual_response($question, $state) {
       if (!empty($state->responses)) {
           $responses[] = $state->responses[''];
       } else {
           $responses[] = '';
       }
       return $responses;
    }
    
    function restore_session_and_responses(&$question, &$state) {
        if (!$options = get_record('question_flash_states', 'stateid', $state->id)) {
            return false;
        }
        $state->options->flashdata = $options->flashdata;
        $state->options->grade = $options->grade;
        $state->options->answer = $state->responses[''];
        $state->responses['flashdata'] = $options->flashdata;
        $state->responses['grade'] = $options->grade;
        return true;
    }
    
    function save_session_and_responses(&$question, &$state) {
        
        if (!set_field('question_states', 'answer', $state->responses[''], 'id', $state->id)) {
            return false;
        }
        $state->responses[''] = stripslashes($state->responses['']);
        if (!empty($state->responses['flashdata'])) {
        	$state->responses['flashdata'] = stripslashes($state->responses['flashdata']);
        }
        
		$options->stateid = $state->id;
        $options->flashdata = isset($state->responses['flashdata']) ? $state->responses['flashdata'] : '';
        $options->grade = isset($state->responses['grade']) ? $state->responses['grade'] : 0;
        $state->options = clone($options);
        // Only in this function we already know $state->id
        if ($options->id = get_field('question_flash_states', 'id', 'stateid', $state->id)) {
            if (!update_record('question_flash_states', $options)) {
                return false;
            }
        } else {
            if (!$options->id = insert_record('question_flash_states', $options)) {
                return false;
            }
        }

        if (!empty($state->responses[''])) {
            if (!$answer = get_record('question_answers', 'question', $question->id, 'answer', $state->responses[''])) {
                $answer->question = $question->id;
                $answer->answer = $state->responses[''];
                $answer->fraction = $options->grade;
                insert_record('question_answers', $answer);
            } else {
                $answer->fraction = $options->grade;
                update_record('question_answers', $answer);
            }
        }

        return true;
    }

//}

/// BACKUP FUNCTIONS ////////////////////////////

    /*
     * Backup the data in a truefalse question
     *
     * This is used in question/backuplib.php
     */
//    function backup($bf,$preferences,$question,$level=6) {
//
//        $status = true;
//
//        $truefalses = get_records("question_truefalse","question",$question,"id");
//        //If there are truefalses
//        if ($truefalses) {
//            //Iterate over each truefalse
//            foreach ($truefalses as $truefalse) {
//                $status = fwrite ($bf,start_tag("TRUEFALSE",$level,true));
//                //Print truefalse contents
//                fwrite ($bf,full_tag("TRUEANSWER",$level+1,false,$truefalse->trueanswer));
//                fwrite ($bf,full_tag("FALSEANSWER",$level+1,false,$truefalse->falseanswer));
//                $status = fwrite ($bf,end_tag("TRUEFALSE",$level,true));
//            }
//            //Now print question_answers
//            $status = question_backup_answers($bf,$preferences,$question);
//        }
//        return $status;
//    }

/// RESTORE FUNCTIONS /////////////////

    /*
     * Restores the data in the question
     *
     * This is used in question/restorelib.php
     */
//    function restore($old_question_id,$new_question_id,$info,$restore) {
//
//        $status = true;
//
//        //Get the truefalse array
//        $truefalses = $info['#']['TRUEFALSE'];
//
//        //Iterate over truefalse
//        for($i = 0; $i < sizeof($truefalses); $i++) {
//            $tru_info = $truefalses[$i];
//
//            //Now, build the question_truefalse record structure
//            $truefalse->question = $new_question_id;
//            $truefalse->trueanswer = backup_todb($tru_info['#']['TRUEANSWER']['0']['#']);
//            $truefalse->falseanswer = backup_todb($tru_info['#']['FALSEANSWER']['0']['#']);
//
//            ////We have to recode the trueanswer field
//            $answer = backup_getid($restore->backup_unique_code,"question_answers",$truefalse->trueanswer);
//            if ($answer) {
//                $truefalse->trueanswer = $answer->new_id;
//            }
//
//            ////We have to recode the falseanswer field
//            $answer = backup_getid($restore->backup_unique_code,"question_answers",$truefalse->falseanswer);
//            if ($answer) {
//                $truefalse->falseanswer = $answer->new_id;
//            }
//
//            //The structure is equal to the db, so insert the question_truefalse
//            $newid = insert_record ("question_truefalse",$truefalse);
//
//            //Do some output
//            if (($i+1) % 50 == 0) {
//                if (!defined('RESTORE_SILENTLY')) {
//                    echo ".";
//                    if (($i+1) % 1000 == 0) {
//                        echo "<br />";
//                    }
//                }
//                backup_flush(300);
//            }
//
//            if (!$newid) {
//                $status = false;
//            }
//        }
//
//        return $status;
//    }
//
//    function restore_recode_answer($state, $restore) {
//        $answer = backup_getid($restore->backup_unique_code,"question_answers",$state->answer);
//        if ($answer) {
//            return $answer->new_id;
//        } else {
//            echo 'Could not recode truefalse answer id '.$state->answer.' for state '.$oldid.'<br />';
//        }
//        return '';
//    }

}
//// END OF CLASS ////

//////////////////////////////////////////////////////////////////////////
//// INITIATION - Without this line the question type is not in use... ///
//////////////////////////////////////////////////////////////////////////
//$QTYPES['flash']= new question_flash_qtype();
// The following adds the questiontype to the menu of types shown to teachers
//$QTYPE_MENU['flash'] = get_string("flash", "quiz");

question_register_questiontype(new question_flash_qtype());
?>
