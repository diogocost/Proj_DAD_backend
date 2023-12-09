<script setup>
import axios from 'axios'
import { useRouter } from 'vue-router'
import { ref, computed, onMounted } from 'vue'
import VcardTable from "./VcardTable.vue"

const router = useRouter()

const vcard = ref([])

const totalUsers = computed(() => {
  return vcard.value.length
})

const loadUsers = async () => {
    try {
      const response = await axios.get('vcards') //criar  API
    vcard.value = response.data.data

  } catch (error) {
    console.log(error)
  }
}

const editUser = (vcard) => {
  router.push({ name: 'User', params: { id: vcard.id } })
}

const toggleBlock = (user) => {
  if(user.blocked == true){
    try {
      //unblock user API
    } catch {}
  } else {
    try {
      //block user API
    } catch {}
  }
}

onMounted (() => {
  loadUsers()
})
</script>

<template>
  <div class="d-flex justify-content-between">
    <div class="mx-2">
      <h3 class="mt-4">Vcard Accounts</h3>
    </div>
    <div class="mx-2 total-filtro">
      <h5 class="mt-4">Total: {{ totalUsers }}</h5>
    </div>
  </div>
  <hr>
  <div
    v-if="!onlyCurrentTasks"
    class="mb-3 d-flex justify-content-between flex-wrap"
  >
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectCompleted"
        class="form-label"
      >Filter by Phone Number:</label>
      <input
        class="form-control"
        id="selectCompleted"
        v-model="filterByPhoneNumber">
    </div>
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectProject"
        class="form-label"
      >Filter by Name:</label>
      <select
        class="form-select"
        id="selectProject"
        v-model="filterByProjectId"
      >
        <option value="-1">Any</option>
        <option :value="null">-- No project --</option>
        <option
          v-for="prj in projectsStore"
          :key="prj.id"
          :value="prj.id"
        >{{prj.name}}</option>
      </select>
    </div>
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="emailToFilter"
        class="form-label"
      >Filter by Email:</label>
      <input type="email" v-model="filterByEmail" class="form-select" name="emailToFilter">
    </div>
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectCompleted"
        class="form-label"
      >Filter by Blocked: </label>
      <p><input type="checkbox" v-model="selectedItems" class="form-check-input" value="Blocked">
      <label class="form-check-label">Only Blocked</label></p>
    </div>
  </div>
  <hr>
  <vcard-table
    :users="vcard" 
    :showId="false"
    @edit="editUser"
    @toggleBlock="toggleBlock"
  ></vcard-table>
</template>

<style scoped>
.filter-div {
  min-width: 12rem;
}
.total-filtro {
  margin-top: 2.3rem;
}
</style>

