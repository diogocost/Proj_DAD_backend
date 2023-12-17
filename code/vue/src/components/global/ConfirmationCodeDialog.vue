<script setup>
import { ref } from 'vue'
const props = defineProps({
    cancelBtn: {
        type: String,
        default: "Cancel",
    },
    confirmationBtn: {
        type: String,
        default: "Confirm",
    },
    data: {
        type: Object,
        required: true,
    },
    title: {
        type: String,
        default: "Insert confirmation code",
    },
    showPassword: {
        type: Boolean,
        default: false,
    },
    msg : {
        type: String,
        default: "",
    },
    errors: {
        type: Object,
        required: false,
    }
})

const emit = defineEmits(["show", "hide", "confirmed"])

const hiddenButtonToShowDialog = ref(null)
const hiddenButtonToHideDialog = ref(null)
const codeInput = ref(null)
const passwordInput = ref(null)
const data = ref(props.data)
const show = () => {
    // Show the modal:
    hiddenButtonToShowDialog.value.click()
    emit("show")
}
const hide = () => {
    // Hide the modal:
    hiddenButtonToHideDialog.value.click()
    emit("hide")
}
const clickConfirm = () => {
    if (props.showPassword) {
        data.value.password = passwordInput.value
    }
    data.value.confirmation_code = codeInput.value
    emit("confirmed")
}

// Properties/Methods that are exposed to the outside when
// the public instance of the component is retrieved via template refs
defineExpose({ show, hide })
</script>


<template>
    <!-- Button trigger to Show modal - HIDDEN -->
    <button ref="hiddenButtonToShowDialog" type="button" class="d-none" data-bs-toggle="modal"
        data-bs-target="#confirmationCodeModalId"></button>

    <!-- Modal -->
    <div class="modal fade" id="confirmationCodeModalId" tabindex="-1" aria-labelledby="confirmationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <!-- Button trigger to Hide modal - HIDDEN -->
            <button ref="hiddenButtonToHideDialog" type="button" class="d-none" data-bs-dismiss="modal"></button>

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">{{ title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" v-show="showPassword">
                    <label for="inputPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="inputPassword" required v-model="passwordInput" :class="{ 'is-invalid': errors ? errors['password'] : false }">
                    <field-error-message :errors="errors" fieldName="password"></field-error-message>
                </div>
                <div class="modal-body">
                    <label for="inputConfirmationCode" class="form-label">Confirmation Code</label>
                    <input type="password" class="form-control" id="inputConfirmationCode" required v-model="codeInput" :class="{ 'is-invalid': errors ? errors['confirmation_code'] : false }">
                    <field-error-message :errors="errors" fieldName="confirmation_code"></field-error-message>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ cancelBtn }}
                    </button>
                    <button type="button" class="btn btn-primary" @click="clickConfirm">
                        {{ confirmationBtn }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
