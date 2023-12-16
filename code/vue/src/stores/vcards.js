import axios from 'axios';
import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { useToast } from "vue-toastification";
import { useUserStore } from './user';

export const useVcardsStore = defineStore('vcards', () => {
    const toast = useToast();
    const vcards = ref([]);
    const userStore = useUserStore();
    const totalVcards = computed(() => vcards.value.length);

    async function fetchVcards() {
        try {
            const response = await axios.get('vcards');
            vcards.value = response.data;
        } catch (error) {
            toast.error("Failed to fetch vCards");
        }
    }

    async function manageVcard(vcardId, data) {
        try {
            await axios.patch(`vcards/${vcardId}`, data);
            const vcard = vcards.value.find(v => v.id === vcardId);
            if (vcard) {
                if (data.blocked !== undefined) vcard.blocked = data.blocked;
                if (data.max_debit !== undefined) vcard.max_debit = data.max_debit;
            }
            toast.success("Vcard updated successfully");
        } catch (error) {
            toast.error("Failed to update vCard");
        }
    }

    async function deleteVcard(vcardId, data) {
        try {
            if(data){
                const config = {
                    headers: {
                        "Content-Type": "application/json",
                    },
                    data: data // Pass additional data here
                };
                socket.emit('deletedUser', vcardId)
                await axios.delete(`vcards/${vcardId}`, config);
                userStore.clearUser();
            } else{
                await axios.delete(`vcards/${vcardId}`);
                vcards.value = vcards.value.filter(v => v.id !== vcardId);
            }
        } catch (error) {
            throw error;
        }
    }

    return {
        vcards,
        totalVcards,
        fetchVcards,
        manageVcard,
        deleteVcard,
    };
});
