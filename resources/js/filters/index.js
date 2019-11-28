import Vue from 'vue'

Vue.filter('toLocalTime', time => {
  var d = new Date(time)
  return d.toLocaleString()
})
