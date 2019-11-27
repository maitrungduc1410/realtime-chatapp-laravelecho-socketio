<template>
  <div class="row justify-content-center h-100">
    <div class="col-md-8 chat">
      <div class="card mb-sm-3 mb-md-0 contacts_card">
        <div class="card-header">
          <h3 class="d-flex text-white">Chatroom<span class="badge badge-success ml-2">{{ $root.rooms.length }}</span></h3>
          <div class="input-group">
              <input v-model="searchQuery" type="text" placeholder="Search..." name="" class="form-control search">
              <div class="input-group-prepend">
              <span class="input-group-text search_btn"><i class="fas fa-search"></i></span>
              </div>
          </div>
          </div>
          <div class="card-body contacts_body">
          <div class="contacts">
            <li
              v-for="room in filteredRooms"
              :key="room.id"
            >
              <router-link :to="`/rooms/${room.id}`">
                <div class="d-flex bd-highlight">
                  <div class="user_info">
                      <span>{{ room.name }}</span>
                      <p v-if="room.description">{{ room.description }}</p>
                  </div>
                </div>
              </router-link>
            </li>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data () {
    return {
      searchQuery: ''
    }
  },
  computed: {
    filteredRooms () {
      return this.$root.rooms.filter(item => item.name.toLowerCase().includes(this.searchQuery.toLowerCase()))
    }
  }
}
</script>
