<?php
/**
 * Defines the editing form for the flash question type.
 *
 * @copyright 2007 Dmitry Pupinin
 * @author Dmitry Pupinin dlnsk@ngs.ru
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questions
 */
require_once($CFG->dirroot.'/question/type/edit_question_form.php');

/**
 * flash editing form definition.
 */
class question_edit_flash_form extends question_edit_form {
	
    function qtype() {
        return 'flash';
    }

    function definition_inner(&$mform) {
        $mform->addElement('filemanager', 'flashobject', get_string('flashobject', 'qtype_flash'), null,
            array('subdirs'=>0,
                  'maxfiles'=>1,
                  'accepted_types'=>array('.swf'),
                  'return_types'=>FILE_INTERNAL));
        $mform->addRule('flashobject', null, 'required', null, 'client');
        $mform->addElement('static', 'warning', '', get_string('flashwarning', 'qtype_flash'));

        $mform->addElement('text', 'flashwidth', get_string('flashwidth', 'qtype_flash'),
                array('size' => 4));
        $mform->setType('flashwidth', PARAM_INT);
        $mform->setDefault('flashwidth', 640);
        $mform->addRule('flashwidth', null, 'required', null, 'client');

        $mform->addElement('text', 'flashheight', get_string('flashheight', 'qtype_flash'),
                array('size' => 4));
        $mform->setType('flashheight', PARAM_INT);
        $mform->setDefault('flashheight', 480);
        $mform->addRule('flashheight', null, 'required', null, 'client');

        $mform->addElement('filemanager', 'optionalfile', get_string('optionalfile', 'qtype_flash'), null,
            array('subdirs'=>0,
                  'maxfiles'=>1,
                  'accepted_types'=>'*',
                  'return_types'=>FILE_INTERNAL));
        $mform->addHelpButton('optionalfile', 'optionalfile', 'qtype_flash');
        $mform->setAdvanced('optionalfile');

        $mform->addElement('textarea', 'optionaldata', get_string('optionaldata', 'qtype_flash'), 'wrap="virtual" rows="10" cols="45"');
        $mform->addHelpButton('optionaldata', 'optionaldata', 'qtype_flash');
        $mform->setAdvanced('optionaldata');
    }
    
/*    function definition() {
        global $COURSE, $CFG;

        $qtype = $this->qtype();
        $langfile = "qtype_$qtype";

        $mform =& $this->_form;

        // Standard fields at the start of the form.
        $mform->addElement('header', 'generalheader', get_string("general", 'form'));

        if (!isset($this->question->id)){
            //adding question
            $mform->addElement('questioncategory', 'category', get_string('category', 'quiz'),
                    array('contexts' => $this->contexts->having_cap('moodle/question:add')));
        } elseif (!($this->question->formoptions->canmove || $this->question->formoptions->cansaveasnew)){
            //editing question with no permission to move from category.
            $mform->addElement('questioncategory', 'category', get_string('category', 'quiz'),
                    array('contexts' => array($this->categorycontext)));
        } elseif ($this->question->formoptions->movecontext){
            //moving question to another context.
            $mform->addElement('questioncategory', 'categorymoveto', get_string('category', 'quiz'),
                    array('contexts' => $this->contexts->having_cap('moodle/question:add')));

        } else {
            //editing question with permission to move from category or save as new q
            $currentgrp = array();
            $currentgrp[0] =& $mform->createElement('questioncategory', 'category', get_string('categorycurrent', 'question'),
                    array('contexts' => array($this->categorycontext)));
            if ($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew){
                //not move only form
                $currentgrp[1] =& $mform->createElement('checkbox', 'usecurrentcat', '', get_string('categorycurrentuse', 'question'));
                $mform->setDefault('usecurrentcat', 1);
            }
            $currentgrp[0]->freeze();
            $currentgrp[0]->setPersistantFreeze(false);
            $mform->addGroup($currentgrp, 'currentgrp', get_string('categorycurrent', 'question'), null, false);

            $mform->addElement('questioncategory', 'categorymoveto', get_string('categorymoveto', 'question'),
                    array('contexts' => array($this->categorycontext)));
            if ($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew){
                //not move only form
                $mform->disabledIf('categorymoveto', 'usecurrentcat', 'checked');
            }
        }

        $mform->addElement('text', 'name', get_string('questionname', 'quiz'),
                array('size' => 50));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('htmleditor', 'questiontext', get_string('description', 'qtype_flash'),
                array('rows' => 15, 'course' => $this->coursefilesid));
        $mform->setType('questiontext', PARAM_RAW);
        $mform->setHelpButton('questiontext', array(array('questiontext', get_string('questiontext', 'quiz'), 'quiz'), 'richtext'), false, 'editorhelpbutton');
        $mform->addElement('format', 'questiontextformat', get_string('format'));

        // -------------------------------------------------------

        $mform->addElement('filemanager', 'flashobject', get_string('flashobject', 'qtype_flash'), null,
            array('subdirs'=>0,
                  'maxfiles'=>1,
                  'accepted_types'=>'*.swf',
                  'return_types'=>FILE_INTERNAL));
        $mform->addRule('flashobject', null, 'required', null, 'client');
        $mform->addElement('static', 'warning', '', get_string('flashwarning', 'qtype_flash'));
        $mform->setHelpButton('warning', array('flashinterface', get_string('interface', 'qtype_flash'), 'qtype_flash'));

        $mform->addElement('text', 'flashwidth', get_string('flashwidth', 'qtype_flash'),
                array('size' => 4));
        $mform->setType('flashwidth', PARAM_INT);
        $mform->setDefault('flashwidth', 640);
        $mform->addRule('flashwidth', null, 'required', null, 'client');

        $mform->addElement('text', 'flashheight', get_string('flashheight', 'qtype_flash'),
                array('size' => 4));
        $mform->setType('flashheight', PARAM_INT);
        $mform->setDefault('flashheight', 480);
        $mform->addRule('flashheight', null, 'required', null, 'client');

        $mform->addElement('filemanager', 'optionalfile', get_string('optionalfile', 'qtype_flash'), null,
            array('subdirs'=>0,
                  'maxfiles'=>1,
                  'accepted_types'=>'*.swf',
                  'return_types'=>FILE_INTERNAL));
        $mform->setHelpButton('optionalfile', array('optionalfile', get_string('optionalfile', 'qtype_flash'), 'qtype_flash'));
        $mform->setAdvanced('optionalfile');

        $mform->addElement('textarea', 'optionaldata', get_string('optionaldata', 'qtype_flash'), 'wrap="virtual" rows="10" cols="45"');
        $mform->setHelpButton('optionaldata', array('optionaldata', get_string('optionaldata', 'qtype_flash'), 'qtype_flash'));
        $mform->setAdvanced('optionaldata');

        // -------------------------------------------------------

        $mform->addElement('text', 'defaultgrade', get_string('defaultgrade', 'quiz'),
                array('size' => 3));
        $mform->setType('defaultgrade', PARAM_INT);
        $mform->setDefault('defaultgrade', 1);
        $mform->addRule('defaultgrade', null, 'required', null, 'client');

        $mform->addElement('text', 'penalty', get_string('penaltyfactor', 'quiz'),
                array('size' => 3));
        $mform->setType('penalty', PARAM_NUMBER);
        $mform->addRule('penalty', null, 'required', null, 'client');
        $mform->setHelpButton('penalty', array('penalty', get_string('penalty', 'quiz'), 'quiz'));
        $mform->setDefault('penalty', 0.1);

        $mform->addElement('htmleditor', 'generalfeedback', get_string('generalfeedback', 'quiz'),
                array('rows' => 10, 'course' => $this->coursefilesid));
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->setHelpButton('generalfeedback', array('generalfeedback', get_string('generalfeedback', 'quiz'), 'quiz'));

        // Any questiontype specific fields.
        $this->definition_inner($mform);

        if (!empty($this->question->id)){
            $mform->addElement('header', 'createdmodifiedheader', get_string('createdmodifiedheader', 'question'));
            $a = new object();
            if (!empty($this->question->createdby)){
                $a->time = userdate($this->question->timecreated);
                $a->user = fullname(get_record('user', 'id', $this->question->createdby));
            } else {
                $a->time = get_string('unknown', 'question');
                $a->user = get_string('unknown', 'question');
            }
            $mform->addElement('static', 'created', get_string('created', 'question'), get_string('byandon', 'question', $a));
            if (!empty($this->question->modifiedby)){
                $a = new object();
                $a->time = userdate($this->question->timemodified);
                $a->user = fullname(get_record('user', 'id', $this->question->modifiedby));
                $mform->addElement('static', 'modified', get_string('modified', 'question'), get_string('byandon', 'question', $a));
            }
        }

        // Standard fields at the end of the form.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);

        $mform->addElement('hidden', 'inpopup');
        $mform->setType('inpopup', PARAM_INT);

        $mform->addElement('hidden', 'versioning');
        $mform->setType('versioning', PARAM_BOOL);

        $mform->addElement('hidden', 'movecontext');
        $mform->setType('movecontext', PARAM_BOOL);

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->setDefault('cmid', 0);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', 0);

        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_LOCALURL);
        $mform->setDefault('returnurl', 0);

        $buttonarray = array();
        if (!empty($this->question->id)){
            //editing / moving question
            if ($this->question->formoptions->movecontext){
                $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('moveq', 'question'));
            } elseif ($this->question->formoptions->canedit || $this->question->formoptions->canmove ||$this->question->formoptions->movecontext){
                $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
            }
            if ($this->question->formoptions->cansaveasnew){
                $buttonarray[] = &$mform->createElement('submit', 'makecopy', get_string('makecopy', 'quiz'));
            }
            $buttonarray[] = &$mform->createElement('cancel');
        } else {
            // adding new question
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
            $buttonarray[] = &$mform->createElement('cancel');
        }
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        if ($this->question->formoptions->movecontext){
            $mform->hardFreezeAllVisibleExcept(array('categorymoveto', 'buttonar'));
        } elseif ((!empty($this->question->id)) && (!($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew))){
            $mform->hardFreezeAllVisibleExcept(array('categorymoveto', 'buttonar', 'currentgrp'));
        }
    }*/

}
?>
