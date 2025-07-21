<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import HeaderActions from "@/Components/HeaderActions.vue";
import { useColors } from "@/Composables/useColors";
import Swal from "sweetalert2";

const props = defineProps({
    aoc: Object,
    purchaseRequests: Array,
    suppliers: Array,
    products: Array,
    supplierProductDetails: Array,
});

const { buttonPrimaryBgColor } = useColors();

const form = useForm({
    purchase_request_id: props.aoc.purchase_request_id,
    reference_no: props.aoc.reference_no,
    items: props.aoc.items.map(item => ({
        id: item.id,
        product_id: item.product_id,
        supplier_id: item.supplier_id,
        qty: item.qty,
        units: item.units,
        unit_price: item.unit_price,
        total_price: item.total_price,
    })),
});

const headerActions = ref([
    {
        text: "Back",
        url: "/aocs",
        inertia: true,
        class: "hover:bg-opacity-90 text-white px-4 py-2 rounded",
        style: computed(() => ({
            backgroundColor: buttonPrimaryBgColor.value,
        })),
    },
]);

const getSuppliersForProduct = (productId) => {
    const supplierIds = props.supplierProductDetails
        .filter(d => d.product_id === productId)
        .map(d => d.supplier_id);
    return props.suppliers.filter(s => supplierIds.includes(s.id));
};

const calculateTotal = (item) => {
    const match = props.supplierProductDetails.find(
        d => d.product_id === item.product_id && d.supplier_id === item.supplier_id
    );
    if (match) {
        item.unit_price = match.price;
    }
    item.total_price = item.unit_price * (item.qty || 0);
};

const getPRItemDetails = (productId) => {
    const pr = props.purchaseRequests.find(pr => pr.id === form.purchase_request_id);
    return pr?.items.find(i => i.product_id === productId);
};

// Update qty/units if product_id is changed
watch(
    () => form.items.map(i => i.product_id),
    () => {
        form.items.forEach(item => {
            const prItem = getPRItemDetails(item.product_id);
            if (prItem) {
                item.qty = prItem.qty;
                item.units = prItem.units;
                calculateTotal(item);
            }
        });
    },
    { deep: true }
);

const submit = () => {
    form.put(route("aocs.update", props.aoc.id), {
        onSuccess: () => Swal.fire("Updated!", "AOC has been updated.", "success"),
        onError: () => Swal.fire("Error", "Update failed.", "error"),
    });
};
</script>

<template>
    <AppLayout title="Edit Abstract of Canvass">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Abstract of Canvass</h2>
                <HeaderActions :actions="headerActions" />
            </div>
        </template>

        <div class="max-w-5xl mx-auto mt-6 bg-white p-6 rounded shadow">
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block mb-1 font-medium">Reference No.</label>
                    <input v-model="form.reference_no" class="w-full border rounded px-3 py-2 bg-gray-100" readonly />
                </div>

                <div>
                    <label class="block mb-1 font-medium">Purchase Request</label>
                    <select disabled v-model="form.purchase_request_id" class="w-full border rounded px-3 py-2 bg-gray-100">
                        <option v-for="pr in purchaseRequests" :key="pr.id" :value="pr.id">
                            {{ pr.pr_no }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block mb-2 font-medium">Canvassed Items</label>
                    <div
                        v-for="(item, index) in form.items"
                        :key="index"
                        class="grid grid-cols-6 gap-4 mb-4"
                    >
                        <div>
                            <label class="block text-sm">Product</label>
                            <select v-model="item.product_id" disabled class="w-full border rounded px-2 py-1 bg-gray-100">
                                <option value="">Select Product</option>
                                <option v-for="prod in products" :key="prod.id" :value="prod.id">
                                    {{ prod.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm">Supplier</label>
                            <select
                                v-model="item.supplier_id"
                                @change="calculateTotal(item)"
                                class="w-full border rounded px-2 py-1"
                            >
                                <option value="">Select Supplier</option>
                                <option
                                    v-for="supp in getSuppliersForProduct(item.product_id)"
                                    :key="supp.id"
                                    :value="supp.id"
                                >
                                    {{ supp.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm">Qty</label>
                            <input
                                v-model.number="item.qty"
                                type="number"
                                readonly
                                class="w-full border rounded px-2 py-1 bg-gray-100"
                            />
                        </div>

                        <div>
                            <label class="block text-sm">Units</label>
                            <input
                                v-model="item.units"
                                type="text"
                                readonly
                                class="w-full border rounded px-2 py-1 bg-gray-100"
                            />
                        </div>

                        <div>
                            <label class="block text-sm">Unit Price</label>
                            <input
                                v-model.number="item.unit_price"
                                type="number"
                                class="w-full border rounded px-2 py-1 bg-gray-100"
                                readonly
                            />
                        </div>

                        <div>
                            <label class="block text-sm">Total</label>
                            <input
                                v-model.number="item.total_price"
                                type="number"
                                class="w-full border rounded px-2 py-1 bg-gray-100"
                                readonly
                            />
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
