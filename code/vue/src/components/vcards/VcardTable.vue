<script setup>
import { inject } from "vue";
import { useUserStore } from "../../stores/user.js"
import avatarNoneUrl from '@/assets/avatar-none.png'

const serverBaseUrl = inject("serverBaseUrl");
const userStore = useUserStore()

const props = defineProps({
  users: {
    type: Array,
    default: () => [],
  },
  showPhoneNumber: {
    type: Boolean,
    default: true,
  },
  showEmail: {
    type: Boolean,
    default: true,
  },
  showBlocked: {
    type: Boolean,
    default: true,
  },
  showBalance: {
    type: Boolean,
    default: true,
  },
  showDebitLimit: {
    type: Boolean,
    default: true,
  },
  showPhoto: {
    type: Boolean,
    default: true,
  },
  showEditButton: {
    type: Boolean,
    default: true,
  },
  showDeleteButton: {
    type: Boolean,
    default: true,
  },
  showAddCreditButton: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(["edit"])

const photoFullUrl = (user) => {
  return user.photo_url
    ? serverBaseUrl + "/storage/fotos/" + user.photo_url
    : avatarNoneUrl;
}

const editClick = (user) => {
  emit("edit", user)  
}

const canViewUserDetail = (userId) => {
  if (!userStore.user) {
    return false
  }
  return userStore.user.user_type == 'A' || userStore.user.id == userId
}

const toggleBlockClick = (user) => {
  emit("toggleBlock", user)
}
</script>

<template>
  <table class="table">
    <thead>
      <tr>
        <th v-if="showPhoto" class="align-middle">Photo</th>
        <th v-if="showPhoneNumber" class="align-middle">Phone Number</th>        
        <th class="align-middle">Name</th>
        <th v-if="showEmail" class="align-middle">Email</th>
        <th v-if="showBalance" class="align-middle">Balance</th>
        <th v-if="showDebitLimit" class="align-middle">Debit Limit</th>
        <th v-if="showBlocked" class="align-middle">Blocked</th>        
        
        <th class="text-end" v-if="showBlocked || showEditButton || showDeleteButton"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="user in users" :key="user.phone_number">
        <td v-if="showPhoto" class="align-middle">
          <img :src="photoFullUrl(user)" class="rounded-circle img_photo" />
        </td>
        <td v-if="showPhoneNumber" class="align-middle">{{ user.phone_number }}</td>
        <td class="align-middle">{{ user.name }}</td>
        <td v-if="showEmail" class="align-middle">{{ user.email }}</td>
        <td v-if="showDebitLimit" class="align-middle">{{ user.balance + "€"}}</td>
        <td v-if="showDebitLimit" class="align-middle">{{ user.max_debit + "€"}}</td>
        <td v-if="showBlocked" class="align-middle text-center">{{ user.blocked == true ? "Sim" : "" }}</td>
        

        <td
          class="text-end align-middle"
          v-if="showBlocked || showEditButton || showDeleteButton"
        >
          <div class="d-flex justify-content-end">
            <button
              style="min-width:90px;"
              class="btn btn-xs "
              :class="{'btn-danger': !user.blocked,}"
              @click="toggleBlockClick(user)"
              v-if="showBlocked && user.blocked == false"
            >
              Block
            </button>
            <button
              style="min-width:90px;"
              class="btn btn-xs"
              :class="{'btn-success': user.blocked,}"
              @click="toggleBlockClick(user)"
              v-if="showBlocked && user.blocked == true"
            >
              Unblock
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="editClick(task)"
              v-if="showEditButton"
            >
              <i class="bi bi-xs bi-pencil"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="editClick(task)"
              v-if="showAddCreditButton"
            >
              <i class="bi bi-xs bi-plus-circle"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="deleteClick(task)"
              v-if="showDeleteButton"
            >
              <i class="bi bi-xs bi-x-square-fill"></i>
            </button>
          </div>
        </td>

      </tr>
    </tbody>
  </table>
</template>

<style scoped>
button {
  margin-left: 3px;
  margin-right: 3px;
}

.img_photo {
  width: 3.2rem;
  height: 3.2rem;
}
</style>
