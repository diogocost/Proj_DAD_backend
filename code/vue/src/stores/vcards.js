import axios from 'axios';
import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { useToast } from "vue-toastification";

export const useVcardsStore = defineStore('vcards', () => {
    const toast = useToast();
    const vcards = ref([]);

    const totalVcards = computed(() => vcards.value.length);

    async function fetchVcards() {
        try {
            const response = await axios.get('vcards');
            vcards.value = response.data;
        } catch (error) {
            console.error(error);
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
            console.error(error);
            toast.error("Failed to update vCard");
        }
    }

    async function deleteVcard(vcardId) {
        try {
            await axios.delete(`vcards/${vcardId}`);
            vcards.value = vcards.value.filter(v => v.id !== vcardId);
            toast.success("Vcard deleted successfully");
        } catch (error) {
            console.error(error);
            toast.error("Failed to delete vCard");
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
