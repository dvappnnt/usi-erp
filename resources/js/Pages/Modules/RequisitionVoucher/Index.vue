<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import { computed, ref } from "vue";
import Swal from "sweetalert2";
import HeaderActions from "@/Components/HeaderActions.vue";
import { useColors } from "@/Composables/useColors";
import jsPDF from "jspdf";
import html2canvas from "html2canvas";
import { nextTick } from "vue";
import ViewOnlyModal from "@/Components/Form/ViewOnlyModal.vue";

defineProps({
    vouchers: Array,
});

const modelName = "requisition-vouchers";
const toast = useToast();
const { buttonPrimaryBgColor } = useColors();

const headerActions = ref([
    {
        text: "Create",
        url: `/${modelName}/create`,
        inertia: true,
        class: "hover:bg-opacity-90 text-white px-4 py-2 rounded",
        style: computed(() => ({
            backgroundColor: buttonPrimaryBgColor.value,
        })),
    },
]);

const deleteVoucher = (id) => {
    Swal.fire({
        title: "Are you sure?",
        text: "This action cannot be undone!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            useForm().delete(route("requisition-vouchers.destroy", id), {
                onSuccess: () => toast.success("Voucher deleted successfully!"),
                onError: () => toast.error("Failed to delete voucher."),
            });
        }
    });
};
const showViewModal = ref(false);
const selectedVoucher = ref(null);

// Add this function
const viewVoucher = (voucher) => {
    selectedVoucher.value = voucher;
    showViewModal.value = true;
};

const exportToPDF = async (voucherId) => {
    try {
        const element = document.getElementById(`voucher-${voucherId}`);
        if (!element) return console.error("Voucher element not found.");

        element.style.display = "block";
        element.style.position = "static";
        element.style.visibility = "visible";

        await nextTick();
        await new Promise((resolve) => setTimeout(resolve, 300));

        const canvas = await html2canvas(element, {
            scale: 2,
            useCORS: true,
            backgroundColor: "#ffffff",
        });

        const imgData = canvas.toDataURL("image/jpeg", 1.0);
        if (imgData === "data:," || imgData.length < 50)
            return console.error("Generated image data is invalid or empty.");

        const pdf = new jsPDF("p", "mm", "a4");
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

        pdf.addImage(imgData, "JPEG", 0, 0, pdfWidth, pdfHeight);
        pdf.save(`voucher-${voucherId}.pdf`);

        element.style.display = "none";
        element.style.position = "absolute";
        element.style.visibility = "hidden";
    } catch (error) {
        console.error("Export to PDF failed:", error);
    }
};
</script>

<template>
    <AppLayout title="Requisition Vouchers">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Requisition Vouchers
                </h2>
                <HeaderActions :actions="headerActions" />
            </div>
        </template>

        <div class="max-w-12xl">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Requisitioner
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Date
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="voucher in vouchers" :key="voucher.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ voucher.requisitioner }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ voucher.date }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap flex gap-2"
                                >
                                    <button
                                        @click="viewVoucher(voucher)"
                                        class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700"
                                    >
                                        View
                                    </button>
                                    <Link
                                        :href="`/requisition-vouchers/${voucher.id}/edit`"
                                        class="bg-blue-500 text-white px-3 pt-2 rounded hover:bg-blue-600"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteVoucher(voucher.id)"
                                        class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                    <button
                                        @click="exportToPDF(voucher.id)"
                                        class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900"
                                    >
                                        Export
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="vouchers.length === 0">
                                <td
                                    colspan="3"
                                    class="text-center py-4 text-gray-500"
                                >
                                    No vouchers found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Printable Vouchers -->
        <teleport to="body">
            <div
                v-for="voucher in vouchers"
                :key="voucher.id"
                :id="`voucher-${voucher.id}`"
                class="hidden-printable"
            >
                <div class="w-full p-12 bg-white">
                    <div class="text-center mb-6">
                        <h1 class="text-3xl font-bold">
                            PANGASINAN I ELECTRIC COOPERATIVE
                        </h1>
                        <p>San Jose, Bani, Pangasinan</p>
                        <p class="mb-4">Bolinao Satellite Office</p>
                        <h2 class="font-semibold mb-4">REQUISITION VOUCHER</h2>
                    </div>

                    <div class="mb-6">
                        <p class="mb-2">
                            <strong>Purpose:</strong> {{ voucher.purpose }}
                        </p>
                        <p><strong>Date:</strong> {{ voucher.date }}</p>
                    </div>

                    <table
                        class="w-full border-collapse border border-gray-800 mb-12 text-center"
                    >
                        <thead>
                            <tr>
                                <th class="border border-gray-800 px-4 py-3">
                                    MAT. CODE
                                </th>
                                <th class="border border-gray-800 px-4 py-3">
                                    PARTICULAR
                                </th>
                                <th class="border border-gray-800 px-4 py-3">
                                    QUANTITY
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(item, index) in voucher.items"
                                :key="index"
                            >
                                <td class="border border-gray-800 px-4 py-3">
                                    {{ item.mat_code || "" }}
                                </td>
                                <td class="border border-gray-800 px-4 py-3">
                                    {{ item.particular }}
                                </td>
                                <td class="border border-gray-800 px-4 py-3">
                                    {{ item.quantity }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex justify-around text-center mt-12">
                        <div class="w-1/3">
                            <p class="font-semibold underline">
                                {{ voucher.requisitioner }}
                            </p>
                            <p>Requisitioner</p>
                        </div>
                        <div class="w-1/3">
                            <p class="font-semibold underline">
                                {{ voucher.recommending_approval }}
                            </p>
                            <p>Recommending Approval</p>
                        </div>
                        <div class="w-1/3">
                            <p class="font-semibold underline">
                                {{ voucher.approved_by }}
                            </p>
                            <p>Approved By</p>
                        </div>
                    </div>
                </div>
            </div>
        </teleport>
        
        <ViewOnlyModal
            v-model:show="showViewModal"
            :title="'Requisition Voucher Details'"
            :modelData="selectedVoucher"
            :fields="[
                { label: 'Purpose', model: 'purpose' },
                { label: 'Date', model: 'date' },
                { label: 'Requisitioner', model: 'requisitioner' },
                {
                    label: 'Requisitioner Position',
                    model: 'requisitioner_position',
                },
                {
                    label: 'Recommending Approval',
                    model: 'recommending_approval',
                },
                {
                    label: 'Recommending Position',
                    model: 'recommending_position',
                },
                { label: 'Approved By', model: 'approved_by' },
                { label: 'Approved Position', model: 'approved_position' },
            ]"
            @close="showViewModal = false"
        />
    </AppLayout>
</template>

<style scoped>
.hidden-printable {
    display: none;
}
</style>
