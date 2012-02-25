<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * The main quickcontent configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package   mod_quickcontent
 * @copyright 2012 Iain Checkland
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
$PAGE->requires->js('/mod/quickcontent/quickcontent.js');   
$PAGE->requires->css('/mod/quickcontent/quickcontent.css');   
$PAGE->requires->js_init_call('embed_preview',array($CFG->wwwroot)); 

class mod_quickcontent_mod_form extends moodleform_mod {

    function definition() {

        global $COURSE,$DB,$CFG;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
        //dbg();
        $sizoptions = array(    -1=>get_string('link', 'quickcontent'),
                                100 => get_string('xxsmall', 'quickcontent'), 
                                200 => get_string('xsmall', 'quickcontent'), 
                                300 => get_string('small', 'quickcontent'), 
                                400 => get_string('medium', 'quickcontent'), 
                                500 => get_string('large', 'quickcontent'), 
                                600 => get_string('xlarge', 'quickcontent'), 
                                800 => get_string('xxlarge', 'quickcontent'), 
                                1000 => get_string('xxxlarge', 'quickcontent'), 
                                1200 => get_string('huge', 'quickcontent'), 
                                0 => get_string('noresize', 'quickcontent'));

        $mform->addElement('html',"<p>
                                    <a class='sectlink' id='heading'>" . get_string('heading', 'quickcontent') . "</a> - 
                                    <a class='sectlink' id='instructions'>" . get_string('instructions', 'quickcontent') . "</a> - 
                                    <a class='sectlink' id='fileupload'>" . get_string('fileupload', 'quickcontent') ."</a> - 
                                    <a class='sectlink' id='imageupload'>" . get_string('imageupload', 'quickcontent') ."</a> - 
                                    <a class='sectlink' id='mediaupload'>" . get_string('mediaupload', 'quickcontent') ."</a> - 
                                    <a class='sectlink' id='embed'>" . get_string('embedcontent', 'quickcontent') . "</a> -  
                                    <a class='sectlink' id='forum'>" . get_string('forum', 'quickcontent') . "</a> - 
                                    <a class='sectlink' id='filedropbox'>" . get_string('filedropbox', 'quickcontent') . "</a> - 
                                    <a class='sectlink' id='urldropbox'>" . get_string('urldropbox', 'quickcontent') . "</a>
                                   </p>"); 
                                   
//-------------------------------------------------------------------------------

        $mform->addElement('header', 'sectheading', "<h2><a name='heading'></a>" . get_string('heading', 'quickcontent') . "</h2>");
        $mform->addElement('text', 'heading', '<b>' . get_string('heading', 'quickcontent') . '</b>', array('size'=>'32'));
        $mform->setType('heading', PARAM_RAW);

        $options=array(1=>'xxlarge',2=>'xlarge',3=>'large',4=>'medium',5=>'small',6=>'xsmall');
        $mform->addElement('select', 'headingsize', get_string('headingsize','quickcontent'), $options); 
        $mform->setType('headingsize', PARAM_INT);
        $mform->setAdvanced('headingsize');
        $mform->setDefault('headingsize', 2);

        $mform->addElement('submit', 'submitheading', get_string('submitheading', 'quickcontent'));
        
        $mform->addElement('html','<hr />');      

//-------------------------------------------------------------------------------

        $mform->addElement('header', 'sectinstructions', "<h2><a name='instructions'></a>" . get_string('instructions', 'quickcontent') . "</h2>");

        $mform->addElement('editor', 'instructions', '<b>' . get_string('instructions', 'quickcontent') . '</b>', null, array('height'=>'640px','maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->context));
        $mform->setType('instructions', PARAM_RAW); // no XSS prevention here, users must be trusted
        
        $mform->addElement('submit', 'submitinstructions', get_string('submitinstructions', 'quickcontent'));
        
        $mform->addElement('html','<hr />');      

//-------------------------------------------------------------------------------

        $mform->addElement('header', 'sectfileupload', "<h2><a name='fileupload'></a>" . get_string('fileupload', 'quickcontent') . "</h2>");
    
        $filemanager_options = array();
        // 3 == FILE_EXTERNAL & FILE_INTERNAL
        // These two constant names are defined in repository/lib.php
        $filemanager_options['return_types'] = 3;
        $filemanager_options['accepted_types'] = '*';
        $filemanager_options['maxbytes'] = 0;
        $filemanager_options['maxfiles'] = -1;
        $filemanager_options['mainfile'] = false;

        $mform->addElement('filemanager', 'files', get_string('selectfiles'), null, $filemanager_options);
        $mform->addElement('html','<br /><i>' . get_string('multiplefileuploadwarning','quickcontent'). '</i>');      

        $mform->addElement('submit', 'submitfileupload', get_string('submitfileupload', 'quickcontent'));
        
        $mform->addElement('html','<hr />');      

//-------------------------------------------------------------------------------
        // IMAGE upload - with sizing and proxy to activity
        
        $mform->addElement('header', 'sectimageupload',"<h2><a name='imageupload'></a>" . get_string('imageupload', 'quickcontent') . "</h2>");
        
        $filemanager_options = array();
        // 3 == FILE_EXTERNAL & FILE_INTERNAL
        // These two constant names are defined in repository/lib.php
        $filemanager_options['return_types'] = 3;
        $filemanager_options['accepted_types'] = array('.jpg','.jpeg','.gif','.bmp','.png');
        $filemanager_options['maxbytes'] = 0;
        $filemanager_options['maxfiles'] = 1;
        $filemanager_options['mainfile'] = false;

        $mform->addElement('filemanager', 'files', get_string('selectfiles'), null, $filemanager_options);
        
        $mform->addElement('select', 'imageuploadwidth', get_string('imagesizing', 'quickcontent'), $sizoptions, array('id'=>'imageuploadwidth'));
        $mform->setDefault('imageuploadwidth', 400);

        $mods = $DB->get_records_sql("select t0.id,t1.name,t0.instance from {$CFG->prefix}course_modules t0, {$CFG->prefix}modules t1 where t0.module=t1.id and course={$COURSE->id} order by module,section");
        $m[0]='Select link if reqd ...';
        foreach($mods as $mod){
            $instname = $DB->get_record($mod->name,array('id'=>$mod->instance));
            if($mod->name!='label'){
                $m[$mod->id] = "[$mod->name] {$instname->name}"; 
            }
        }
        $mform->addElement('select', 'activity', get_string('linktoactivity', 'quickcontent'),$m);
        $mform->setDefault('activity', 0);
        $mform->setAdvanced('activity');

        $mform->addElement('submit', 'submitimageupload', get_string('submitimageupload', 'quickcontent'));
        
        $mform->addElement('html','<hr />');      

//-------------------------------------------------------------------------------

        // MEDIA - with embed/sizing option
        $mform->addElement('header', 'sectmediaupload',"<h2><a name='mediaupload'></a>" . get_string('mediaupload', 'quickcontent') . "</h2>");
        
        $filemanager_options = array();
        // 3 == FILE_EXTERNAL & FILE_INTERNAL
        // These two constant names are defined in repository/lib.php
        $filemanager_options['return_types'] = 3;
        $filemanager_options['accepted_types'] = array('audio','video');
        $filemanager_options['maxbytes'] = 0;
        $filemanager_options['maxfiles'] = 1;
        $filemanager_options['mainfile'] = false;

        $mform->addElement('filemanager', 'files', get_string('selectfiles'), null, $filemanager_options);
        
        $mform->addElement('select', 'mediawidth', get_string('embedsizing', 'quickcontent'), $sizoptions, array('id'=>'mediawidth'));
        $mform->setDefault('mediawidth', 400);
        
        $mform->addElement('submit', 'submitmediaupload', get_string('submitmediaupload', 'quickcontent'));
        
        $mform->addElement('html','<hr />');      

//-------------------------------------------------------------------------------    
        
        $mform->addElement('header', 'sectembed',"<h2><a name='embed'></a>". get_string('embedcontent', 'quickcontent') . "</h2>");
        
        $mform->addElement('text', 'url', get_string('url', 'quickcontent'), array(id=>'url','size'=>'64'));
        $mform->addRule('url', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('url', 'quickcontentname', 'quickcontent');
        
        $mform->addElement('select', 'prewidth', get_string('embedsizing', 'quickcontent'), $sizoptions, array(id=>'prewidth'));
        $mform->setDefault('prewidth', 400);
        
        $mform->addElement('button', 'preview_url', get_string('preview', 'quickcontent'), array(id=>'preview_url'));
        $mform->addElement('hidden', 'embedcode', '', array('id'=>'embedcode'));
        $mform->addElement('submit', 'submitembed', 'Submit Embed');
        
        $mform->addElement('html','<br/>Preview:<div id="ePreview"></div>');

        $mform->addElement('html','Examples:<br/><i>http://www.slideshare.net/weierophinney/best-practices-of-php-development-presentation</i>');
        $mform->addElement('html','<br/><i>http://www.bbc.co.uk</i>');
        $mform->addElement('html','<br/><i>https://sites.google.com/a/kgv.hk/1to1/Home</i>');
        $mform->addElement('html','<br/><i>http://vimeo.com/18150336</i>');
        $mform->addElement('html','<br/><i>http://www.flickr.com/photos/28634332@N05/4741770655/</i>');
        $mform->addElement('html','<br/><i>http://www.teachertube.com/viewVideo.php?video_id=30&title=Walkthroughs_and_Learning_Objectives</i>');
        $mform->addElement('html','<br/><i>http://www.youtube.com/watch?v=hI-BDR2UcmU</i>');
        $mform->addElement('html','<br/><div id="ePreview"></div>');

        
        $mform->addElement('html','<hr />');      

//-------------------------------------------------------------------------------
                
        $mform->addElement('header', 'sectfiledropbox',"<h2><a name='filedropbox'></a>". get_string('filedropbox', 'quickcontent') . "</h2>", array('size'=>'48'));

        $mform->addElement('html',"<p><i>" . get_string('filedrophelptext', 'quickcontent') . "</i></p>");
        $mform->addElement('text', 'filedrop', get_string('filedropboxname', 'quickcontent')); 
        $mform->setType('filedrop', PARAM_RAW);
        
        $mform->addElement('header', 'filedropoptions', get_string('filedropoptions', 'quickcontent'));
        
        $mform->addElement('text', 'fmaxnum', "Max number of files/student", array('size'=>'2')); 
        $mform->setType('fmaxnum', PARAM_INT);
        $mform->setAdvanced('fmaxnum');
        $mform->setDefault('fmaxnum', 1);
        
        $mform->addElement('submit', 'submitfiledrop', get_string('submitfiledropbox', 'quickcontent'));

        $mform->addElement('html','<hr />');
//-------------------------------------------------------------------------------        

        $mform->addElement('header', 'secturldropbox',"<h2><a name=<h2><a name='urldropbox'></a>". get_string('urldropbox', 'quickcontent') . "</h2>", array('size'=>'48'));
        
        $mform->addElement('html',"<p><i>" . get_string('fileurlhelptext', 'quickcontent') . "</i></p>");
        $mform->addElement('text', 'urldrop', get_string('urldropboxname', 'quickcontent')); 
        $mform->setType('urldropboxname', PARAM_RAW);
        $mform->addElement('submit', 'submiturldrop', get_string('submiturldropbox', 'quickcontent'));

        $mform->addElement('html','<hr />'); 
//-------------------------------------------------------------------------------
        
        $mform->addElement('header', 'sectforum',"<h2><a name=<h2><a name='forum'></a>". get_string('forum', 'quickcontent') . "</h2>", array('size'=>'48'));
        
        //$mform->addElement('html',"<p><i>" . get_string('forumhelptext', 'quickcontent') . "</i></p>");
        $mform->addElement('text', 'forum', get_string('forum', 'quickcontent')); 
        $mform->setType('forum', PARAM_RAW);
        $mform->addElement('submit', 'submitforum', get_string('submitforum', 'quickcontent'));

        $mform->addElement('html','<hr />'); 
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        //$this->standard_hidden_coursemodule_elements();
        $mform->addElement('hidden', 'visible', 1);
        $mform->addElement('hidden', 'cmidnumber', '');
        $this->standard_hidden_coursemodule_elements();
        //$this->add_action_buttons();

        
    }

    function data_preprocessing(&$default_values) {
        $draftitemid = file_get_submitted_draft_itemid('files');
        file_prepare_draft_area($draftitemid, $this->context->id, 'mod_resource', 'content', 0, array('subdirs'=>true));
        $default_values['files'] = $draftitemid;
    }

}
