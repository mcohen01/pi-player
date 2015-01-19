var Nightmare = require('nightmare')
var expect = require('chai').expect

describe('tutorial menu', function() {

  this.timeout(5000)

  it('should list tutorials and start selected one', function(done) {
    new Nightmare()
        .goto('http://localhost:8000')
        .type('input#Student', 'Michael')
        .evaluate(function() {
          document.forms[0].frameSelection[3].checked = true
        })
        .click('#submit')
        .wait('#frameNumber')
        .evaluate(function () {
          return document.querySelector('#frameNumber').innerText
        }, function (frame) {
          expect(frame).to.equal('Frame #: 1 of 21')
        })
        .type('input#userAnswerField', 'foobar')
        .evaluate(function() {
          evaluateResponse('foobar')
        })
        .evaluate(function () {
          console.log(document.querySelector('#evaluation'))
          return document.querySelector('#evaluation').innerHTML
        }, function (evaluation) {
          expect(evaluation).to.match(/INCORRECT/)
          expect(evaluation).to.match(/The correct answer is/)
        })
        .evaluate(function () {
          doContinue()
        })
        .type('input#userAnswerField', 'foobar')
        .evaluate(function() {
          evaluateResponse('foobar')
        })
        .evaluate(function () {
          console.log(document.querySelector('#evaluation'))
          return document.querySelector('#evaluation').innerHTML
        }, function (evaluation) {
          expect(evaluation).to.match(/INCORRECT/)
          expect(evaluation).to.match(/Please try again/)
        })
        .evaluate(function () {
          doContinue()
        })
        .type('input#userAnswerField', 'parsimonious')
        .evaluate(function() {
          evaluateResponse('parsimonious')
        })
        .evaluate(function () {
          console.log(document.querySelector('#evaluation'))
          return document.querySelector('#evaluation').innerHTML
        }, function (evaluation) {
          expect(evaluation).to.match(/Your answer.*parsimonious.*is.*green.*CORRECT/)
          expect(evaluation).to.match(/Press Enter or Click to Continue/)
        })
        .run(done)
  })

})