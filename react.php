<html>
    <head>
        <script src="//cdnjs.cloudflare.com/ajax/libs/superagent/0.15.7/superagent.min.js"></script>
        <script src="//fb.me/react-0.12.2.min.js"></script>
        <script src="//fb.me/JSXTransformer-0.12.2.js"></script>
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/css/bootstrap.css">
        <style>
            body {
                margin: 20px;
                background-color: <?php echo $backgroundColor; ?>;
                color: #333;
            }
            * {
                font-family: 'Myrial Pro', Calibri, "Helvetica Neue", Arial, sans-serif;
                font-size: 1.1em;
            }
            input {
                margin: 10px;
                border-radius: 4px;
                border: 1px solid #aaaaaa;
            }
        </style>
        <script>
            var scriptname = '<?php echo $scriptname; ?>';
            var student = '<?php echo $student; ?>';
            var tutorial = '<?php echo $tutorial; ?>';
            var percentStartOver = '<?php echo $percentStartOver; ?>';
            var postParams = '<?php echo 'key='.$_SESSION['key'].'&frameSelection='.$tutorial; ?>';

            var tutorialFrames = '';
            var currentFrame = 0;
            var numberCorrect = 0;
            var currentTry = 1;
        </script>
        <script src="app.jsx"></script>
    </head>
    <body></body>
</html>