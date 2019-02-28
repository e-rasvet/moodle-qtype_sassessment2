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
 * sassessment question renderer class.
 *
 * @package    qtype
 * @subpackage sassessment
 * @copyright  2018 Kochi-Tech.ac.jp

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib.php');

/**
 * Generates the output for sassessment questions.
 *
 * @copyright  2018 Kochi-Tech.ac.jp

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_sassessment_renderer extends qtype_renderer {
    public function formulation_and_controls(question_attempt $qa,
                                             question_display_options $options) {
        global $USER, $CFG, $PAGE, $OUTPUT, $DB;

        $PAGE->requires->jquery_plugin('jquery');

        $question = $qa->get_question();

        $questiontext = $question->format_questiontext($qa);

        /*
         * Subtitles parser
         */
        if (strstr($questiontext, ".vtt")){
            if (preg_match('/src="(.*?).vtt/', $questiontext, $match) == 1) {

                $vttFile = parse_url($match[1]);
                $vttPatchArray = array_reverse(explode("/", $vttFile["path"]));
                $fileID = $vttPatchArray[1];
                $fileName = $vttPatchArray[0].".vtt";

                $fileData = $DB->get_record("files", array("filename"=>$fileName, "itemid"=>$fileID, "component"=>"question", "filearea"=>"questiontext"));

                $filePatch = substr($fileData->contenthash, 0, 2)."/".substr($fileData->contenthash, 2, 2);

                $vttContents = file ($CFG->dataroot."/filedir/".$filePatch."/".$fileData->contenthash);

                $strings = array();

                foreach($vttContents as $k => $v){
                    if (substr($v, 0, 3) == "00:"){
                        $strings[$v] = $vttContents[$k+1];
                    }
                }

                $parsedToLast = 0;

                $stringLinks = html_writer::start_tag('ul', array('style'=>'text-align: left;list-style-type: none;'));
                foreach ($strings as $k => $v){
                    $stringLinks .= html_writer::start_tag('li');
                    list($from, $to) = explode(" --> ", $k);
                    $parsed = date_parse($from);
                    $parsedTo = date_parse($to);

                    $secondsFull = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
                    $parsedTo = $parsedTo['hour'] * 3600 + $parsedTo['minute'] * 60 + $parsedTo['second'];

                    if ($parsedToLast == $secondsFull){
                        $secondsFull++;
                    }

                    $allSecs = range($secondsFull, $parsedTo);

                    $timeClasses = "";
                    foreach($allSecs as $sec){
                        $timeClasses .= " qaclass".$sec."id";
                    }

                    $seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'] + $parsed['fraction'];
                    $stringLinks .= html_writer::tag('a', "[".$seconds."] ".$v, array('href' => 'javascript:;', 'class' => 'goToVideo'.$timeClasses, 'data-value'=>$seconds));
                    $stringLinks .= html_writer::end_tag('li');

                    $parsedToLast = $parsedTo;
                }
                $stringLinks .= html_writer::end_tag('ul');

                $questiontext = str_replace("</video>", "</video>". $stringLinks, $questiontext);
            }
        }

        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        if (!$options->readonly) {
            $sampleResponses = html_writer::start_tag('ul');
            foreach ($question->questions as $q) {
                $sampleResponses .= html_writer::start_tag('li');
                $sampleResponses .= html_writer::tag('div', $question->format_text($q->answer, $q->answerformat,
                    $qa, 'question', 'answer', $q->id)); // , array('class' => 'qtext')
                $sampleResponses .= html_writer::end_tag('li');

                break;
            }
            $sampleResponses .= html_writer::end_tag('ul');
        }

        $answername = $qa->get_qt_field_name('answer');
        {
            $label = 'answer';
            $currentanswer = $qa->get_last_qt_var($label);
            $inputattributes = array(
                'type' => 'hidden',
                'name' => $answername,
                'value' => $currentanswer,
                'id' => $answername,
                'size' => 60,
                'class' => 'form-control d-inline',
                'readonly' => 'readonly',
                //'style' => 'border: 0px; background-color: transparent;',
            );

            $answerDiv = $qa->get_qt_field_name('answerDiv');

            $input = html_writer::div($currentanswer, $answerDiv, array("id" => $answerDiv));
            $input .= html_writer::empty_tag('input', $inputattributes);


            if ($question->show_transcript == 1) {
                $answerDisplayStatus = "none";
            } else {
                $answerDisplayStatus = "display:none";
            }

            if ($question->show_analysis == 1) {
                $gradeDisplayStatus = "none";
            } else {
                $gradeDisplayStatus = "display:none";
            }

            if ($question->save_stud_audio == 1) {
                $audioDisplayStatus = "none";
            } else {
                $audioDisplayStatus = "display:none";
            }

            if (!$options->readonly) {
                /*
                 * No need to show target text
                 */

                $result .= html_writer::start_tag('div', array('class' => 'ablock form-inline', 'style' => 'display:none'));
                $result .= html_writer::tag('label', get_string('targetresponse', 'qtype_sassessment',
                    $sampleResponses . html_writer::tag('span', $input, array('class' => 'answer'))),
                    array('for' => $inputattributes['id'], 'style' => $answerDisplayStatus));
                $result .= html_writer::end_tag('div');

            }
        }

        $itemid = $qa->prepare_response_files_draft_itemid('attachments', $options->context->id);
        if (!$options->readonly) {
            $gradename = $qa->get_qt_field_name('grade');
            $btnname = $qa->get_qt_field_name('rec');
            $audioname = $qa->get_qt_field_name('audio');
            $btnattributes = array(
                'name' => $btnname,
                'id' => $btnname,
                'size' => 80,
                'qid' => $question->id,
                'answername' => $answername,
                'answerDiv' => $answerDiv,
                'gradename' => $gradename,
                'onclick' => 'recBtn(event);',
                'type' => 'button',
                'options' => json_encode(array(
                    'repo_id' => $this->get_repo_id(),
                    'ctx_id' => $options->context->id,
                    'itemid' => $itemid,
                    'title' => 'audio.mp3',
                )),
                'audioname' => $audioname,
            );

            $btn = html_writer::tag('button', 'Start recording', $btnattributes);
            $audio = html_writer::empty_tag('audio', array('src' => ''));

            $result .= html_writer::start_tag('div', array('class' => 'ablock'));
            $result .= html_writer::tag('label', "" . $btn,
                array('for' => $btnattributes['id']));
            $result .= html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => $qa->get_qt_field_name('attachments'), 'value' => $itemid));
            $result .= html_writer::end_tag('div');

            $result .= html_writer::start_tag('div', array('class' => 'ablock', 'style' => $audioDisplayStatus));
            $result .= html_writer::empty_tag('audio', array('id' => $audioname, 'name' => $audioname, 'controls' => ''));
            $result .= html_writer::end_tag('div');

            $result .= html_writer::script(null, new moodle_url('/question/type/sassessment/js/recorder.js'));
            $result .= html_writer::script(null, new moodle_url('/question/type/sassessment/js/main.js?1'));
            $result .= html_writer::script(null, new moodle_url('/question/type/sassessment/js/Mp3LameEncoder.min.js'));
        }
        else {
            $files = $qa->get_last_qt_files('attachments', $options->context->id);

            if ($question->save_stud_audio == 1) {
                $audioDisplayStatus = "none";
            } else {
                $audioDisplayStatus = "display:none";
            }

            foreach ($files as $file) {
                $result .= html_writer::start_tag('div', array('class' => 'ablock', 'style' => $audioDisplayStatus));
                // $result .= html_writer::tag('p', html_writer::link($qa->get_response_file_url($file),
                //         $this->output->pix_icon(file_file_icon($file), get_mimetype_description($file),
                //         'moodle', array('class' => 'icon')) . ' ' . s($file->get_filename())));
                $result .= html_writer::tag('p', html_writer::empty_tag('audio', array('src' => $qa->get_response_file_url($file), 'controls' => '')));
                $result .= html_writer::end_tag('div');
            }
        }

        $gradename = $qa->get_qt_field_name('grade');
        {
            $label = 'grade';
            $currentanswer = $qa->get_last_qt_var($label);
            $inputattributes = array(
                'name' => $gradename,
                'value' => $currentanswer,
                'id' => $gradename,
                'size' => 10,
                'class' => 'form-control d-inline',
                'readonly' => 'readonly',
                'style' => 'border: 0px; background-color: transparent;',
            );

            $input = html_writer::empty_tag('input', $inputattributes);

            if (!$options->readonly) {
                $result .= html_writer::start_tag('div', array('class' => 'ablock form-inline'));
                $result .= html_writer::tag('label', get_string('score', 'qtype_sassessment',
                    html_writer::tag('span', $input, array('class' => 'answer'))),
                    array('for' => $inputattributes['id'], 'style'=>$gradeDisplayStatus));
                $result .= html_writer::end_tag('div');
            }
        }


        /*
         * IOS app integration code START
         */
        if (function_exists("voiceshadow_is_ios"))
            if (voiceshadow_is_ios() && !$options->readonly && file_exists($CFG->dirroot.'/mod/voiceshadow/ajax-apprecord.php')) {
                $time = time();
                $result .= html_writer::start_tag("a", array("href" => 'voiceshadow://?link='.$CFG->wwwroot.'&id='.$options->context->id.'&uid='.$USER->id.'&time='.$time.'&fid='.$question->id.'&var=0&audioBtn=1&sstBtn=1&type=voiceshadow&mod=voiceshadow', "id" => "id_recoring_link",  //
                    "onclick" => 'formsubmit(this.href)'));

                $result .= get_string('recordAudioIniosapp', 'qtype_sassessment');
                $result .= html_writer::end_tag('a');


                $PAGE->requires->js_amd_inline('
require(["jquery"], function(min) {
    $(function() {
        $.get( "'.$CFG->wwwroot.'/mod/voiceshadow/ajax-apprecord.php", { id: '.$question->id.', inst: '.$options->context->id.', uid: '.$USER->id.' }, function(json){
            var j = JSON.parse(json);
            var t = +new Date();
    
            if (j.status == "success") {
    
                  $(":text").each(function() {
                      if ($(this).attr("id") == "'.$answername.'") {
                            $(this).val(j.text);
                      }
                  });
                  
                  $("audio").each(function() {
                      if ($(this).attr("id") == "'.$audioname.'") {
                            $(this).attr("src", "'.$CFG->wwwroot.'/mod/voiceshadow/file.php?file="+j.fileid);
                      }
                  });
                  
                  $("input").each(function() {
                      if ($(this).attr("name") == "'.$qa->get_qt_field_name('attachments').'") {
                            $(this).val(j.itemid);
                      }
                  });
                  
                  $.post("'.$CFG->wwwroot.'/question/type/sassessment/ajax-score.php", { qid: '.$question->id.', ans: j.text },
                     function (data) {
                        $("input").each(function() {
                                         if ($(this).attr("name") == "'.$gradename.'") {
                                $(this).val(JSON.parse(data).gradePercent);
                             }
                        });
                     });
    
                  $.get( "'.$CFG->wwwroot.'/mod/voiceshadow/ajax-apprecord.php", { a: "delete", id: '.$question->id.', inst: '.$options->context->id.', uid: '.$USER->id.' });
            }
        });    
    });
});
');

                /*
                 * IOS app integration code END
                 */

            }


        //$PAGE->requires->js_call_amd('qtype_sassessment/sassessment', 'init');

        $PAGE->requires->js_amd_inline('
require(["jquery"], function(min) {
    $(function() {
        $(".goToVideo").click(function() {
            $(".goToVideo").attr("style","");
            var $div = $(this).closest(".qtext");
            video = $div.find("video").get(0);
            video.currentTime = $(this).attr("data-value");
            video.play();
            console.log(video.currentTime);
        });
        
        window.latestID = -1;

            $("video").on("timeupdate", function(event){
                time = Math.round(this.currentTime);
                
                if (window.latestID != time) {
 
                for (var i=(time-1); i>0; i--) {
                   $(".qaclass"+i+"id").attr("style","");
                }
                
                $(".qaclass"+time+"id").attr("style","background-color: antiquewhite; color: black;");
                
                //$(".goToVideo").each(function() {
                //    console.log("DV: " + $(this).attr("data-value"));
                //    if ($(this).attr("data-value") > this.currentTime) {
                //        console.log($(this).attr("data-value"));

                //    }
                //});
                window.latestID = time
                }
            });
    });
});
');

        return $result;
    }

    public function get_repo_id($type = 'upload') {
        global $CFG;
        require_once($CFG->dirroot . '/lib/form/filemanager.php');
        foreach (repository::get_instances() as $rep) {
            $meta = $rep->get_meta();
            if ($meta->type == $type)
                return $meta->id;
        }
        return null;
    }

    public function specific_feedback(question_attempt $qa) {
        $question = $qa->get_question();
        $ans = $qa->get_last_qt_var('answer');
        $grade = qtype_sassessment_compare_answer($ans, $qa->get_question()->id);

        $result = '';
        $result .= html_writer::start_tag('div', array('class' => 'ablock'));

        /*
         * No need to show target response
         */
        //$result .= html_writer::tag('p', get_string('targetresponsee', 'qtype_sassessment') . ": " . $grade['answer']);

        if ($question->show_transcript == 1) {
            $result .= html_writer::tag('p', get_string('yourresponse', 'qtype_sassessment') . ": " . $ans);
        }

        $result .= html_writer::tag('p', get_string('scoree', 'qtype_sassessment') . ": " . $grade['gradePercent']);
        $result .= html_writer::end_tag('div');

        /*
         * TMP disabled Analises report
         */
        if ($question->show_analysis == 1) {
            $anl = qtype_sassessment_printanalizeform($ans);
            unset($anl['laters']);
            $table = new html_table();
            $table->head = array('Analysis', 'Result');
            $table->data = array();

            foreach ($anl as $k => $v)
                $table->data[] = array(get_string($k, 'qtype_sassessment'), $v);

            $result .= html_writer::table($table);
        }

        return $result;
    }

    public function correct_response(question_attempt $qa) {
        // TODO.
        return '';
    }
}
