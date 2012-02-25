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
 * Library of interface functions and constants for module quickcontent
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the quickcontent specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package   mod_quickcontent
 * @copyright 2010 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


function quickcontent_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return false;
        case FEATURE_MOD_INTRO:               return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return false;

        default: return null;
    }
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $quickcontent An object from the form in mod_form.php
 * @return int The id of the newly inserted quickcontent record
 */
function quickcontent_add_instance($data,$mform) {
    global $DB,$CFG,$PAGE;

    $data->timecreated = time();
    $data->timemodified = time();
    if(isset($data->submitheading)){
        // label/heading
        $lm = $DB->get_record('modules',array('name'=>'label'));
        $upd=new stdClass();
        $upd->id=$data->coursemodule;
        $upd->module=$lm->id;
        $DB->update_record('course_modules',$upd);
        
        // fiddle some form data to spoof a label insert
        $data->module=$lm->id;
        $data->add='label';
        $data->modulename='label';
        // add the label
        $data->type = 'label';
        $data->name = "title";
        if(empty($data->heading)){ $data->heading='Heading'; } 
        $data->intro = "<h{$data->headingsize}>{$data->heading}</h{$data->headingsize}>";
        $data->introformat = 1;
        return $DB->insert_record('label', $data);
    }
    elseif(isset($data->submitinstructions)){
        // label/heading
        $lm = $DB->get_record('modules',array('name'=>'label'));
        $upd=new stdClass();
        $upd->id=$data->coursemodule;
        $upd->module=$lm->id;
        $DB->update_record('course_modules',$upd);

        
        // fiddle some form data to spoof a label insert
        $data->module=$lm->id;
        $data->add='label';
        $data->modulename='label';
        // add the label
        $data->type = 'label';
        $data->name = "instructions";
        if(empty($data->instructions['text'])){ 
            $data->intro='instructions'; 
        }else{ 
            $data->intro = $data->instructions['text'];
        }


        $cmid = $data->coursemodule;
        $draftitemid = $data->instructions['itemid'];        
        $context = get_context_instance(CONTEXT_MODULE, $cmid);

        // deal with images
        if ($draftitemid) {
            $data->intro = file_save_draft_area_files($draftitemid, $context->id, 'mod_label', 'intro', 0, array('subdirs'=>true),$data->intro);
        }
        $data->introformat = 1;
        return $DB->insert_record('label', $data);
    }
    elseif(isset($data->submitfileupload)){
        
        require_once("$CFG->libdir/resourcelib.php");
        $fs = get_file_storage();
        $cmid = $data->coursemodule;
        $draftitemid = $data->files;
        
        $context = get_context_instance(CONTEXT_MODULE, $cmid);
        $files = file_get_drafarea_files($draftitemid);
        
        if (count($files->list) == 1) {
            if ($draftitemid) {
                file_save_draft_area_files($draftitemid, $context->id, 'mod_resource', 'content', 0, array('subdirs'=>true));
            }
            $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder', false);
        
            $file = array_shift($files);
            $lm = $DB->get_record('modules',array('name'=>'resource'));
            $upd=new stdClass();
            $upd->id=$data->coursemodule;
            $upd->module=$lm->id;
            $DB->update_record('course_modules',$upd);
               
            $data->name = $file->get_filename();
            $data->id = $DB->insert_record('resource', $data);

            // we need to use context now, so we need to make sure all needed info is already in db
            $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$data->coursemodule));
        }else{
            // for multiple files - create a folder 
            
            if ($draftitemid) {
                file_save_draft_area_files($draftitemid, $context->id, 'mod_folder', 'content', 0, array('subdirs'=>true));
            }
            $files = $fs->get_area_files($context->id, 'mod_folder', 'content', 0, 'sortorder', false);

            $fns = '';
            $sep = '';
            foreach($files as $f){
                $fns .= $sep . $f->get_filename();    
                $sep = '-';
            }
            
            $file = array_shift($files);
            $lm = $DB->get_record('modules',array('name'=>'folder'));
            $upd=new stdClass();
            $upd->id=$data->coursemodule;
            $upd->module=$lm->id;
            $DB->update_record('course_modules',$upd);
               
            $data->name = "Resource Folder [$fns]";
            $data->intro = 'List of files:' . $fns;
            $data->revision = 1;
            $data->introformat = 1;
            $data->id = $DB->insert_record('folder', $data);
        }
            
        return $data->id;
         
    }
    elseif(isset($data->submitimageupload)){      
        // label/heading
        $lm = $DB->get_record('modules',array('name'=>'label'));
        $upd=new stdClass();
        $upd->id=$data->coursemodule;
        $upd->module=$lm->id;
        $DB->update_record('course_modules',$upd);

        $fs = get_file_storage();
        $draftitemid = $data->files;
        
        $context = get_context_instance(CONTEXT_MODULE, $upd->id);
        
        if ($draftitemid) {
            file_save_draft_area_files($draftitemid, $context->id, 'mod_label', 'intro', 0, array('subdirs'=>true));
        }
        $files = $fs->get_area_files($context->id, 'mod_label', 'intro');
        $file = array_shift($files);
        $filename=$file->get_filename();
        if($filename=='.'){
            $file = array_shift($files);
            $filename=$file->get_filename();    
        }
        $file_record = array('contextid'=>$file->get_contextid(), 'component'=>$file->get_component(), 'filearea'=>$file->get_filearea(),
                             'itemid'=>$file->get_itemid(), 'filepath'=>$file->get_filepath(),
                             'filename'=>'thumb_'.$filename, 'userid'=>$file->get_userid());
        if($data->imageuploadwidth>0){
            try {
               $thumburl = $fs->convert_image($file_record,$file,$data->imageuploadwidth);            
               $thumb="<img src='@@PLUGINFILE@@/{$file_record['filename']}' />";
               $data->intro = "<a href='@@PLUGINFILE@@/{$filename}'>$thumb</a>";        
            } catch (Exception $e) {}
        }elseif($data->imageuploadwidth==0){
            $thumb = $data->intro = "<img src='@@PLUGINFILE@@/{$filename}' />";                   
        }else{
            $thumb = $filename;
            $data->intro = "<a href='@@PLUGINFILE@@/{$filename}'>{$filename}</a>";        
        }

        // fiddle some form data to spoof a label insert
        $data->module=$lm->id;
        $data->add='label';
        $data->modulename='label';
        // add the label
        $data->type = 'label';
        $data->name = "title";
        $data->introformat = 1;
  
        if($data->activity>0){
            dbg();    
            $mod = $DB->get_record_sql("select t0.id,t1.name,t0.instance 
                                        from {$CFG->prefix}course_modules t0, {$CFG->prefix}modules t1 
                                        where t0.module=t1.id and t0.id={$data->activity} 
                                        ");
            $instname = $DB->get_record($mod->name,array('id'=>$mod->instance));
            $data->intro = "<span class='floaty' style='text-align:center'><a class='piclink' href='{$CFG->wwwroot}/mod/{$mod->name}/view.php?id={$mod->id}'>$thumb<br/>{$instname->name}</a></span>"; 
            
            $data->intro .="<script type='text/javascript'> 
                        var e = YAHOO.util.Dom.getElementsByClassName('commands', 'span');
                        if(e.length){
                            document.getElementById('module-{$data->activity}').style.display = 'block';}else{document.getElementById('module-{$data->activity}').style.display = 'none';}</script>";
            
        }        
        return $DB->insert_record('label', $data);
    }
    elseif(isset($data->submitmediaupload)){
        // embedded content (i.e. a label!)
        $lm = $DB->get_record('modules',array('name'=>'label'));
        $upd=new stdClass();
        $upd->id=$data->coursemodule;
        $upd->module=$lm->id;
        $DB->update_record('course_modules',$upd);
        
        // fiddle some form data to spoof a label insert
        $data->module=$lm->id;
        $data->add='label';
        $data->modulename='label';
        // add the label
        $data->type = 'label';
        $data->name = "title";
        
        $fs = get_file_storage();
        $draftitemid = $data->files;
        
        $context = get_context_instance(CONTEXT_MODULE, $upd->id);
        
        if ($draftitemid) {
            file_save_draft_area_files($draftitemid, $context->id, 'mod_label', 'intro', 0, array('subdirs'=>true));
        }
        $files = $fs->get_area_files($context->id, 'mod_label', 'intro');
        $file = array_shift($files);
        $filename=$file->get_filename();
        if($filename=='.'){
            $file = array_shift($files);
            $filename=$file->get_filename();    
        }
        require_once($CFG->dirroot . '/filter/mediaplugin/filter.php'); // Include the code to test
        $filterplugin = new filter_mediaplugin(null, array());
        $data->intro = $filterplugin->filter('<a href="@@PLUGINFILE@@/' . $filename . '">' . $filename . '</a>');
        $data->introformat = 1;
        return $DB->insert_record('label', $data);
    }
    elseif(isset($data->submitembed)){
        // embedded content (i.e. a label!)
        $lm = $DB->get_record('modules',array('name'=>'label'));
        $upd=new stdClass();
        $upd->id=$data->coursemodule;
        $upd->module=$lm->id;
        $DB->update_record('course_modules',$upd);
        
        // fiddle some form data to spoof a label insert
        $data->module=$lm->id;
        $data->add='label';
        $data->modulename='label';
        // add the label
        $data->type = 'label';
        $data->name = "title";
        $data->intro = "<h2>{$data->embedcode}</h2>";
        $data->introformat = 1;
        return $DB->insert_record('label', $data);
    }
    elseif(isset($data->submitforum)){
        // embedded content (i.e. a label!)
        dbg();
        $lm = $DB->get_record('modules',array('name'=>'forum'));
        $upd=new stdClass();
        $upd->groupmode=0;
        $upd->id=$data->coursemodule;
        $upd->module=$lm->id;
        $DB->update_record('course_modules',$upd);
        
        // fiddle some form data to spoof a forum insert
        $data->module=$lm->id;
        $data->add='forum';
        $data->modulename='forum';
        // add the label
        $data->type = 'general';
        $data->name = "forum";
        $data->intro = "<h2>{$data->forum}</h2>";
        $data->introformat = 1;
        return $DB->insert_record('forum', $data);
    }
    elseif(isset($data->submiturldrop)||isset($data->submitfiledrop)){
        $lm = $DB->get_record('modules',array('name'=>'assignment'));
        $upd=new stdClass();
        $upd->id=$data->coursemodule;
        $upd->module=$lm->id;
        $DB->update_record('course_modules',$upd);

        $assignment->course = $data->course;             
        $assignment->introformat = 1;
        $assignment->maxbytes = $CFG->maxbytes;
        if(isset($data->submiturldrop)){
            $assignment->assignmenttype = 'online';
            $assignment->name = $data->urldrop;
        }else{
            $assignment->name = $data->filedrop;
            if($data->fmaxnum<=1){
                $assignment->assignmenttype = 'uploadsingle';
            }else{
                $assignment->assignmenttype = 'upload';
                $assignment->var1 = max(2,$data->fmaxnum);
                $assignment->var4 = 1;
                $assignment->resubmit=1; 
            }
        }
        $assignment->intro = "$assignment->name ({$assignment->assignmenttype} Drop Box)";
        $assignment->timeavailable = time();
        $assignment->timedue = time()+(60*60*24*365);
        $assignment->grade = 10;         
        $assignment->groupmode=1;  
        return $DB->insert_record("assignment", $assignment); 
    }
    
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $quickcontent An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function quickcontent_update_instance($quickcontent) {
    global $DB;

    $quickcontent->timemodified = time();
    $quickcontent->id = $quickcontent->instance;

    # You may have to add extra stuff in here #

    return $DB->update_record('quickcontent', $quickcontent);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function quickcontent_delete_instance($id) {
    global $DB;

    if (! $quickcontent = $DB->get_record('quickcontent', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records('quickcontent', array('id' => $quickcontent->id));

    return true;
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 */
function quickcontent_user_outline($course, $user, $mod, $quickcontent) {
    $return = new stdClass;
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function quickcontent_user_complete($course, $user, $mod, $quickcontent) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in quickcontent activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function quickcontent_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function quickcontent_cron () {
    return true;
}

/**
 * Must return an array of users who are participants for a given instance
 * of quickcontent. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $quickcontentid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function quickcontent_get_participants($quickcontentid) {
    return false;
}

/**
 * This function returns if a scale is being used by one quickcontent
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $quickcontentid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 */
function quickcontent_scale_used($quickcontentid, $scaleid) {
    global $DB;

    $return = false;

    //$rec = $DB->get_record("quickcontent", array("id" => "$quickcontentid", "scale" => "-$scaleid"));
    //
    //if (!empty($rec) && !empty($scaleid)) {
    //    $return = true;
    //}

    return $return;
}

/**
 * Checks if scale is being used by any instance of quickcontent.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any quickcontent
 */
function quickcontent_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('quickcontent', 'grade', -$scaleid)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function quickcontent_uninstall() {
    return true;
}


