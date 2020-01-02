import confetti from 'canvas-confetti'
import $ from 'jquery'

$(document).ready(() => {
  $('body').click(e => {
    if ($(e.target).attr('class') === 'highlightText') {
      confetti({
        particleCount: 300,
        spread: 150,
        origin: {
          y: 1
        }
      })
    }
  })
})
