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
      const response = await axios.get('users') //criar  API
    vcard.value = response.data.data

  } catch (error) {
    console.log(error)
  }
}

const editUser = (vcard) => {
  router.push({ name: 'User', params: { id: vcard.id } })
}

onMounted (() => {
  loadUsers()
})
</script>

<template>
  <h3 class="mt-5 mb-3">Vcard Accounts</h3>
  <hr>
  <vcard-table
    :users="vcard"
    :showId="false"
    @edit="editUser"
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

