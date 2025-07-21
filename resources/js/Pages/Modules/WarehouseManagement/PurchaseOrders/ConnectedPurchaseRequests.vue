<script setup>
import { ref, watch } from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";

const props = defineProps({
    supplier: {
        type: Object,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
    purchaseOrderId: { // 👈 You forgot this
        type: [Number, String],
        required: true,
    },
});

const toast = useToast();

const selectedPurchaseRequests = ref([]);

// Initialize selection state
selectedPurchaseRequests.value =
    props.supplier?.purchase_requests?.map((pr) => ({
        ...pr,
        selected: false,
    })) || [];

// Watch if supplier changes to reset the selection
watch(
    () => props.supplier,
    (newSupplier) => {
        selectedPurchaseRequests.value =
            newSupplier?.purchase_requests?.map((pr) => ({
                ...pr,
                selected: false,
            })) || [];
    }
);

const toggleSelection = async (pr) => {
    if (pr.selected) {
        try {
            await axios.post(
                `/purchase-orders/${props.purchaseOrderId}/attach-purchase-request`,
                {
                    purchase_request_id: pr.id,
                }
            );

            // Add all items from this PR to the items array
            pr.items.forEach((item) => {
                props.items.push({
                    id: Date.now() + Math.random(), // Unique ID
                    product_id: item.product?.id || "",
                    variation_id: "",
                    supplier_product_detail_id: null,
                    qty: item.qty || 1,
                    free_qty: 0,
                    discount: 0,
                    price: 0,
                    total: 0,
                    notes: "",
                });
            });

            toast.success("Purchase Request attached successfully.");
        } catch (error) {
            pr.selected = false;
            toast.error("Failed to attach purchase request.");
            console.error(error);
        }
    } else {
        try {
            await axios.post(
                `/purchase-orders/${props.purchaseOrderId}/detach-purchase-request`,
                {
                    purchase_request_id: pr.id,
                }
            );

            

            // Remove all items added from this PR
            pr.items.forEach((item) => {
                const index = props.items.findIndex(
                    (i) => i.product_id === item.product?.id
                );
                if (index !== -1) {
                    props.items.splice(index, 1);
                }
            });

            toast.success("Purchase Request detached successfully.");
        } catch (error) {
            pr.selected = true;
            toast.error("Failed to detach purchase request.");
            console.error(error);
        }
    }
};
</script>

<template>
    <div v-if="supplier?.purchase_requests?.length" class="mt-8 px-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Connected Purchase Requests
        </h3>

        <div class="overflow-x-auto">
            <table
                class="min-w-full divide-y divide-gray-200 bg-white rounded-lg shadow"
            >
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2"></th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            PR No
                        </th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Date
                        </th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Product Slug
                        </th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Quantity
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template
                        v-for="pr in selectedPurchaseRequests"
                        :key="pr.id"
                    >
                        <template v-if="pr.items?.length">
                            <tr
                                v-for="(item, index) in pr.items"
                                :key="item.id"
                            >
                                <td
                                    v-if="index === 0"
                                    :rowspan="pr.items.length"
                                    class="px-4 py-2 text-center"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="pr.selected"
                                        @change="toggleSelection(pr)"
                                    />
                                </td>
                                <td
                                    class="px-4 py-2 text-gray-700"
                                    v-if="index === 0"
                                    :rowspan="pr.items.length"
                                >
                                    {{ pr.pr_no }}
                                </td>
                                <td
                                    class="px-4 py-2 text-gray-700"
                                    v-if="index === 0"
                                    :rowspan="pr.items.length"
                                >
                                    {{ pr.date }}
                                </td>
                                <td class="px-4 py-2 text-gray-700">
                                    {{ item.product?.slug || "No Slug" }}
                                </td>
                                <td class="px-4 py-2 text-gray-700">
                                    {{ item.qty }}
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td class="px-4 py-2 text-center">
                                <input
                                    type="checkbox"
                                    v-model="pr.selected"
                                    @change="toggleSelection(pr)"
                                />
                            </td>
                            <td class="px-4 py-2 text-gray-700">
                                {{ pr.pr_no }}
                            </td>
                            <td class="px-4 py-2 text-gray-700">
                                {{ pr.date }}
                            </td>
                            <td
                                class="px-4 py-2 text-gray-500 italic"
                                colspan="2"
                            >
                                No products in this purchase request.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
