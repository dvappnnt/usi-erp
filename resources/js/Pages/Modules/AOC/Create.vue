<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import HeaderActions from "@/Components/HeaderActions.vue";
import { useColors } from "@/Composables/useColors";
import Swal from "sweetalert2";

const props = defineProps({
    purchaseRequests: Array,
    suppliers: Array,
    products: Array,
    supplierProductDetails: Array,
});

const { buttonPrimaryBgColor } = useColors();

const form = useForm({
    purchase_request_id: "",
    items: [
        {
            product_id: "",
            supplier_id: "",
            qty: 0,
            units: "",
            unit_price: 0,
            total_price: 0,
        },
    ],
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

const addItem = () => {
    form.items.push({
        product_id: "",
        supplier_id: "",
        qty: 0,
        units: "",
        unit_price: 0,
        total_price: 0,
    });
};

const removeItem = (index) => {
    if (form.items.length > 1) form.items.splice(index, 1);
};

const filteredProducts = computed(() => {
    const pr = props.purchaseRequests.find(pr => pr.id === form.purchase_request_id);
    if (!pr || !pr.items) return [];
    const prProductIds = pr.items.map(item => item.product_id);
    return props.products.filter(p => prProductIds.includes(p.id));
});

const getSuppliersForProduct = (productId) => {
    const supplierIds = props.supplierProductDetails
        .filter(d => d.product_id === productId)
        .map(d => d.supplier_id);
    return props.suppliers.filter(s => supplierIds.includes(s.id));
};

const getPurchaseRequestItemDetails = (productId) => {
    const selectedPR = props.purchaseRequests.find(pr => pr.id === form.purchase_request_id);
    if (!selectedPR) return null;
    return selectedPR.items.find(item => item.product_id === productId);
};

watch(
    () => form.items.map(i => i.product_id),
    () => {
        form.items.forEach(item => {
            const details = getPurchaseRequestItemDetails(item.product_id);
            if (details) {
                item.qty = details.qty;
                item.units = details.units;
                calculateTotal(item);
            }
        });
    },
    { deep: true }
);

const calculateTotal = (item) => {
    const match = props.supplierProductDetails.find(
        d => d.product_id === item.product_id && d.supplier_id === item.supplier_id
    );
    if (match) {
        item.unit_price = match.price;
    }
    item.total_price = item.unit_price * (item.qty || 0);
};

const submit = () => {
    form.post(route("aocs.store"), {
        onSuccess: () => Swal.fire("Saved!", "AOC has been created.", "success"),
        onError: () => Swal.fire("Error", "Something went wrong.", "error"),
    });
};
</script>

<template>
    <AppLayout title="Create Abstract of Canvass">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create Abstract of Canvass
                </h2>
                <HeaderActions :actions="headerActions" />
            </div>
        </template>

        <div class="max-w-5xl mx-auto mt-6 bg-white p-6 rounded shadow">
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block mb-1 font-medium">Purchase Request</label>
                    <select v-model="form.purchase_request_id" class="w-full border rounded px-3 py-2">
                        <option value="">Select PR</option>
                        <option
                            v-for="pr in props.purchaseRequests"
                            :key="pr.id"
                            :value="pr.id"
                        >
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
                            <select
                                v-model="item.product_id"
                                class="w-full border rounded px-2 py-1"
                            >
                                <option value="">Select Product</option>
                                <option
                                    v-for="prod in filteredProducts"
                                    :key="prod.id"
                                    :value="prod.id"
                                >
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
                                @input="calculateTotal(item)"
                                type="number"
                                min="1"
                                class="w-full border rounded px-2 py-1 bg-gray-100"
                                readonly
                            />
                        </div>
                        <div>
                            <label class="block text-sm">Units</label>
                            <input
                                v-model="item.units"
                                type="text"
                                class="w-full border rounded px-2 py-1 bg-gray-100"
                                readonly
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

                    <button
                        type="button"
                        @click="addItem"
                        class="text-blue-600 hover:underline"
                    >
                        + Add Item
                    </button>
                </div>

                <div class="text-right">
                    <button
                        type="submit"
                        class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700"
                    >
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
