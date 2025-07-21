<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router, usePage, Link } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import DialogModal from "@/Components/DialogModal.vue";
import { useToast } from "vue-toastification";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import jsPDF from "jspdf";
import html2canvas from "html2canvas";
import { nextTick } from "vue";

defineProps({
    rfqs: Object,
});
const toast = useToast();

const createRFQ = () => {
    router.visit(route("rfqs.create"));
};

// Modal state
const showModal = ref(false);
const selectedRFQ = ref(null);

// Open modal and load RFQ
const openModal = (rfq) => {
    selectedRFQ.value = rfq;
    showModal.value = true;
};

// Close modal
const closeModal = () => {
    showModal.value = false;
    selectedRFQ.value = null;
};
const showDeleteModal = ref(false);
const rfqToDelete = ref(null);

const confirmDelete = (rfq) => {
    rfqToDelete.value = rfq;
    showDeleteModal.value = true;
};

const deleteRFQ = () => {
    router.delete(route("rfqs.destroy", rfqToDelete.value.id), {
        onSuccess: () => {
            toast.success("RFQ deleted successfully.");
            showDeleteModal.value = false;
        },
        onError: () => {
            toast.error("Failed to delete RFQ.");
        },
    });
};
const exportRFQToPDF = async (rfqId) => {
    const element = document.getElementById(`rfq-print-${rfqId}`);
    if (!element) return;

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
    const pdf = new jsPDF("p", "mm", "a4");
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

    pdf.addImage(imgData, "JPEG", 0, 0, pdfWidth, pdfHeight);
    pdf.save(`rfq-${rfqId}.pdf`);

    element.style.display = "none";
    element.style.position = "absolute";
    element.style.visibility = "hidden";
};
const sendEmail = (rfq) => {
    router.post(
        route("rfqs.sendEmail", rfq.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(`Email sent to ${rfq.supplier.email}`);
                rfq.status = "sent"; // update local state to disable button
            },
            onError: (errors) => {
                if (
                    errors.email ===
                    "Request for Quotation already sent to supplier."
                ) {
                    toast.warning(
                        "Request for Quotation already sent to supplier."
                    );
                } else {
                    toast.error("Failed to send email.");
                }
            },
        }
    );
};
</script>

<template>
    <AppLayout title="Request for Quotation">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Request for Quotation (RFQ)
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm rounded-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">RFQ List</h3>
                        <button
                            @click="createRFQ"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
                        >
                            + Create RFQ
                        </button>
                    </div>

                    <table class="min-w-full text-sm text-left">
                        <thead
                            class="bg-gray-100 text-xs uppercase text-gray-600"
                        >
                            <tr>
                                <th class="px-4 py-2">RFQ No</th>
                                <th class="px-4 py-2">PR No</th>
                                <th class="px-4 py-2">Supplier</th>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="rfq in rfqs.data"
                                :key="rfq.id"
                                class="border-t"
                            >
                                <td class="px-4 py-2">{{ rfq.rfq_no }}</td>
                                <td class="px-4 py-2">{{ rfq.pr?.pr_no }}</td>
                                <td class="px-4 py-2">
                                    {{ rfq.supplier?.name }}
                                </td>
                                <td class="px-4 py-2">
                                    {{
                                        new Date(
                                            rfq.created_at
                                        ).toLocaleDateString()
                                    }}
                                </td>
                                <td class="px-4 py-2 flex space-x-2">
                                    <button
                                        @click="openModal(rfq)"
                                        class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700"
                                    >
                                        View
                                    </button>
                                    <Link
                                        :href="`/rfqs/${rfq.id}/edit`"
                                        class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="confirmDelete(rfq)"
                                        class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                    <button
                                        @click="exportRFQToPDF(rfq.id)"
                                        class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-900"
                                    >
                                        Export
                                    </button>
                                    <!-- <button
                                        @click="sendEmail(rfq)"
                                        class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700"
                                    >
                                        Email
                                    </button> -->
                                    <button
                                        @click="sendEmail(rfq)"
                                        :disabled="rfq.status === 'sent'"
                                        :class="[
                                            'px-3 py-1 rounded',
                                            rfq.status === 'sent'
                                                ? 'bg-gray-400 cursor-not-allowed text-white'
                                                : 'bg-indigo-600 hover:bg-indigo-700 text-white',
                                        ]"
                                    >
                                        {{
                                            rfq.status === "sent"
                                                ? "Sent"
                                                : "Email"
                                        }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <Pagination :links="rfqs.links" />
                    </div>
                </div>
            </div>
        </div>
        <teleport to="body">
            <div
                v-for="rfq in rfqs.data"
                :id="`rfq-print-${rfq.id}`"
                :key="rfq.id"
                class="hidden-printable"
            >
                <div class="p-8 bg-white w-full text-black">
                    <!-- <div class="flex justify-between items-start mb-6"> -->
                    <div class="flex items-center justify-center mb-6">
                        <div>
                            <img
                                src="/storage/app-settings/app-logo.png"
                                alt="Logo"
                                class="w-24 h-24"
                            />
                        </div>
                        <div class="text-center">
                            <h1 class="text-xl font-bold">
                                Pangasinan I Electric Cooperative (PANELCO I)
                            </h1>
                            <p>San Jose, Bani, Pangasinan</p>
                            <p class="ml-2">
                                T/F: +6375-551-5564 | E: panelco_one@yahoo.com |
                                W: www.panelco1.com.ph
                            </p>
                        </div>
                    </div>

                    <h2 class="text-2xl font-bold text-center mb-6">
                        REQUEST FOR QUOTATION
                    </h2>

                    <div class="grid grid-cols-2 text-sm mb-4">
                        <div>
                            <p><strong>TO:</strong></p>
                            <p><strong>ATTENTION:</strong></p>
                            <p><strong>Tel. No:</strong></p>
                            <p><strong>Fax No:</strong></p>
                            <p><strong>Mobile No:</strong></p>
                            <p><strong>Email:</strong></p>
                        </div>
                        <div class="text-right">
                            <p>
                                <strong>Date:</strong>
                                {{
                                    new Date(
                                        rfq.created_at
                                    ).toLocaleDateString()
                                }}
                            </p>
                            <p><strong>RFQ No:</strong> {{ rfq.rfq_no }}</p>
                            <p><strong>PR No:</strong> {{ rfq.pr?.pr_no }}</p>
                        </div>
                    </div>

                    <p class="text-sm mb-2">
                        Kindly quote your best price for the following items:<br />
                        Please indicate brand, availability, credit terms,
                        delivery schedule and warranty.<br />
                        Reference this RFQ in your Quotation, preferably with
                        your own Quotation Reference No.
                    </p>

                    <table
                        class="w-full border border-black text-sm text-center mb-6"
                    >
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-black px-2 py-1">#</th>
                                <th class="border border-black px-2 py-1">
                                    Material Description
                                </th>
                                <th class="border border-black px-2 py-1">
                                    Qty
                                </th>
                                <th class="border border-black px-2 py-1">
                                    Unit
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in rfq.items" :key="index">
                                <td class="border border-black px-2 py-1">
                                    {{ index + 1 }}
                                </td>
                                <td class="border border-black px-2 py-1">
                                    {{ item.description }}
                                </td>
                                <td class="border border-black px-2 py-1">
                                    {{ item.quantity }}
                                </td>
                                <td class="border border-black px-2 py-1">
                                    {{ item.unit }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="text-sm">
                        <p>
                            <strong
                                >Send your quotation or clarifications
                                to:</strong
                            >
                        </p>
                        <p>Mr. Eleaser C. Ramos</p>
                        <p>Purchaser</p>
                        <p>Mobile: 09198394643</p>
                        <p>Email: ramos.eleaser@gmail.com</p>
                    </div>
                </div>
            </div>
        </teleport>

        <!-- Modal -->
        <DialogModal :show="showModal" @close="closeModal">
            <template #title>RFQ Details</template>
            <template #content>
                <div v-if="selectedRFQ">
                    <p><strong>RFQ No:</strong> {{ selectedRFQ.rfq_no }}</p>
                    <p><strong>PR No:</strong> {{ selectedRFQ.pr?.pr_no }}</p>
                    <p>
                        <strong>Supplier:</strong>
                        {{ selectedRFQ.supplier?.name }}
                    </p>
                    <p>
                        <strong>Date:</strong>
                        {{
                            new Date(
                                selectedRFQ.created_at
                            ).toLocaleDateString()
                        }}
                    </p>
                    <p><strong>Deadline:</strong> {{ selectedRFQ.deadline }}</p>
                    <hr class="my-2" />
                    <div v-if="selectedRFQ.items && selectedRFQ.items.length">
                        <h4 class="font-semibold mb-1">Products:</h4>
                        <ul class="list-disc ml-5">
                            <li
                                v-for="(item, idx) in selectedRFQ.items"
                                :key="idx"
                            >
                                {{ item.description }} - {{ item.quantity }}
                                {{ item.unit }}
                            </li>
                        </ul>
                    </div>
                </div>
            </template>
            <template #footer>
                <button
                    class="px-4 py-2 bg-gray-500 text-white rounded"
                    @click="closeModal"
                >
                    Close
                </button>
            </template>
        </DialogModal>
        <ConfirmationModal
            :show="showDeleteModal"
            @close="showDeleteModal = false"
        >
            <template #title> Delete RFQ </template>

            <template #content>
                Are you sure you want to delete
                <span class="font-semibold text-gray-900">
                    RFQ #{{ rfqToDelete?.rfq_no }} </span
                >? This action cannot be undone.
            </template>

            <template #footer>
                <button
                    @click="showDeleteModal = false"
                    class="me-3 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                >
                    Cancel
                </button>

                <button
                    @click="deleteRFQ"
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                >
                    Delete
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
