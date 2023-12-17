<script setup>
import { ref, watch, computed, inject } from "vue";
import avatarNoneUrl from '@/assets/avatar-none.png'
import { useUserStore } from '../../stores/user.js'

const serverBaseUrl = inject("serverBaseUrl");
const userStore = useUserStore()

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    required: false,
  },
});

const emit = defineEmits(["save", "cancel", "dismiss"]);

const editingUser = ref(props.user)

const inputPhotoFile = ref(null)
const editingImageAsBase64 = ref(null)
const deletePhotoOnTheServer = ref(false)

watch(
  () => props.user,
  (newUser) => {
    editingUser.value = newUser
  },
  { immediate: true }
)

const photoFullUrl = computed(() => {
  if (deletePhotoOnTheServer.value) {
    return avatarNoneUrl
  }
  if (editingImageAsBase64.value) {
    return editingImageAsBase64.value
  } else {
    return editingUser.value.photo_url
      ? serverBaseUrl + "/storage/fotos/" + editingUser.value.photo_url
      : avatarNoneUrl
  }
})

const userTitle = computed(() => {
  if (!editingUser.value) {
    return ''
  }
  if (editingUser.value.user_type == 'A') {
    return 'Admin #' + editingUser.value.id
  } else if (editingUser.value.user_type == 'V') {
    return 'Vcard #' + editingUser.value.id
  }
})

const save = () => {
  const userToSave = editingUser.value
  if(deletePhotoOnTheServer.value) {
    userToSave.base64ImagePhoto = ''
  }else if(editingImageAsBase64.value) {
    userToSave.base64ImagePhoto = editingImageAsBase64.value
  }
  emit("save", userToSave);
}

const cancel = () => {
  emit("cancel", editingUser.value);
}

const dismiss = () => {
  emit("dismiss", editingUser.value);
}

// When changing the photo file, change the editingImageAsBase64.value
const changePhotoFile = () => {
  try {
    const file = inputPhotoFile.value.files[0]
    if (!file) {
      editingImageAsBase64.value = null
    } else {
      const reader = new FileReader()
      reader.addEventListener(
        'load',
        () => {
          // convert image file to base64 string
          editingImageAsBase64.value = reader.result
          deletePhotoOnTheServer.value = false
        },
        false,
      )
      if (file) {
        reader.readAsDataURL(file)
      }
    }
  } catch (error) {
    editingImageAsBase64.value = null
  }
}

const resetToOriginalPhoto = () => {
  deletePhotoOnTheServer.value = false
  inputPhotoFile.value.value = ''
  changePhotoFile()
}

const cleanPhoto = () => {
  deletePhotoOnTheServer.value = true
}

</script>

<template>
  <form class="row g-3 needs-validation" novalidate @submit.prevent="save">
    <h3 class="mt-5 mb-3">{{ userTitle }}</h3>
    <hr />
    <div class="d-flex flex-wrap justify-content-between">
      <div class="w-75 pe-4">
        <div class="mb-3">
          <label for="inputName" class="form-label">Name</label>
          <input type="text" class="form-control" :class="{ 'is-invalid': errors ? errors['name'] : false }"
            id="inputName" placeholder="User Name" required v-model="editingUser.name" />
          <field-error-message :errors="errors" fieldName="name"></field-error-message>
        </div>

        <div class="mb-3 px-1">
          <label for="inputEmail" class="form-label">Email</label>
          <input type="email" class="form-control" :class="{ 'is-invalid': errors ? errors['email'] : false }"
            id="inputEmail" placeholder="Email" required v-model="editingUser.email" />
          <field-error-message :errors="errors" fieldName="email"></field-error-message>
        </div>
      </div>
      <div class="w-25">
        <div class="d-flex flex-column">
          <label class="form-label">Photo</label>
          <div class="form-control text-center">
            <img type="file" :src="photoFullUrl" class="w-100" />
          </div>
          <div class="mt-3 d-flex justify-content-between flex-wrap" v-if="editingUser.user_type == 'V'">
            <label for="inputPhoto" class="btn btn-dark flex-grow-1 mx-1">Carregar</label>
            <button class="btn btn-secondary flex-grow-1 mx-1" @click.prevent="resetToOriginalPhoto"
              v-if="editingUser.photo_url">Repor</button>
            <button class="btn btn-danger flex-grow-1 mx-1" @click.prevent="cleanPhoto"
              v-show="editingUser.photo_url || editingImageAsBase64">Apagar</button>
          </div>
          <div>
            <field-error-message :errors="errors" fieldName="base64ImagePhoto"></field-error-message>
          </div>
        </div>
      </div>
    </div>
    <hr />
    <div class="mt-2 d-flex justify-content-between">
      <div>
      <router-link class="btn btn-primary px-5 mx-2" :class="{ active: $route.name === 'ChangePassword' }"
        :to="{ name: 'ChangePassword' }"> <!--  -->
        Change password
      </router-link>
      <router-link class="btn btn-primary px-5 mx-2" :class="{ active: $route.name === 'ChangeConfirmationCode' }" v-if="editingUser.user_type == 'V'"
        :to="{ name: 'ChangeConfirmationCode' }"> <!--  -->
        Change Confirmation Code
      </router-link>
      </div>
      <div>
        <button type="button" class="btn btn-primary px-5 mx-2" @click="save">Save</button>
        <button type="button" class="btn btn-light px-5 mx-2" @click="cancel">Cancel</button>
        <button type="button" class="btn btn-danger px-5 mx-2" @click="dismiss">Delete</button>
      </div>
    </div>
  </form>
  <input type="file" style="visibility:hidden;" id="inputPhoto" ref="inputPhotoFile" @change="changePhotoFile" />
</template>

<style scoped>
.total_hours {
  width: 26rem;
}
</style>
