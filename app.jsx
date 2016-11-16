var Tutorial = React.createClass({

  render: function() {
    return (
      <span id="frameNumber"></span><br>
      <span id="tryNumber"></span><br>
      <span id="percentCorrect"></span><p>
      <span id="frame"></span><p>
      <span id="graphic"></span><p>
      <center><span id="video"></span></center><p>
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
    )
  }
});

React.render(<Tutorial/>, document.getElementByTagName('body'));