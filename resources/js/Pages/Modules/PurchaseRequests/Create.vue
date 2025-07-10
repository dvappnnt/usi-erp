<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import Swal from "sweetalert2";
import { computed } from "vue";
import HeaderActions from "@/Components/HeaderActions.vue";
import { useColors } from "@/Composables/useColors";

const props = defineProps({
    errors: Object,
    suppliers: Array,
});

const selectedSupplierId = ref(null);
const selectedSupplierProducts = ref([]);

const form = useForm({
    supplier_id: "",
    // pr_no: "",
    purpose: "",
    date: "",
    // prepared_by: "",
    // approved_by: "",
    // received_by: "",
    items: [{ item_no: "", product_id: "", qty: "", units: "" }],
});

// Auto-load products based on selected supplier
watch(selectedSupplierId, (newId) => {
    const supplier = props.suppliers.find((s) => s.id === parseInt(newId));
    selectedSupplierProducts.value = supplier
        ? supplier.supplier_product_details
        : [];
    form.supplier_id = newId;
});

// Add new item row
const addItem = () => {
    form.items.push({ item_no: "", product_id: "", qty: "", units: "" });
};

// Remove item row
const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    } else {
        Swal.fire("At least one item is required.", "", "warning");
    }
};

// Submit form
const submit = () => {
    form.post(route("purchase-requests.store"));
};
const modelName = "purchase-requests";
const { buttonPrimaryBgColor } = useColors();
const headerActions = ref([
    {
        text: "Back",
        url: `/${modelName}`,
        inertia: true,
        class: "hover:bg-opacity-90 text-white px-4 py-2 rounded",
        style: computed(() => ({
            backgroundColor: buttonPrimaryBgColor.value,
        })),
    },
]);
</script>

<template>
    <AppLayout title="Create Purchase Request">
         <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                   Create Purchase Request
                </h2>
                <HeaderActions :actions="headerActions" />
            </div>
        </template>

        <div class="max-w-4xl mx-auto mt-8 bg-white p-6 rounded shadow">
            <form @submit.prevent="submit">
                <!-- Supplier Dropdown -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Supplier</label>
                    <select v-model="selectedSupplierId" class="w-full border rounded px-3 py-2">
                        <option value="">Select Supplier</option>
                        <option v-for="supplier in props.suppliers" :key="supplier.id" :value="supplier.id">
                            {{ supplier.name }}
                        </option>
                    </select>
                    <span v-if="props.errors.supplier_id" class="text-red-500 text-sm">{{ props.errors.supplier_id }}</span>
                </div>

                <!-- Display Supplier Products -->
                <!-- <div v-if="selectedSupplierProducts.length" class="mb-4">
                    <h3 class="text-lg font-semibold mb-2">Supplier Products</h3>
                    <ul class="list-disc ml-6">
                        <li v-for="product in selectedSupplierProducts" :key="product.id">
                            {{ product.product.name }} - ₱{{ product.price }}
                        </li>
                    </ul>
                </div> -->

                <!-- PR Number -->
                <!-- <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">PR Number</label>
                    <input v-model="form.pr_no" type="text" class="w-full border rounded px-3 py-2" />
                    <span v-if="props.errors.pr_no" class="text-red-500 text-sm">{{ props.errors.pr_no }}</span>
                </div> -->

                <!-- Purpose -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Purpose</label>
                    <input v-model="form.purpose" type="text" class="w-full border rounded px-3 py-2" />
                    <span v-if="props.errors.purpose" class="text-red-500 text-sm">{{ props.errors.purpose }}</span>
                </div>

                <!-- Date -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Date</label>
                    <input v-model="form.date" type="date" class="w-full border rounded px-3 py-2" />
                    <span v-if="props.errors.date" class="text-red-500 text-sm">{{ props.errors.date }}</span>
                </div>

              
                <!-- <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Prepared By</label>
                    <input v-model="form.prepared_by" type="text" class="w-full border rounded px-3 py-2" />
                </div>

               
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Approved By</label>
                    <input v-model="form.approved_by" type="text" class="w-full border rounded px-3 py-2" />
                </div>

               
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Received By</label>
                    <input v-model="form.received_by" type="text" class="w-full border rounded px-3 py-2" />
                </div> -->

                <!-- Dynamic Items -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-3">Items</label>

                    <div v-for="(item, index) in form.items" :key="index" class="mb-4 border p-4 rounded">
                        <div class="grid grid-cols-4 gap-4 mb-2">
                            <div>
                                <label class="block text-gray-700 text-sm mb-1">Item No.</label>
                                <input v-model="item.item_no" type="number" class="w-full border rounded px-2 py-1" />
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm mb-1">Product</label>
                                <select v-model="item.product_id" class="w-full border rounded px-2 py-1">
                                    <option value="">Select Product</option>
                                    <option v-for="product in selectedSupplierProducts" :key="product.id" :value="product.product.id">
                                        {{ product.product.name }} - ₱{{ product.price }}
                                    </option>
                                </select>
                                <span v-if="props.errors[`items.${index}.product_id`]" class="text-red-500 text-sm">
                                    {{ props.errors[`items.${index}.product_id`] }}
                                </span>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm mb-1">Quantity</label>
                                <input v-model="item.qty" type="number" class="w-full border rounded px-2 py-1" />
                                <span v-if="props.errors[`items.${index}.qty`]" class="text-red-500 text-sm">
                                    {{ props.errors[`items.${index}.qty`] }}
                                </span>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm mb-1">Unit</label>
                                <input v-model="item.units" type="text" class="w-full border rounded px-2 py-1" />
                                <span v-if="props.errors[`items.${index}.units`]" class="text-red-500 text-sm">
                                    {{ props.errors[`items.${index}.units`] }}
                                </span>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="button" @click="removeItem(index)" class="text-red-600 text-sm hover:underline">
                                Remove Item
                            </button>
                        </div>
                    </div>

                    <button type="button" @click="addItem" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Add Item
                    </button>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
