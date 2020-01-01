<template>
  <footer id="footer" class="w-100">
    <div>
        This demo using Laravel, VueJS, Laravel Echo, Redis, SocketIO.
        <a href="https://github.com/maitrungduc1410/realtime-chat/tree/full-app" target="_blank">
            (Source code)
        </a>
    </div>
    <div>
        Broadcast through Presence Channel for Shared Room and Private Channel for private message
    </div>
</footer>
</template>

<script>
export default {
}
</script>

<style>
footer {
  background: #1c5986;
  color: white;
  text-align: center;
  position: absolute;
  bottom: 0;
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
