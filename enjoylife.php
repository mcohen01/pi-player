<?php

//////////////// Editable options ////////////////

	// this is the banner headline displayed on the Menu page
	$tutorialTitle = 'ENJOY LIFE';

	$backgroundColor = '#C4D9E1';
	$cssLink = '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/css/bootstrap.css';

	// this is the Introductory text displayed on the Menu page. HTML tags can be included
$menuIntroText = <<<EOT

<p>
<center><img src="enjoylifesmiley.jpg" alt="enjoylifesmiley" style="width:290px;height:204px"></center></>
<p>
	This program can turn your perspective about happiness and satisfaction inside out. Read slowly and carefully as you begin the road to a more joyous life.
<p>
A new kind of learning experience is about to happen to you.  You are going to learn by actively participating rather than simply reading or listening alone. 

What you experience will be a series of screen presentations. You will pass from one screen presentation to the next only by typing words that are missing.  This will require you to think a little each time and insist that you understand each point.

Your struggle to supply the missing word or words will take more effort than passively reading or listening, but this moment of struggling is when deep learning occurs.  Extensive scientific research has clearly demonstrated the superiority of this active learning method. The bottom line is that you will learn more thoroughly and recall more of the content than you would by reading text or listening to a lecture.
<p>
By applying your new knowledge, you can take big steps to enjoying life more! 
<p>
Do the tutorials in successive order because they build upon each other.
</p>

EOT;

	// the directory that will be searched for files, relative to wherever this file is located on the filesystem
	// e.g. 'tutorials/aba/oneToTen/', which would look 3 folders under the current, or
	// e.g. '../../tutorials/aba/' which would look two directories above the current and then under /tutorials/aba/
	// **** MUST START WITH ./ AND MUST END WITH /
	$frameDirectory = './enjoylife/';


	// regex used to match frame files that will be shown in the menu
	$frameFilePattern = '/txt$/';

	// directory where output files will be written, same rules as for the $frameDirectory, i.e. above or below the current dir
	// **** MUST START WITH ./ AND MUST END WITH /
	$outfileDirectory = './enjoylife/';

	// suffix appended to the name of the tutorial which will be used to generate the file for final scores
	$finalScoresFileSuffix = '_FINAL_SCORE.out';

	// students are forced to start over if their score drops below this number after the 5th frame
	$percentStartOver = 10;

	$outOfSequenceMessage = "It\'s strongly recommended that you work through these tutorials in order. ";
  $outOfSequenceMessage = $outOfSequenceMessage."Please work through the following tutorials first:";

	// change this to true to only give one try and not show the correct answer
	$isTest = false;


///////////////////////////////////////////////////////////////////////////////  END Editable options







//////////////// Do not edit below this line unless you know what you're doing ////////////

$student = $_REQUEST['Student'];
	$tutorial = $_REQUEST['frameSelection'];
	$percentStartOver = isset($_REQUEST['PercentStartOver']) ? $_REQUEST['PercentStartOver'] : $percentStartOver;
	$scriptname = basename(__FILE__, '');
	session_start();

    function readtutorialLine(&$frames, $line, &$frame) {
        global $isFrame;
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

    //// admin stats
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_REQUEST['adminStats']) {
        if ($_REQUEST['adminStats'] != '__frazier') {
            header("HTTP/1.1 500 Internal Server Error");
            exit();
        }
        function readLines($frameDirectory, $file) {
            $f = fopen($frameDirectory.$file, 'r');
            $lines = array();
            while (!feof($f)) {
                $line = fgets($f);
                array_push($lines, $line);
            }
            fclose($f);
            return $lines;
        }

        function getTutorials($frameFilePattern, $frameDirectory) {
            $dir_handle = opendir($frameDirectory);
            $tutorials = array();
            while ($file = readdir($dir_handle)) {
                if (preg_match($frameFilePattern, $file)) {
                    array_push($tutorials, $file);
                }
            }
            closedir($dir_handle);
            sort($tutorials);
            return $tutorials;
        }

        $rval = array();
        $tuts = getTutorials($frameFilePattern, $frameDirectory);
        $index = 0;
        foreach ($tuts as $tutorial) {
            $rval[$index] = array();
            $rval[$index]['tutorial'] = $tutorial;
            $lines = readLines($frameDirectory, $tutorial);
            $frames = array();
            foreach ($lines as $line) {
                $frame = readtutorialLine($frames, $line, $frame);
            }
            $rval[$index]['frames'] = $frames;
            $rval[$index]['responses'] = readLines($frameDirectory, str_replace('.txt', '.out', $tutorial));
            $index += 1;
        }

        header('Content-Type: application/json');
        echo json_encode($rval);
        exit();
    }



	if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_REQUEST['checkProgress']) {
		header('Content-Type: application/json');

		try {

			$name = urldecode($_REQUEST['name']);
			$tutorial = urldecode($_REQUEST['tutorial']);

			$capture = array();
			$match = preg_match('/[0-9]{1}/', $tutorial, $capture, 256);
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
				$match = preg_match('/[0-9]{1}/', $tut, $capture, 256);
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
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.5/nv.d3.css"/>

      <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.4/lodash.min.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.17/d3.min.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.5/nv.d3.js"></script>
      <script>
        $(document).ready(function() {

          function response(line) {
            try {
              if (line) {
                line = line.split(',');
                return {
                  student: line[0],
                  tutorial: line[1],
                  attempt: line[2],
                  frame: line[3],
                  answer: line[4],
                  response: line[5],
                  isCorrect: line[6] === 'CORRECT',
                  date: new Date(Date.parse(line[11]))
                }
              }
            } catch (ex) {
              console.log(ex);
              return {}
            }
          }


          function parseResponses(responses) {
            var rval = [];
            responses.forEach(function(line) {
              var r = response(line);
              if (rval.length) {
                rval[rval.length - 1].nextR = r;
              }
              rval.push(r);
            });
            return rval
          }


          function frameScore(r) {
            return r ? [r.frame, r.isCorrect] : [0,0];
          }


          function frameTotals(memo, tuple) {
            var containsKey = Object.keys(memo).find(function(key) {
              return key === tuple[0]
            });
            if (! containsKey) {
              memo[tuple[0]] = {
                correct: 0,
                incorrect: 0,
              }
            } else if (tuple[1]) {
              memo[tuple[0]].correct += 1
            } else {
              memo[tuple[0]].incorrect += 1
            }
            delete memo[0];
            return memo
          }


          function frameScores(frame) {
            if (frame.incorrect > 0) {
              return frame.correct / (frame.correct + frame.incorrect)
            } else if (frame.correct > 0 && frame.incorrect === 0) {
              return 1
            } else {
              return 0
            }
          }


          function completion(frame) {
            return frame.correct + frame.incorrect
          }


          var fetched = false;
          $('#Student').keyup(function() {
            if ($(this).val().match(/^__/) && $(this).val().length == 9 && !fetched) {
              fetched = true;
              $('#loading-gif').show();
              $.ajax({
                url: 'enjoylife.php?adminStats=' + $(this).val(),
                success: function (data) {
                  $('#loading-gif').hide();
                  $('#stats').show();
                  $('#stats').next().hide();
                  fetched = false;
                  var tutorials = data.map(function (tut) {
                    return {
                      name: tut.tutorial,
                      frames: tut.frames,
                      responses: parseResponses(tut.responses)
                    }
                  });
                  var links = '';
                  tutorials.forEach((t, i) => {
                    var name = t.name.replace('.txt', '').replace(/_/g, ' ');
                    links += '<a href="javascript:void(\'\');" data-index="' + i + '">' + name + '</a><br/>'
                  });
                  $('#tutorial-listing').html(links);
                  $('#tutorial-listing a').click(function (evt) {
                    var index = $(this).data('index');
                    $('#stat-frame-text').html('');
                    prepareGraph(tutorials[index]);
                  });
                },
                error: function() {
                  fetched = false;
                  $('#loading-gif').hide();
                }
              });
            }
          });


          function prepareGraph(tutorial) {
            var scores = _.mapValues(tutorial.responses.map(frameScore).reduce(frameTotals, {}), frameScores);
            var ks = Object.keys(scores);
            var _scores = ks.map(k => scores[k]);
            var avg = _scores.reduce( (acc, x) => acc + x) / ks.length;
            var mean = avg.toFixed(2) * 100;
            var sd = Math.sqrt(_scores.map(x => (x - avg) * (x - avg)).reduce( (acc, x) => acc + x) / _scores.length);
            var name = tutorial.name.replace('.txt', '').replace(/_/g, ' ');
            var times = frameThinkTime(tutorial);

            var html = '<b>' + name + '</b><br/>';
            html += 'Average Tutorial Time: ' + times.avgTutorialTime + ' min<br/>';
            html += 'Avg Frame Score: ' + mean + '%<br/>';
            html += 'SD: ' + sd.toFixed(2) * 100 + '<br/><br/>';

            html += '<table><tr><th>Students</th><th>Date</th><th align="right">Time</th></tr>';

            Object.keys(times.students).forEach(k => {
              try {
                var d = new Date(times.students[k].start).toISOString().slice(0, 10);
                var t = times.students[k].thinkTime || '-';
              } catch (e) {}
              html += '<tr><td>' + k + '</td><td>' + d + '</td><td align="right">' + t + '</td></tr>';
            });

            html += '</table>';
            for (var i = 0; i < 30; i++) html += '<br/>';
            $('#tutorial-statistics').html(html);

            var frameLength = tutorial.frames.map(f => f.frame.length);
            var meanFrameLength = frameLength.reduce( (acc, x) => acc + x) / frameLength.length;
            var scaled = frameLength.map(x => x / meanFrameLength);
            var values = [], thinkTimes = [], scaledTimes = [];

            tutorial.frames.forEach( (f, k) => {
              values.push({ x: k + 1, y: scores[k + 1] })
            });

            Object.keys(times.frames).forEach(k => {
              scaledTimes.push({x: k, y: times.frames[k] * scaled[parseInt(k)-1]});
              thinkTimes.push({x: k, y: times.frames[k]});
            });

            graph(tutorial, '#stats-svg', [{
              key: 'Frame Scores',
              bar: true,
              values: values
            }, {
              key: 'Think Time',
              color: '#f441dc',
              values: thinkTimes
            }, {
              key: 'Think Time Scaled',
              color: '#85f441',
              values: scaledTimes
            }]);
          }


          function incorrectResponses(tutorial, frameNumber) {
            var counts = {};
            var responses = tutorial.responses.filter(r => {
              return r && ! r.isCorrect && r.frame === '' + frameNumber
            }).map(r => $.trim(r.response));
            responses.forEach(r => {
              if (counts[r]) {
                counts[r]++
              } else {
                counts[r] = 1
              }
            });
            counts = Object.keys(counts).map(k => [k, counts[k]]);
            return counts.sort( (a,b) => {
              return a[1] < b[1] ? 1 : -1
            });
          }


          function frameThinkTime(tutorial) {
            var times = {}, students = {};
            var lastFrame = tutorial.frames.length - 1;
            tutorial.responses.forEach( (r, i) => {
              if (r && r.frame) {
                var student = $.trim(r.student);
                if (!times[r.frame]) {
                  times[r.frame] = []
                }
                if (!students[student]) {
                  students[student] = {name: student, adjustments: []}
                }
                if (r.frame === '1' && !students[student].end) {
                  students[student].start = r.date
                }
                if (parseInt(r.frame) === lastFrame && !students[student].end) {
                  students[student].end = r.date
                }
                var nextIndex = i + 1;
                if (nextIndex < tutorial.responses.length) {
                  var next = tutorial.responses[nextIndex];
                  if (next && r.student === next.student && parseInt(r.frame) === (parseInt(next.frame) - 1)) {
                    var thinkTime = (next.date - r.date) / 1000;
                    if (thinkTime < 300 && thinkTime > 0) {
                      times[r.frame].push(thinkTime);
                    } else {
                      students[student].adjustments.push(thinkTime);
                    }
                  }
                }
              }
            });
            var total = [];
            Object.keys(students).forEach(k => {
              var times = students[k];
              if (times.end) {
                var time = (times.end - times.start) / 1000;
                times.adjustments.forEach(t => {
                  time -= t;
                });
                var t = Math.round(time / 60);
                if (! isNaN(t) && t > 0 && t < 100) {
                  total.push(t);
                  students[k].thinkTime = t;
                }
              }
            });

            return {
              frames: _.mapValues(times, xs => xs.reduce( (acc, x) => acc + x, 0) / xs.length),
              students: students,
              avgTutorialTime: total = Math.round(total.reduce( (acc, x) => acc + x) / total.length)
            }
          }


          function graph(tutorial, selector, points) {
            d3.selectAll("svg > *").remove();
            nv.addGraph(function () {
              var chart = nv.models.linePlusBarChart();
              ccc = chart;
              //chart.reduceXTicks(tutorial.frames.length > 30);
              //chart.showControls(false);
              chart.y1Axis.tickFormat(d3.format('.0%'));
              chart.y2Axis.tickFormat(d3.format(',f'));
              chart.width(768).height(480);
              chart.focusEnable(false);
              chart.legendLeftAxisHint('');
              chart.legendRightAxisHint('');
              chart.bars.dispatch.on('elementClick', function(event) {
                try {
                  var responses = incorrectResponses(tutorial, event.data.x);
                  var html = tutorial.frames[event.index].frame;
                  html += '<p>&nbsp;<table cellpadding="4" cellspacing="6">';
                  html += '<tr><th>Correct Response:</th><th style="color:green;" nowrap="nowrap">';
                  html += tutorial.frames[event.index].answer + '</th></tr>';
                  html += '<tr><th>Incorrect Responses&nbsp;&nbsp;&nbsp;&nbsp;</th><th>Occurences</th></tr>';
                  html += '<tr><td><p></td><td><p></td></tr>';
                  responses.forEach(pair => {
                    html += '<tr><td nowrap="nowrap">' + pair[0];
                    html += '&nbsp;&nbsp;&nbsp;&nbsp;</td><td>' + pair[1] + '</td></tr>';
                  });
                  html += '</table>';
                  $('#stat-frame-text').html(html);
                } catch (e) {}
                return false
              });

              chart.tooltip.contentGenerator(function (obj) {
                var spacer = '&nbsp;&nbsp;&nbsp;';
                try {
                  return '<br/>' + spacer + '<b>Frame ' + obj.data.x + spacer +
                    '<br/>' + spacer + '&nbsp;Score: ' + obj.data.y.toFixed(2) * 100  + '%' +
                    spacer + '<br/>&nbsp;<br/></b>'
                } catch(ex) {
                  return '<br/>' + spacer + '<b>Frame ' + obj.point.x + spacer +
                          '<br/>' + spacer + '&nbsp;' + obj.series[0].key + ': ' + obj.point.y.toFixed(2) +
                          spacer + '<br/>&nbsp;<br/></b>'
                }
              });

              $(selector).each(function () { $(this)[0].setAttribute('viewBox', '0 0 860 400') });
              d3.select(selector).datum(points).style({ 'width': 768, 'height': 480 }).call(chart);
              nv.utils.windowResize(chart.update);
              return chart;
            })
          }

        });
      </script>

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
        svg {
          margin-top:-50px;
          margin-bottom:0px;
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
			function validateForm(frm) {
				if (document.getElementById('Student').value === '') {
					alert('Please fill in your name.');
					return false;
				}
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
				return true;
			}

			$(document).ready(function() {
				$('#tutorial-form').click(function() {
					var frm = document.forms[0];
					var scriptname = '<?php echo $scriptname; ?>';
					var name = $('#Student').val();
					if (name.match(/^__/)) {
						if (validateForm(frm)) {
							frm.submit();
						}
					} else {
						var tutorial = $('input[name=frameSelection]:checked').val();
						if (validateForm(frm)) {
							$.get(scriptname + '?checkProgress=1&name=' + name + '&tutorial=' + tutorial, function(data) {
								console.log(data);
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
									frm.submit();
								}
							});
						}
					}
				});
			});
		</script>

		<form name="phpMenu" method="post" onsubmit="return false;">
		<input type="hidden" name="PercentStartOver" value="<?php echo $percentStartOver; ?>">
		<input type="hidden" name="QuestionNumber" value="1">

		<strong>Step 1 - Type your full name (e.g. Mary Smith):</strong><br>
		<input type="text" id="Student" name="Student" size="30"/>
		<br/>
		<br>

    <img id="loading-gif" src="loading.gif" style="display:none;" width="60" />
    <div id="stats" style="display:none;">
      <table>
        <tr>
          <td>
            <div id="tutorial-listing"></div>
          </td>
          <td></td>
        </tr>
        <tr>
          <td style="vertical-align:top;">
            <svg id="stats-svg" viewBox="0 0 0 0" preserveAspectRatio="xMidYMid meet"></svg>
            <div id="tutorial-statistics"></div>
          </td>
          <td style="vertical-align:top;">
            <br/><br/><br/>
            <div id="stat-frame-text" style="max-width:300px; "></div>
          </td>
        </tr>
      </table>
    </div>

		<div>
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

				<div id="error-message" style="color:#c00000;"></div>
				<strong>Step 3 - Click Begin <?php echo ($isTest ? 'test' : 'tutorial'); ?>: </strong><br>
		  		<button style="margin-top: 10px;" class="btn btn-primary" id="tutorial-form">Begin <?php echo ($isTest ? 'Test' : 'Tutorial'); ?></button>

            <hr>
		</div>

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
			$userAnswer = str_replace(',', ' ', $_GET['userAnswer']);
			$userAnswer = htmlspecialchars($userAnswer);
			date_default_timezone_set('EST');
			if(!isset($_GET['outfile'])) $_GET['outfile'] = $_GET['tutorial'].'.out';
			$line = $_GET['student'].','.$_GET['tutorial'].','.$_GET['currentTry'].','.$_GET['currentFrame'].','.
					$_GET['correctAnswer'].','.$userAnswer.','.$_GET['feedback'].','.$_GET['numberOfQuestions'].','.
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
			$decoded = str_replace(' ', '_', urldecode($tutorial));
			$f = fopen($frameDirectory.$decoded, 'r');
			while (!feof($f)) {
				$line = fgets($f);
				$frame = readtutorialLine($frames, $line, $frame);
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

		function submitFeedback() {
			$.post(scriptname + '?specialfeedback=1', JSON.stringify({
				 feedback: document.getElementById('feedbackTextarea').value,
				 student: student,
				 tutorial: tutorial,
				 frame: currentFrame + 1
			 }), function(success) {
				document.getElementById('feedbackTextarea').value = '';
				document.getElementById('feedbackForm').style.display = 'none';
				document.getElementById('userAnswer').style.visibility = 'visible';
				document.getElementById('userAnswerField').focus();
			});

		};

        function parseFrameText(evalutation_text) {
            var frameText = tutorialFrames[currentFrame]['frame'];
            var matchText = tutorialFrames[currentFrame]['frame'].match(/\{\{\{.*\}\}\}/);
            frameText = matchText ? frameText.replace(matchText[0], '') : frameText;
            if (matchText && currentTry == 1 && evalutation_text === '') {
                document.getElementById('userAnswer').style.visibility = 'hidden';
                document.getElementById('feedbackForm').style.display = 'inline';
                document.getElementById('feedbackTextarea').focus();
                document.getElementById('feedbackText').innerHTML = matchText[0].replace(/\{\{\{/g, '').replace(/\}\}\}/g, '');
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
<span id="feedbackForm" style="display: none;">
	<span id="feedbackText" style="color:rgb(208, 61, 122);"></span><br/><br/>
	<textarea id="feedbackTextarea" name="feedbackSubmission" style="width: 400px; height:200px;"></textarea>
	<br/>
	<button id="feedbackButton" onclick="submitFeedback()" class="btn btn-primary">Save</button>
</span>
<form method="post" name="frm" onSubmit="return false;">
	<div id="finish"></div>

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