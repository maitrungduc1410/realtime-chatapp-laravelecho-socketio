<template>
  <footer id="footer" class="w-100">
    <div>
      This demo uses <a href="https://laravel.com" target="_blank">Laravel</a>, <a href="https://vuejs.org"
        target="_blank">VueJS</a>, <a href="https://github.com/laravel/echo" target="_blank">Laravel Echo</a>, <a
        href="https://reverb.laravel.com" target="_blank">Reverb</a>.
      <a href="https://github.com/maitrungduc1410/realtime-chatapp-laravelecho-socketio" target="_blank">
        (Source code)
      </a>
    </div>
    <div>
      Broadcast is done through Presence Channel for Shared Room and Private Channel for private message
    </div>
  </footer>
</template>

<style>
footer {
  background: #1c5986;
  color: white;
  text-align: center;
  position: fixed;
  bottom: 0;
  font-size: 13px;
  font-weight: 300;
  z-index: 1;
}

@media only screen and (max-width: 768px) {
  footer {
    position: initial;
  }
}

footer a {
  color: #00ffe9;
}
</style>
