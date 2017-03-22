<?php

//////////////// Editable options ////////////////

	// this is the banner headline displayed on the Menu page
	$tutorialTitle = 'LEARNING PRINCIPLES';

	$backgroundColor = '#C4D9E1';
	$cssLink = '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/css/bootstrap.css';

	// this is the Introductory text displayed on the Menu page. HTML tags can be included
$menuIntroText = <<<EOT

<p>
<center><img src="/learnprin/learningprinciples.gif" alt="learningprinciples" style="width:290px;height:155px"></center></>
<p>
	This set of tutorials is about the basic principles of learning and how they relate to your success in life.  Tutorials build upon each other and you should work through them in serial order; don't skip any.  Each "set" is a series of screen presentations to which you respond by typing a word or two.  This will require you to think a little each time because the program insists that you understand each point. You cannot go backwards when working through a given set of frames.  Read everything carefully as the program will constantly test your memory. A momentary green flash signals that you have responded correctly and have advanced forward.  Sometimes you may need to try again.  Finish each successive tutorial before you take a break.  Remember the name you used to sign in and continue to use it when you advance to the next set in the menu.  Now, click on a set number below and experience automated instruction.

<p>
By applying your new knowledge, you can take big steps to enjoying life more!
</p>

EOT;

	// the directory that will be searched for files, relative to wherever this file is located on the filesystem
	// e.g. 'tutorials/aba/oneToTen/', which would look 3 folders under the current, or
	// e.g. '../../tutorials/aba/' which would look two directories above the current and then under /tutorials/aba/
	// **** MUST START WITH ./ AND MUST END WITH /
	$frameDirectory = './learnprin/';


	// regex used to match frame files that will be shown in the menu
	$frameFilePattern = '/txt$/';

	// directory where output files will be written, same rules as for the $frameDirectory, i.e. above or below the current dir
	// **** MUST START WITH ./ AND MUST END WITH /
	$outfileDirectory = './learnprin/';

	// suffix appended to the name of the tutorial which will be used to generate the file for final scores
	$finalScoresFileSuffix = '_FINAL_SCORE.out';

	// students are forced to start over if their score drops below this number after the 5th frame
	$percentStartOver = 50;

	$outOfSequenceMessage = "It\'s strongly recommended that you work through these tutorials in order. ";
	$outOfSequenceMessage = $outOfSequenceMessage."Please work through the following tutorials first:";


	// change this to true to only give one try and not show the correct answer
	$isTest = false;

	// from one to whatever set they choose
	// read final score file
	// check at least one line has their name in first position and score above 50 in third
	// if not prompt them to go to first unfinished tutorial

///////////////////////////////////////////////////////////////////////////////  END Editable options







//////////////// Do not edit below this line unless you know what you're doing ////////////

	$student = $_REQUEST['Student'];
	$tutorial = $_REQUEST['frameSelection'];
	$percentStartOver = isset($_REQUEST['PercentStartOver']) ? $_REQUEST['PercentStartOver'] : $percentStartOver;
	$scriptname = basename(__FILE__, '');
	session_start();

	if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_REQUEST['checkProgress']) {
		header('Content-Type: application/json');

		try {

			$name = urldecode($_REQUEST['name']);
			$tutorial = urldecode($_REQUEST['tutorial']);

			$capture = array();
			$match = preg_match('/[0-9]{2}/', $tutorial, $capture, 256);
			if ($match) {
				$requestedTutorialNumber = $capture[0][0];
			}

			$dir_handle = @opendir($frameDirectory);
			$tutorials = array();
			while ($file = readdir($dir_handle)) {
				if (preg_match($frameFilePattern, $file)) {
					array_push($tutorials, $file);
				}
			}
			closedir($dir_handle);
			sort($tutorials);

			function prettyName($file) {
				$displayFile = str_replace('.txt', '', $file);
				$displayFile = str_replace('_', ' ', $displayFile);
				return $displayFile;
			}

			$rememdiation = array();

			foreach($tutorials as $tut) {
				$capture = array();
				$match = preg_match('/[0-9]{2}/', $tut, $capture, 256);
				if ($match) {
					if ($capture[0][0] == $requestedTutorialNumber) {
						break;
					}
				}
				$filename = str_replace('.txt', '_FINAL_SCORE.out', $tut);
				if (file_exists($frameDirectory.$filename)) {
					$passedTutorial = false;
					$f = fopen($frameDirectory.$filename, 'r');
					while (!feof($f)) {
						$line = fgets($f);
						$parts = explode(',', $line);
						if ($parts[0] == $name && $parts[2] > $percentStartOver) {
							$passedTutorial = true;
							break;
						}
					}
					fclose($f);
					if (!$passedTutorial) {
						array_push($rememdiation, prettyName($tut));
					}
				} else {
					array_push($rememdiation, prettyName($tut));
				}
			}

			echo json_encode($rememdiation);
		} catch (Exception $e) {
			echo '[]';
		}
		exit();
	}

	if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_REQUEST['finalScore']) {
		$decoded = str_replace('.txt', '', str_replace(' ', '_', urldecode($_REQUEST['tutorial'])));
		$finalScoreFile = $outfileDirectory.$decoded.$finalScoresFileSuffix;
		$f = fopen($finalScoreFile, 'a');
		$stringData = $_REQUEST['student'].','.$_REQUEST['tutorial'].','.$_REQUEST['finalScore'].','.$_REQUEST['numberOfQuestions'].','.$_REQUEST['numberOfAttempts'].','.$_REQUEST['answeredCorrectly'].','.date("D M j G:i:s Y");
		fwrite($f, $stringData."\n");
		fclose($f);
		exit();
	}

	if ($_SERVER['REQUEST_METHOD'] == 'GET' && ! $_REQUEST['frameSelection'] && ! $_REQUEST['correctAnswer']) {
?>
		<html>
		<head>
			<title><?php echo $tutorialTitle; ?></title>
			<meta name="viewport" content="width=device-width" />
			<link rel="stylesheet" href="<?php echo $cssLink; ?>">
      		<script src="https://cdnjs.cloudflare.com/ajax/libs/json2/20140204/json2.min.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

			<style>
				body {
					margin: 20px;
					background-color: <?php echo $backgroundColor; ?>;
					color: #333;
				}
				* {
					font-family: 'Myriad Pro', Calibri, "Helvetica Neue", Arial, sans-serif;
					font-size: 18px !important;
				}
        input {
					margin: 10px;
					border-radius: 4px;
					border: 1px solid #aaaaaa;
				}
			</style>
		</head>
		<body onLoad="document.phpMenu.Student.focus();">

		<center>
            <h2><?php echo $tutorialTitle; ?> Main Menu</h2>
        </center>

		<?php echo $menuIntroText; ?>

		<p>If your score falls below <?php echo $percentStartOver ?>%, you will be returned to the beginning of the <?php echo ($isTest ? 'test' : 'tutorial'); ?>.</p>

		<p>Follow the <strong>3 Steps</strong> below to experience the <?php echo ($isTest ? 'test' : 'tutorials'); ?>.</p>

		<hr>

		<script>
			$(document).ready(function() {
				$('#tutorial-form').click(function() {
					var scriptname = '<?php echo $scriptname; ?>';
					var name = $('#Student').val();
					var tutorial = $('input[name=frameSelection]:checked').val();
					$.get(scriptname + '?checkProgress=1&name=' + name + '&tutorial=' + tutorial, function(data) {
						console.log(data);
						//return false;
						if (data.length) {
							var msg = '<?php echo $outOfSequenceMessage; ?>';
							msg += '<br/><br/>';
							for (var i = 0; i < data.length; i++) {
								msg += data[i] + '<br/>';
							}
							msg += '<br/>';
							$('#error-message').html(msg);
							return false;
						} else {
							if (document.getElementById('Student').value === '') {
								alert('Please fill in your name.');
								return false;
							}
							var frm = document.forms[0];

							var isChecked = false;
							for (var i = 0; i < frm.elements.length; i++ ) {
								if (frm.elements[i].type == 'radio' && frm.elements[i].name === 'frameSelection') {
									if (frm.elements[i].checked) {
										isChecked = true;
									}
								}
							}
							if (! isChecked) {
								alert('Please select a <?php echo ($isTest ? 'test' : 'tutorial'); ?>.');
								return false;
							}
							frm.submit();
						}
					});
				});
			});
		</script>

		<form name="phpMenu" method="post" onsubmit="return false;">
		<input type="hidden" name="PercentStartOver" value="<?php echo $percentStartOver; ?>">
		<input type="hidden" name="QuestionNumber" value="1">

		<strong>Step 1 - Type your full name (e.g. Mary Smith):</strong><br>
		<input type="text" id="Student" name="Student" size="30"/>
		<br/><br/>
		<p>
		<strong>
			Step 2 - Select the <?php echo ($isTest ? 'test' : 'tutorial'); ?> by clicking on the button next to it below):<br>
		</strong><br/>
		  <?php
			  $dir_handle = @opendir($frameDirectory);
			  $dirFiles = array();
			  while ($file = readdir($dir_handle)) {
				  if (preg_match($frameFilePattern, $file)) {
					  $displayFile = str_replace('.txt', '', $file);
					  $displayFile = str_replace('_', ' ', $displayFile);
					  array_push($dirFiles, $displayFile);
				  }
			  }
			  closedir($dir_handle);
              asort($dirFiles);

              foreach($dirFiles as $theFile) {
                $fileVal = $theFile.".txt";
                echo "<input style='margin-left: 10px;' type='radio' name='frameSelection' id='frameSelection' value='$fileVal'>$theFile<br/>";
              }
		 ?>
			<br/>
		<p>
				<div id="error-message" style="color:#c00000;"></div>
				<strong>Step 3 - Click Begin <?php echo ($isTest ? 'test' : 'tutorial'); ?>: </strong><br>
		  		<button style="margin-top: 10px;" class="btn btn-primary" id="tutorial-form">Begin <?php echo ($isTest ? 'Test' : 'Tutorial'); ?></button>
		<hr>
		<p>

		</body>
		</html>
		<?php
		exit();
	}

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_GET['specialfeedback'])) {
            $entityBody = file_get_contents('php://input');
            $feedbackFile = $outfileDirectory.'feedback.out';
            $f = fopen($feedbackFile, 'a');
            fwrite($f, $entityBody."\n");
            fclose($f);
            exit();
        } else {
            $_SESSION['key'] = md5(uniqid(rand(), true));
        }
    } else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		if (isset($_GET['userAnswer'])) {
			date_default_timezone_set('EST');
			if(!isset($_GET['outfile'])) $_GET['outfile'] = $_GET['tutorial'].'.out';
			$line = $_GET['student'].','.$_GET['tutorial'].','.$_GET['currentTry'].','.$_GET['currentFrame'].','.
					$_GET['correctAnswer'].','.$_GET['userAnswer'].','.$_GET['feedback'].','.$_GET['numberOfQuestions'].','.
					$_GET['numberOfAttempts'].','.$_GET['answeredCorrectly'].','.$_GET['percent'].','.
					date("D M j G:i:s Y");

			$decoded = str_replace(' ', '_', urldecode($_GET['outfile']));
			$h = fopen(str_replace('.txt', '', $outfileDirectory.$decoded), 'a');
		    fwrite($h, $line."\n");
		    fclose($h);
		    exit();
		} else {
			if (strcmp($_SESSION['key'], $_REQUEST['key']) != 0) exit();
			$_SESSION['key'] = null;

			$frames = array();
			function readtutorialLine($line, &$frame) {
				global $frames, $isFrame;
				$endOfFrame = 0;
				if (! is_array($frame)) $frame = array();
				if (strpos(trim($line), '@begin') === 0) $isFrame = 1;
				if (strpos(trim($line), '@end') === 0) $isFrame = 0;
				if (strpos(trim($line), '@answer') === 0) {
					$thisAnswer = str_replace("'", "&rsquo;", trim(substr(trim($line), 7)));
					if (! $frame['answer']) {
						$frame['answer'] = array();
					}
					array_push($frame['answer'], ($thisAnswer));
				}
				if (strpos(trim($line), '@tries') === 0) $frame['tries'] = trim(substr(trim($line), 6));
				if (strpos(trim($line), '@graphic') === 0) $frame['graphic'] = str_replace("'", "&rsquo;",trim(substr(trim($line), 8)));
				if (strpos(trim($line), '@video') === 0) {
					$frame['video'] = str_replace("'", "&rsquo;", trim(substr(trim($line), 6)));
					array_push($frames, $frame);
					$endOfFrame = 1;
				}
				if ($isFrame === 1) {
					if (strlen(trim($line)) && trim($line) != '@begin') {
						$frame['frame'] = $frame['frame'].str_replace("'", "&rsquo;", trim($line)).'<br>';
					} else {
						$frame['frame'] = $frame['frame'].'<br>';
					}
				}
				return $endOfFrame ? null : $frame;
			}

			$decoded = str_replace(' ', '_', urldecode($tutorial));
			$f = fopen($frameDirectory.$decoded, 'r');
			while (!feof($f)) {
				$line = fgets($f);
				$frame = readtutorialLine($line, $frame);
			}
			fclose($f);

			echo json_encode($frames);
			exit();
		}
	}
?>
<html>
<head>
	<title></title>
  <meta name="viewport" content="width=device-width" />
	<link rel="stylesheet" href="<?php echo $cssLink; ?>">

	<style>
		body {
			margin: 20px;
			background-color: <?php echo $backgroundColor; ?>;
			color: #333;
		}
		* {
      font-family: 'Myriad Pro', Calibri, "Helvetica Neue", Arial, sans-serif;
      font-size: 18px !important;
    }
    @media only screen and (max-width:800px) {
      * { font-size: 100% !important; }          
    }
		input {
			margin: 10px;
			border-radius: 4px;
			border: 1px solid #aaaaaa;
		}
	</style>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/json2/20140204/json2.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-color/2.1.2/jquery.color.min.js"></script>
	<script>
		var scriptname = '<?php echo $scriptname; ?>';
		var student = '<?php echo $student; ?>';
		var tutorial = '<?php echo $tutorial; ?>';
		var percentStartOver = <?php echo $percentStartOver; ?>;
		var postParams = '<?php echo 'key='.$_SESSION['key'].'&frameSelection='.$tutorial; ?>';

		var tutorialFrames = '';
		var currentFrame = 0;
		var numberCorrect = 0;
		var currentTry = 1;

        function xhr() {
            var xmlhttp = null;
            try {
                xmlhttp = new XMLHttpRequest();
            } catch (e) {}
            if (!xmlhttp) {
                for(var i = 0; i < 3; ++i) {
                    var progid = ['Msxml2.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.4.0'][i];
                    try {
                        xmlhttp = new ActiveXObject(progid);
                        break;
                    } catch(e) {}
                }
            }
			return xmlhttp;
        }

		function saveAnswer(correctAnswer, userAnswer, isCorrect) {
			var feed = isCorrect ? 'CORRECT' : 'INCORRECT';
			var xmlhttp = xhr();
            xmlhttp.open("GET", scriptname + '?userAnswer=' + userAnswer + '&student=' + student + '&tutorial=' + tutorial + '&currentTry=' +
							currentTry + '&currentFrame=' + eval(currentFrame + 1) + '&correctAnswer=' + correctAnswer + '&feedback=' +
							feed + '&percent=' + getScore() + '&numberOfQuestions=' + tutorialFrames.length + '&numberOfAttempts=' + eval(currentFrame + 1) + '&answeredCorrectly=' + numberCorrect, true);
			xmlhttp.send(null);
		}

		function saveFinalScore() {
			var parameters = scriptname + '?student=' + student + '&tutorial=' + tutorial + '&finalScore=' + getScore() + '&numberOfQuestions=' + tutorialFrames.length + '&numberOfAttempts=' + currentFrame + '&answeredCorrectly=' + numberCorrect;
			var xmlhttp = xhr();
            xmlhttp.open("GET", parameters, true);
			xmlhttp.send(null);
		}

		function init() {
            var xmlhttp = xhr();
			xmlhttp.open("GET", scriptname + '?' + postParams, true);
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4) {
					tutorialFrames = JSON.parse(xmlhttp.responseText);
					repaint('hidden', 'hidden', 'visible', 'userAnswer', '', true);
				}
			}
			xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xmlhttp.send(null);
		}

		function video(src, autoplay) {
		  var mode = autoplay ? 'autoplay' : '';
		  return '<video controls ' + mode + '>' +
					'<source src="' + src + '" type="video/mp4">' +
					  'Your browser does not support the video tag.' +
				  '</video>';
		}

		function evaluation_response(is_correct, show_correct) {
			if (is_correct) {
				return '<br>Your answer <font color="blue">' + document.frm.userAnswer.value + '</font> is <font color="green">CORRECT</font>. <br>Press Enter or Click to Continue.';
			} else if (show_correct) {
				return '<br>Your answer was <font color="blue">' + document.frm.userAnswer.value + '</font>.<br>The correct answer is <font color="green">' + tutorialFrames[currentFrame]['answer'][0] + '</font>';
			} else {
				return '<br>Not yet. Your answer was <font color="blue">' + document.frm.userAnswer.value + '</font>. Please try again.';
			}
		}

        function parseFrameText(evalutation_text) {
            var frameText = tutorialFrames[currentFrame]['frame'];
            var matchText = tutorialFrames[currentFrame]['frame'].match(/\{\{\{.*\}\}\}/);
            frameText = matchText ? frameText.replace(matchText[0], '') : frameText;
            if (matchText && currentTry == 1 && evalutation_text === '') {
                document.getElementById('userAnswer').style.visibility = 'hidden';
                document.getElementById('feedbackForm').style.display = 'inline';
                document.getElementById('feedbackTextarea').focus();
                document.getElementById('feedbackText').innerHTML = matchText[0].replace(/\{\{\{/g, '').replace(/\}\}\}/g, '');
                var handler = function(e) {
                    remover();
                    var feedback = document.getElementById('feedbackTextarea').value;

                    document.getElementById('feedbackTextarea').value = '';
                    document.getElementById('feedbackForm').style.display = 'none';
                    document.getElementById('userAnswer').style.visibility = 'visible';
                    document.getElementById('userAnswer').focus();

                    var xmlhttp = xhr();
                    xmlhttp.open("POST", scriptname + '?specialfeedback=1', true);
                    xmlhttp.setRequestHeader("Content-type","application/json");
                    xmlhttp.send(JSON.stringify({
                        feedback: feedback,
                        student: student,
                        tutorial: tutorial,
                        frame: currentFrame + 1
                    }));
                };
                var remover = function() {
                    document.getElementById('feedbackButton').removeEventListener('click', handler) ;
                }
                document.getElementById('feedbackButton').addEventListener('click', handler) ;
            }
            return frameText;
        }

		function repaint(e, c, u, field, evalutation_text, autoplay) {      
			document.getElementById('evaluation').style.visibility = e;
			document.getElementById('continueButton').style.visibility = c;
			document.getElementById('userAnswer').style.visibility = u;
			document.getElementById('evaluation').innerHTML = evalutation_text;
			document.getElementById('frameNumber').innerHTML = 'Frame #: ' + eval(currentFrame + 1) + ' of ' + tutorialFrames.length;
			document.getElementById('tryNumber').innerHTML = 'Try #: ' + currentTry;
			document.getElementById('percentCorrect').innerHTML = 'Correct %: ' + getScore();
            document.getElementById('frame').innerHTML = parseFrameText(evalutation_text);

            if (trim(tutorialFrames[currentFrame]['graphic'].toUpperCase()) === 'none'.toUpperCase()) {
				document.getElementById('graphic').innerHTML = '';
			} else {
				document.getElementById('graphic').innerHTML = '<center><img src="' + tutorialFrames[currentFrame]['graphic'] + '"/></center>';
			}
			if (trim(tutorialFrames[currentFrame]['video'].toUpperCase()) === 'NONE') {
				document.getElementById('video').innerHTML = '';
			} else {
				document.getElementById('video').innerHTML = video(trim(tutorialFrames[currentFrame]['video']), autoplay);
			}
			document.getElementById(field + 'Field').focus();
		}

		function evaluateResponse(response) {
			var answers = tutorialFrames[currentFrame]['answer'];
			var isCorrect = false;
			for (var i = 0; i < answers.length; i++) {
				if (answers[i].toUpperCase() === trim(response.toUpperCase())) {
					isCorrect = true;
					saveAnswer(answers[i].toUpperCase(), response.toUpperCase(), true);
					break;
				}
			}
			if (! isCorrect) {
				saveAnswer(answers[0].toUpperCase(), response.toUpperCase(), false);
			}
			if (isCorrect) {
				//repaint('visible', 'visible', 'hidden', 'continueButton', evaluation_response(true));
        numberCorrect++;

        $('body').css('background-color', '#3dab52');
        $('body').animate({
          'background-color': '#C4D9E1'
        }, 1300, null, function() {
          $('body').css('background-color', '#C4D9E1');
        });
				
        doContinue();
			}
			<?php if (! $isTest) { ?>
			else {
				if (currentTry < tutorialFrames[currentFrame]['tries']) {
					currentTry++;
					repaint('visible', 'hidden', 'visible', 'userAnswer', evaluation_response(false));
				} else {
					document.frm.userAnswer.disabled = true;
					repaint('visible', 'visible', 'hidden', 'continueButton', evaluation_response(false, true));
				}
			}
			document.frm.userAnswer.value = '';
			<?php } else echo 'document.frm.userAnswer.value = ""; doContinue();'; ?>

		}

		function doContinue() {
			currentFrame++;
			if (currentFrame === tutorialFrames.length || (currentFrame > 4 && getScore() < percentStartOver)) {
				saveFinalScore();
				var conclusion = '<br><p align="center"><b>You have reached the end of this program.</b></p><div align="center">';
				conclusion += '<table border="2" width="66%"><tr><td width="80%">Number of frames</td><td width="20%">' + tutorialFrames.length + '</td></tr>';
				conclusion +=  '<tr><td width="80%">Number of frames you attempted</td><td width="20%">' + currentFrame + '</td></tr><tr>';
				conclusion += '<td width="80%">Number of attempted frames you answered correctly</td><td width="20%">' + numberCorrect;
				conclusion += '</td></tr><tr><td width="80%">Percent correct score of attempted frames</td><td width="20%">' + getScore();
				conclusion += '%</td></tr></table></center></div><br><center><strong><a href="' + scriptname + '">Click here to return to the Main Menu</a></strong></center><br>';
				if (currentFrame != tutorialFrames.length) conclusion = '<p align="center">Your score fell below ' + percentStartOver + '%. Hit refresh in your browser to start over.</p>';
				for(var i = 0; i < 8; ++i) {
					document.getElementById(['frame', 'graphic', 'percentCorrect', 'frameNumber', 'tryNumber', 'userAnswer', 'evaluation', 'continueButton'][i]).innerHTML = '';
				}
				document.getElementById('finish').innerHTML = conclusion;
				return;
			}
			currentTry = 1;
			document.frm.userAnswer.value = '';
			document.frm.userAnswer.disabled = false;
			repaint('hidden', 'hidden', 'visible', 'userAnswer', '', true);
		}

		function getScore() {
			return ! isNaN(numberCorrect/currentFrame) && isFinite(numberCorrect/currentFrame) ? Math.round((numberCorrect/currentFrame) * 100) : '';
		}

		function trim(s) {
			while (s.substring(0,1) === ' ') s = s.substring(1,s.length);
			while (s.substring(s.length-1,s.length) === ' ') s = s.substring(0,s.length-1);
			return s;
		}

	</script>
</head>
<body onLoad="init()" style="font-weight:bold; padding:20px;">
<span id="frameNumber"></span><br>
<span id="tryNumber"></span><br>
<span id="percentCorrect"></span><p>
<span id="frame"></span><p>
<div id="graphic"></div><p>
<center><span id="video"></span></center><p>
<form method="post" name="frm" onSubmit="return false;">
	<div id="finish"></div>
    <span id="feedbackForm" style="display: none;">
        <span id="feedbackText" style="color:rgb(208, 61, 122);"></span><br/><br/>
        <textarea id="feedbackTextarea" name="feedbackSubmission" style="width: 400px; height:200px;"></textarea>
        <br/>
        <button id="feedbackButton" class="btn btn-primary">Save</button>
    </span>
	<span id="userAnswer" style="visibility:hidden;">
		Type your answer here:
		<input id="userAnswerField"
			   name="userAnswer"
			   onKeyPress="if (event.keyCode === 13 && trim(this.form.userAnswer.value) !== '') evaluateResponse(this.form.userAnswer.value)"
			   size="30"
			   autocomplete="off">
	</span>
	<center><span id="evaluation"></span></center>
	<span id="continueButton" style="visibility:hidden;">
		<center>
			<button style="margin-top: 10px;" class="btn btn-primary"
					name="continueButton"
				    id="continueButtonField"
				    type="button"
				    onKeyPress="if (event.keyCode === 13) doContinue()"
				    onMouseDown="doContinue()">Continue</button>
		</center>
	</span>
</form>
</body>
</html>