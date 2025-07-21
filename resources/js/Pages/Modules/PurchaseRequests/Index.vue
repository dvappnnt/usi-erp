<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import { computed, ref } from "vue";
import Swal from "sweetalert2";
import HeaderActions from "@/Components/HeaderActions.vue";
import { useColors } from "@/Composables/useColors";
import ViewOnlyModal from "@/Components/Form/ViewOnlyModal.vue";
import jsPDF from "jspdf";
import html2canvas from "html2canvas";
import { nextTick } from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

// Props from the backend
const props = defineProps({
    requests: Object,
    signatories: Array,
});

const getSignatory = (label) =>
    props.signatories.find((s) => s.label === label) || {};

const modelName = "purchase-requests";
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

// View Purchase Request
const showViewModal = ref(false);
const selectedRequest = ref({});

const viewRequest = (request) => {
    selectedRequest.value = request;
    showViewModal.value = true;
};
// Delete Purchase Request
const confirmingDelete = ref(false);

const openDeleteModal = (request) => {
    selectedRequest.value = request;
    confirmingDelete.value = true;
};

const closeDeleteModal = () => {
    confirmingDelete.value = false;
    selectedRequest.value = {};
};


const confirmDelete = () => {
    router.delete(route("purchase-requests.destroy", selectedRequest.value.id), {
        onSuccess: () => {
            toast.success("Purchase Request deleted successfully.");
            closeDeleteModal();
        },
        onError: () => {
            toast.error("Failed to delete Purchase Request.");
        },
    });
};


// Export to PDF

const exportToPDF = async (requestId) => {
    try {
        const element = document.getElementById(`request-${requestId}`);
        if (!element) {
            console.error("Request element not found.");
            return;
        }

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
        if (imgData === "data:," || imgData.length < 50) {
            console.error("Generated image data is invalid or empty.");
            return;
        }

        const pdf = new jsPDF("p", "mm", "a4");
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

        // Just capture everything as it is (logo + header)
        pdf.addImage(imgData, "JPEG", 0, 0, pdfWidth, pdfHeight);
        pdf.save(`purchase-request-${requestId}.pdf`);

        element.style.display = "none";
        element.style.position = "absolute";
        element.style.visibility = "hidden";
    } catch (error) {
        console.error("Export to PDF failed:", error);
    }
};
</script>

<template>
    <AppLayout title="Purchase Requests">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Purchase Requests
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
                                    PR Number
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Purpose
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
                            <tr
                                v-for="request in requests.data"
                                :key="request.id"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ request.pr_no }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ request.purpose }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ request.date }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap flex gap-2"
                                >
                                    <button
                                        @click="viewRequest(request)"
                                        class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700"
                                    >
                                        View
                                    </button>
                                    <Link
                                        :href="`/purchase-requests/${request.id}/edit`"
                                        class="bg-blue-500 text-white px-3 pt-2 rounded hover:bg-blue-600"
                                        >Edit</Link
                                    >
                                    <button
                                        @click="openDeleteModal(request)"
                                        class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                    <button
                                        @click="exportToPDF(request.id)"
                                        class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900"
                                    >
                                        Export
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="requests.data.length === 0">
                                <td
                                    colspan="4"
                                    class="text-center py-4 text-gray-500"
                                >
                                    No purchase requests found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-4 flex justify-center space-x-2">
                        <template
                            v-for="(link, index) in requests.links"
                            :key="index"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="px-4 py-2 border rounded text-sm"
                                :class="{
                                    'bg-gray-800 text-white': link.active,
                                    'hover:bg-gray-200': !link.active,
                                }"
                            />
                            <span
                                v-else
                                v-html="link.label"
                                class="px-4 py-2 border rounded text-gray-400 text-sm cursor-not-allowed"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <teleport to="body">
            <div
                v-for="request in requests.data"
                :key="request.id"
                :id="`request-${request.id}`"
                class="hidden-printable"
            >
                <div class="w-full p-14 bg-white">
                    <!-- Header with Logo and Centered Text -->
                    <div class="flex items-center justify-center mb-6">
                        <img
                            src="/storage/app-settings/app-logo.png"
                            alt="Logo"
                            class="w-24 h-24 mr-4 object-contain"
                        />
                        <div class="text-center">
                            <h1 class="text-2xl font-bold">
                                PANGASINAN I ELECTRIC COOPERATIVE (PANELCO I)
                            </h1>
                            <p class="italic text-base">
                                SAN JOSE, BANI, PANGASINAN
                            </p>
                            <h2 class="text-lg font-bold mt-1">
                                PURCHASE REQUEST
                            </h2>
                        </div>
                    </div>

                    <!-- Request Details -->
                    <div class="mb-6">
                        <p class="mb-2">
                            <strong>PR No.:</strong> {{ request.pr_no }}
                        </p>
                        <p class="mb-2">
                            <strong>Purpose:</strong> {{ request.purpose }}
                        </p>
                        <p><strong>Date:</strong> {{ request.date }}</p>
                    </div>

                    <!-- Table -->
                    <table
                        class="w-full border-collapse border border-gray-800 mb-12 text-center"
                    >
                        <thead>
                            <tr>
                                <th class="border border-gray-800 px-4 py-3">
                                    Item No.
                                </th>
                                <th class="border border-gray-800 px-4 py-3">
                                    Particular
                                </th>
                                <th class="border border-gray-800 px-4 py-3">
                                    Quantity
                                </th>
                                <th class="border border-gray-800 px-4 py-3">
                                    Unit
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(item, index) in request.items"
                                :key="index"
                            >
                                <td class="border border-gray-800 px-4 py-3">
                                    {{ index + 1 }}
                                </td>
                                <td class="border border-gray-800 px-4 py-3">
                                    {{ item.product?.name || "-" }}
                                </td>
                                <td class="border border-gray-800 px-4 py-3">
                                    {{ item.qty }}
                                </td>
                                <td class="border border-gray-800 px-4 py-3">
                                    {{ item.units }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Signatories -->
                    <div class="grid grid-cols-3 text-center mt-12">
                        <div>
                            <p class="text-sm mb-2">
                                {{
                                    getSignatory("Prepared By:").label ||
                                    "__________________________"
                                }}
                            </p>
                            <p class="font-bold">
                                {{
                                    getSignatory("Prepared By:").name ||
                                    "__________________________"
                                }}
                            </p>
                            <p class="text-sm">
                                {{
                                    getSignatory("Prepared By:").position ||
                                    "Position"
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm mb-2">
                                {{
                                    getSignatory("Approved By:").label ||
                                    "__________________________"
                                }}
                            </p>
                            <p class="font-bold">
                                {{
                                    getSignatory("Approved By:").name ||
                                    "__________________________"
                                }}
                            </p>
                            <p class="text-sm">
                                {{
                                    getSignatory("Approved By:").position ||
                                    "Position"
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm mb-2">
                                {{
                                    getSignatory("Received By:").label ||
                                    "__________________________"
                                }}
                            </p>
                            <p class="font-bold">
                                {{
                                    getSignatory("Received By:").name ||
                                    "__________________________"
                                }}
                            </p>
                            <p class="text-sm">
                                {{
                                    getSignatory("Received By:").position ||
                                    "Position"
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </teleport>

        <ViewOnlyModal
            v-model:show="showViewModal"
            :title="'Purchase Request Details'"
            :modelData="selectedRequest"
            :fields="[
                { label: 'PR Number', model: 'pr_no' },
                { label: 'Purpose', model: 'purpose' },
                { label: 'Date', model: 'date' },
            ]"
            @close="showViewModal = false"
        />
        <ConfirmationModal :show="confirmingDelete" @close="closeDeleteModal">
            <template #title> Delete Purchase Request </template>

            <template #content>
                Are you sure you want to delete PR
                <strong>{{ selectedRequest?.pr_no }}</strong
                >? This action cannot be undone.
            </template>

            <template #footer>
                <button
                    @click="closeDeleteModal"
                    class="px-4 py-2 bg-gray-300 rounded me-2"
                >
                    Cancel
                </button>
                <button
                    @click="confirmDelete"
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                >
                    Confirm
                </button>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<style scoped>
.hidden-printable {
    display: none;
}
</style>
