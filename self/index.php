<?php
	$student = $_REQUEST['Student'];
	$tutorial = $_REQUEST['TutorialSelection'];
	$mainMenuAddress = $_REQUEST['MainMenuAddress'];
	$percentStartOver = isset($_REQUEST['PercentStartOver']) ? $_REQUEST['PercentStartOver'] : 50;
	$scriptname = 'index.php';
	$tutorialDirectory = '';
	$outfileDirectory = '';
	session_start();

	if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_REQUEST['finalScore']) {
		$tut = str_replace('.txt', '', $tutorialDirectory.$_REQUEST['tutorial'].'_FINAL_SCORE.out');
		$f = fopen($tut, 'a');
		$stringData = $_REQUEST['student'].','.$_REQUEST['tutorial'].','.$_REQUEST['finalScore'].','.$_REQUEST['numberOfQuestions'].','.$_REQUEST['numberOfAttempts'].','.$_REQUEST['answeredCorrectly'];
		fwrite($f, $stringData."\n");
		fclose($f);
		exit();
	}


	if ($_SERVER['REQUEST_METHOD'] == 'GET' && ! $_REQUEST['TutorialSelection'] && ! $_REQUEST['correctAnswer']) {
		?>
		<html>
		<head>
		<title>Tutorial Main Menu</title>
		</head>
		<body onLoad="document.phpMenu.Student.focus();">

		<center>
            <h2>The "Initiating Self" Tutorial Main Menu</h2>
        </center>

		<P>
            The content and points made throughout this program are largely drawn
            from a paper written by B. F. Skinner entitled "The Initiating Self."
            Some sentences were drawn directly from this paper, however, the instructional
            frames are an abridged version of this paper and contain analyses and
            interpretations common in several of Skinner's books and papers.
            It was abridged and programmed into frames by Darrel E. Bostow.
        </P>

		<P>
            This series of tutorial parts is to be done sequentially.  All you do
            is read the content of a frame and type in the missing words, then tap the ENTER key.
            Each tutorial segment takes about 10 minutes and you can watch your progress by noting
            the data in the upper left hand corner of each progressive frame.
        </P>

        <P>
            If your score falls below 50%, you will be returned to the beginning of the tutorial.
        </P>

        <P>
            Follow the <strong>3 Steps</strong> below to experience tutorials.
        </P>

        <HR>

		<script>

		function check() {
			if (document.getElementById('Student').value == '') {
				alert('Please fill in your name.');
				return false;
			}
			var checked = false;
			var frm = document.forms[0];

			var isChecked = false;
			for (var i = 0; i < frm.elements.length; i++ ) {
				if (frm.elements[i].type == 'radio' && frm.elements[i].name == 'TutorialSelection') {
					if (frm.elements[i].checked) {
						isChecked = true;
					}
				}
			}
			if (! isChecked) {
				alert('Please select a tutorial.');
				return false;
			}
		}
		</script>

		<form name="phpMenu" method="post" onsubmit="return check();">
		<input type="hidden" name="MainMenuAddress" value="menu.php">
		<input type="hidden" name="PercentStartOver" value="50">
		<input type="hidden" name="QuestionNumber" value="1">

		<strong>Step 1 - Type your full name (e.g. Mary Smith):</strong><br>
		<input type="text" id="Student" name="Student" size="30">
		<p>
		<strong>Step 2 - Select a tutorial (numbers in parentheses refer to frames within tutorials):<br>
		</strong>Introduction<br>
		  <?php
			  $path = str_replace($scriptname,'',$_SERVER['SCRIPT_FILENAME']);
			  $dir_handle = @opendir($path);
			  $dirFiles = array();
			  while ($file = readdir($dir_handle)) {
			     if($file != "." && $file !=".." && substr_count($file,'.txt') == 1 && substr_count($file,'.txt.out') == 0) {
			     	$displayFile = str_replace('.txt', '', $file);
			     	array_push($dirFiles, $displayFile);
			     }
			  }
			  closedir($dir_handle);
              asort($dirFiles);

              foreach($dirFiles as $theFile) {
                $fileVal = $theFile.".txt";
                echo "<input type='radio' name='TutorialSelection' value='$fileVal'>$theFile<br/>";
              }
		 ?>
		<p>  <strong>Step 4 - Click Begin Tutorial: </strong><br>
		  <input type="submit" value="Begin Tutorial">
		<HR>
		<p>

		</body>
		</html>
		<?php
		exit();
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$_SESSION['key'] = md5(uniqid(rand(), true));
	} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		if (isset($_GET['userAnswer'])) {
			if(!isset($_GET['outfile'])) $_GET['outfile'] = $_GET['tutorial'].'.out';
			$line = $_GET['student'].','.$_GET['tutorial'].','.$_GET['currentTry'].','.$_GET['currentFrame'].','.$_GET['correctAnswer'].','.$_GET['userAnswer'].','.
					$_GET['feedback'].','.$_GET['numberOfQuestions'].','.$_GET['numberOfAttempts'].','.$_GET['answeredCorrectly'].','.$_GET['percent'].','.date("D M j G:i:s Y");

			$h = fopen(str_replace('.txt', '', $outfileDirectory.$_GET['outfile']), 'a');
		    fwrite($h, $line."\n");
		    fclose($h);
		    exit();
		} else {
			if (strcmp($_SESSION['key'], $_REQUEST['key']) != 0) exit();
			$_SESSION['key'] = null;

			$frames = array();
			function readTutorialLine($line, &$frame) {
				global $frames, $isFrame;
				$endOfFrame = 0;
				if (! is_array($frame)) $frame = array();
				if (strpos(trim($line), '@begin') === 0) $isFrame = 1;
				if (strpos(trim($line), '@end') === 0) $isFrame = 0;
				if (strpos(trim($line), '@answer') === 0) $frame['answer'] = str_replace("'", "&rsquo;", trim(substr(trim($line), 7)));
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

			$f = fopen($tutorialDirectory.$tutorial, 'r');
			while (!feof($f)) {
				$line = fgets($f);
				$frame = readTutorialLine($line, $frame);
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
	<script src="http://webscan.googlecode.com/svn-history/r80/trunk/ui/js/third-party/json2.min.js"></script>
	<script>
		var scriptname = '<?php echo $scriptname; ?>';
		var student = '<?php echo $student; ?>';
		var tutorial = '<?php echo $tutorial; ?>';
		var menu = '<?php echo $mainMenuAddress; ?>';
		var percentStartOver = <?php echo $percentStartOver; ?>;
		var postParams = '<?php echo 'key='.$_SESSION['key'].'&TutorialSelection='.$tutorial; ?>';

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

            if (!xmlhttp) {
                alert('Get a new browser!');
            } else {
                return xmlhttp;
            }
        }

		function saveAnswer() {
			var feed = 'CORRECT';
			if (tutorialFrames[currentFrame]['answer'].toUpperCase() != trim(document.frm.userAnswer.value.toUpperCase())) feed = 'IN' + feed;
			var xmlhttp = xhr();
            xmlhttp.open("GET", scriptname + '?userAnswer=' + trim(document.frm.userAnswer.value) + '&student=' + student + '&tutorial=' + tutorial + '&currentTry=' +
							currentTry + '&currentFrame=' + eval(currentFrame + 1) + '&correctAnswer=' + tutorialFrames[currentFrame]['answer'] + '&feedback=' +
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
					repaint('hidden', 'hidden', 'visible', 'userAnswer', '');
				}
			}
			xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xmlhttp.send(null);
		}

		function repaint(e, c, u, field, txt) {
			document.getElementById('evaluation').style.visibility = e;
			document.getElementById('continueButton').style.visibility = c;
			document.getElementById('userAnswer').style.visibility = u;
			document.getElementById('evaluation').innerHTML = txt;
			document.getElementById('frameNumber').innerHTML = 'Frame #: ' + eval(currentFrame + 1) + ' of ' + tutorialFrames.length;
			document.getElementById('tryNumber').innerHTML = 'Try #: ' + currentTry;
			document.getElementById('percentCorrect').innerHTML = 'Correct %: ' + getScore();
			document.getElementById('frame').innerHTML = tutorialFrames[currentFrame]['frame'];
			if (trim(tutorialFrames[currentFrame]['graphic'].toUpperCase()) == 'none'.toUpperCase()) {
				document.getElementById('graphic').innerHTML = '';
			} else {
				document.getElementById('graphic').innerHTML = '<center><img src="' + tutorialFrames[currentFrame]['graphic'] + '"/></center>';
			}
			eval('document.frm.' + field + '.focus()');
		}

		function evaluateResponse(response) {
			saveAnswer();
			if (tutorialFrames[currentFrame]['answer'].toUpperCase() == trim(response.toUpperCase())) {
				repaint('visible', 'visible', 'hidden', 'continueButton', '<br>Your answer <font color="blue">' + document.frm.userAnswer.value + '</font> is <font color="green">CORRECT</font>. <br>Press Enter or Click to Continue.');
				numberCorrect++;
			} else {
				if (currentTry < tutorialFrames[currentFrame]['tries']) {
					currentTry++;
					repaint('visible', 'hidden', 'visible', 'userAnswer', '<br>Your answer <font color="blue">' + document.frm.userAnswer.value + '</font> is <font color="red">INCORRECT</font>. Please try again.');
				} else {
					document.frm.userAnswer.disabled = true;
					repaint('visible', 'visible', 'hidden', 'continueButton', '<br>Your answer <font color="blue">' + document.frm.userAnswer.value + '</font> is <font color="red">INCORRECT</font>.<br>The correct answer is <font color="green">' + tutorialFrames[currentFrame]['answer'] + '</font>');
				}
			}
			document.frm.userAnswer.value = '';
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
			repaint('hidden', 'hidden', 'visible', 'userAnswer', '');
		}

		function getScore() {
			return ! isNaN(numberCorrect/currentFrame) && isFinite(numberCorrect/currentFrame) ? Math.round((numberCorrect/currentFrame) * 100) : '';
		}

		function trim(s) {
			while (s.substring(0,1) == ' ') s = s.substring(1,s.length);
			while (s.substring(s.length-1,s.length) == ' ') s = s.substring(0,s.length-1);
			return s;
		}

	</script>
</head>
<body onLoad="init()" style="font-weight:bold; padding:20px;">
<span id="frameNumber"></span><br>
<span id="tryNumber"></span><br>
<span id="percentCorrect"></span><p>
<span id="frame"></span><p>
<span id="graphic"></span><p>
<form method="post" name="frm" onSubmit="return false;">
	<div id="finish"></div>
	<span id="userAnswer" style="visibility:hidden;">
		Type your answer here: <input name="userAnswer" onkeypress="if (event.keyCode === 13 && trim(this.form.userAnswer.value) != '') evaluateResponse(this.form.userAnswer.value)" size="30" autocomplete="off">
	</span>
	<center><span id="evaluation"></span></center>
	<span id="continueButton" style="visibility:hidden;"><center><input name="continueButton" type="button" onkeypress="if (event.keyCode === 13) doContinue()" onMouseDown="doContinue()" value="Continue"></center></span>
</form>
</body>
</html>