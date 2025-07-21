<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import HeaderActions from "@/Components/HeaderActions.vue";
import { useColors } from "@/Composables/useColors";
import { nextTick } from "vue";
import html2canvas from "html2canvas";
import jsPDF from "jspdf";

const props = defineProps({
    aocs: Object,
});

const modelName = "aocs";
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
const getLowestSupplierName = (aoc) => {
    let minSupplier = null;
    let minTotal = Infinity;

    for (const supplier of aoc.unique_suppliers) {
        const total = aoc.items
            .filter((i) => i.supplier_id === supplier.id)
            .reduce((sum, item) => sum + Number(item.total_price || 0), 0);

        if (total < minTotal) {
            minTotal = total;
            minSupplier = supplier.name;
        }
    }
    return minSupplier;
};

const getLowestSupplierTotal = (aoc) => {
    let minTotal = Infinity;

    for (const supplier of aoc.unique_suppliers) {
        const total = aoc.items
            .filter((i) => i.supplier_id === supplier.id)
            .reduce((sum, item) => sum + Number(item.total_price || 0), 0);

        if (total < minTotal) minTotal = total;
    }
    return minTotal.toFixed(2);
};

const exportToPDF = async (aocId) => {
    try {
        const element = document.getElementById(`aoc-${aocId}`);
        if (!element) return;

        element.style.display = "block";
        element.style.position = "static";
        element.style.visibility = "visible";

        await nextTick();
        await new Promise((r) => setTimeout(r, 300));

        const canvas = await html2canvas(element, {
            scale: 2,
            useCORS: true,
            backgroundColor: "#fff",
        });

        const imgData = canvas.toDataURL("image/jpeg", 1.0);
        const pdf = new jsPDF("p", "mm", "a4");
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

        pdf.addImage(imgData, "JPEG", 0, 0, pdfWidth, pdfHeight);
        pdf.save(`abstract-of-canvass-${aocId}.pdf`);

        element.style.display = "none";
        element.style.position = "absolute";
        element.style.visibility = "hidden";
    } catch (err) {
        console.error("Export to PDF failed:", err);
    }
};
</script>

<template>
    <AppLayout title="Abstract of Canvass">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Abstract of Canvass
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
                                    Reference No
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Purchase Request
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Created At
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="aoc in aocs.data" :key="aoc.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ aoc.reference_no || "—" }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ aoc.purchase_request?.pr_no || "—" }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ aoc.created_at }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap flex gap-2"
                                >
                                    <Link
                                        :href="`/aocs/${aoc.id}/edit`"
                                        class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="exportToPDF(aoc.id)"
                                        class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-900"
                                    >
                                        Export
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="aocs.data.length === 0">
                                <td
                                    colspan="4"
                                    class="text-center py-4 text-gray-500"
                                >
                                    No abstract of canvass found.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-center space-x-2">
                        <template
                            v-for="(link, index) in aocs.links"
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
                v-for="aoc in aocs.data"
                :key="aoc.id"
                :id="`aoc-${aoc.id}`"
                class="hidden-printable"
            >
                <div class="w-full p-10 bg-white text-sm leading-tight">
                    <!-- Header -->
                    <div class="text-center mb-2">
                        <h1 class="text-lg font-extrabold tracking-wide">
                            PANGASINAN I ELECTRIC COOPERATIVE
                        </h1>
                        <p class="text-xs">SAN JOSE, BANI, PANGASINAN</p>
                        <p class="text-xs mb-2">panelco_one@yahoo.com</p>
                    </div>

                    <!-- REF Info -->
                    <div class="flex justify-between mb-6 text-xs">
                        <div></div>
                        <div class="text-right">
                            <p><strong>REF</strong>: {{ aoc.reference_no }}</p>
                            <p>
                                <strong>Date:</strong>
                                {{
                                    new Date(aoc.created_at).toLocaleDateString(
                                        "en-US",
                                        {
                                            year: "numeric",
                                            month: "short",
                                            day: "2-digit",
                                        }
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Title -->
                    <h2 class="text-center font-semibold text-base mb-4">
                        Abstract of Canvass
                    </h2>

                    <!-- Multi-Supplier Table -->
                    <table
                        class="w-full border border-collapse text-center text-sm"
                    >
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border px-2 py-2" rowspan="2">
                                    Qty
                                </th>
                                <th class="border px-2 py-2" rowspan="2">
                                    Unit
                                </th>
                                <th class="border px-2 py-2" rowspan="2">
                                    Particulars
                                </th>
                                <template
                                    v-for="(
                                        supplier, sIdx
                                    ) in aoc.unique_suppliers"
                                    :key="sIdx"
                                >
                                    <th class="border px-2 py-2" colspan="2">
                                        ({{ sIdx + 1 }}) {{ supplier.name }}
                                    </th>
                                </template>
                            </tr>
                            <tr class="bg-gray-100">
                                <template v-for="() in aoc.unique_suppliers">
                                    <th class="border px-2 py-2">Unit Price</th>
                                    <th class="border px-2 py-2">Total</th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, rIdx) in aoc.product_matrix"
                                :key="rIdx"
                            >
                                <td class="border px-2 py-1">{{ row.qty }}</td>
                                <td class="border px-2 py-1">{{ row.unit }}</td>
                                <td class="border px-2 py-1">
                                    {{ row.product }}
                                </td>
                                <template
                                    v-for="supplier in aoc.unique_suppliers"
                                >
                                    <td class="border px-2 py-1 text-right">
                                        {{
                                            Number(
                                                row.suppliers[supplier.id]
                                                    ?.unit_price || 0
                                            ).toFixed(2)
                                        }}
                                    </td>
                                    <td class="border px-2 py-1 text-right">
                                        {{
                                            Number(
                                                row.suppliers[supplier.id]
                                                    ?.total_price || 0
                                            ).toFixed(2)
                                        }}
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td
                                    colspan="3"
                                    class="border px-2 py-2 text-right font-semibold"
                                >
                                    Totals:
                                </td>
                                <template
                                    v-for="supplier in aoc.unique_suppliers"
                                >
                                    <td class="border px-2 py-2"></td>
                                    <td
                                        class="border px-2 py-2 text-right font-semibold"
                                    >
                                        {{
                                            aoc.items
                                                .filter(
                                                    (i) =>
                                                        i.supplier_id ===
                                                        supplier.id
                                                )
                                                .reduce(
                                                    (sum, item) =>
                                                        sum +
                                                        Number(
                                                            item.total_price ||
                                                                0
                                                        ),
                                                    0
                                                )
                                                .toFixed(2)
                                        }}
                                    </td>
                                </template>
                            </tr>
                        </tfoot>
                    </table>
                    <!-- Footer Signatures: Prepared By -->
                    <div class="mt-10 text-sm text-right p-4 mr-5">
                        <p class="mb-8">Prepared By:</p>
                        <p class="font-semibold">Eleazer C. Ramos</p>
                        <p class="text-xs">Purchaser</p>
                    </div>

                    <!-- Conclusion Section -->
                    <div
                        class="mt-10 text-center text-sm border-t border-b py-2"
                    >
                        <template v-if="aoc.unique_suppliers.length">
                            <p>
                                Conclusion:
                                {{ getLowestSupplierName(aoc)?.toUpperCase() }}
                                offered the lowest quotation
                            </p>
                            <p>
                                in the amount of Php
                                {{ getLowestSupplierTotal(aoc) }}
                            </p>
                        </template>
                    </div>

                    <!-- Footer Signatures: Noted -->
                    <div class="mt-10 text-sm p-4 ml-6">
                        <p class="mb-8">Noted:</p>
                        <p class="font-semibold">Ian Ichiro Beguas</p>
                        <p class="text-xs">ISD Manager</p>
                    </div>
                </div>
            </div>
        </teleport>
    </AppLayout>
</template>
<style scoped>
.hidden-printable {
    display: none;
}
</style>
