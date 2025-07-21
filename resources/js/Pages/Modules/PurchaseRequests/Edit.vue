<script setup>
import HeaderActions from "@/Components/HeaderActions.vue";
import { useColors } from "@/Composables/useColors";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { useToast } from "vue-toastification";

const props = defineProps({
    request: Object,
    suppliers: Array, // Supplier with supplierProductDetails and products
});

const toast = useToast();

const selectedSupplierId = ref(props.request.supplier_id);
const selectedSupplierProducts = ref([]);

const form = useForm({
    supplier_id: props.request.supplier_id,
    pr_no: props.request.pr_no,
    purpose: props.request.purpose,
    date: props.request.date,
    // prepared_by: props.request.prepared_by,
    // approved_by: props.request.approved_by,
    // received_by: props.request.received_by,
    items: props.request.items.map(item => ({
        item_no: item.item_no,
        product_id: item.product_id,
        qty: item.qty,
        units: item.units
    })),
});

// Load products when supplier changes
watch(selectedSupplierId, (newId) => {
    const supplier = props.suppliers.find(s => s.id === parseInt(newId));
    selectedSupplierProducts.value = supplier ? supplier.supplier_product_details : [];
    form.supplier_id = newId;
});

// Preload supplier products
const preloadSupplierProducts = () => {
    const supplier = props.suppliers.find(s => s.id === parseInt(form.supplier_id));
    selectedSupplierProducts.value = supplier ? supplier.supplier_product_details : [];
};
preloadSupplierProducts();

const addItem = () => {
    form.items.push({ item_no: '', product_id: '', qty: '', units: '' });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const submitForm = () => {
    form.put(`/purchase-requests/${props.request.id}`, {
        onSuccess: () => toast.success('Purchase Request updated successfully!'),
        onError: () => toast.error('Failed to update Purchase Request.'),
    });
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
    <AppLayout title="Edit Purchase Request">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                   Edit Purchase Request
                </h2>
                <HeaderActions :actions="headerActions" />
            </div>
        </template>

        <div class="flex justify-center py-10 sm:px-6 lg:px-8">
            <div class="bg-white p-8 rounded shadow w-full max-w-3xl">
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Supplier Dropdown -->
                    <div>
                        <label class="block mb-2">Supplier</label>
                        <select v-model="selectedSupplierId" class="input w-full">
                            <option value="">Select Supplier</option>
                            <option v-for="supplier in props.suppliers" :key="supplier.id" :value="supplier.id">
                                {{ supplier.name }}
                            </option>
                        </select>
                        <div v-if="form.errors.supplier_id" class="text-red-500 text-sm mt-1">{{ form.errors.supplier_id }}</div>
                    </div>

                    <div>
                        <label class="block mb-2">PR Number</label>
                        <input v-model="form.pr_no" type="text" class="input w-full" />
                        <div v-if="form.errors.pr_no" class="text-red-500 text-sm mt-1">{{ form.errors.pr_no }}</div>
                    </div>

                    <div>
                        <label class="block mb-2">Purpose</label>
                        <input v-model="form.purpose" type="text" class="input w-full" />
                    </div>

                    <div>
                        <label class="block mb-2">Date</label>
                        <input v-model="form.date" type="date" class="input w-full" />
                        <div v-if="form.errors.date" class="text-red-500 text-sm mt-1">{{ form.errors.date }}</div>
                    </div>

                    <!-- <div>
                        <label class="block mb-2">Prepared By</label>
                        <input v-model="form.prepared_by" type="text" class="input w-full" />
                    </div>

                    <div>
                        <label class="block mb-2">Approved By</label>
                        <input v-model="form.approved_by" type="text" class="input w-full" />
                    </div>

                    <div>
                        <label class="block mb-2">Received By</label>
                        <input v-model="form.received_by" type="text" class="input w-full" />
                    </div> -->

                    <div>
                        <h3 class="font-semibold mb-4">Items</h3>
                        <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-5 gap-4 mb-4">
                            <input v-model="item.item_no" type="text" placeholder="Item No." class="input" />

                            <!-- Product Dropdown -->
                            <select v-model="item.product_id" class="input w-full">
                                <option value="">Select Product</option>
                                <option v-for="product in selectedSupplierProducts" :key="product.id" :value="product.product_id">
                                    {{ product.product.name }}
                                </option>
                            </select>
                            <div v-if="form.errors[`items.${index}.product_id`]" class="text-red-500 text-sm col-span-5">
                                {{ form.errors[`items.${index}.product_id`] }}
                            </div>

                            <input v-model="item.qty" type="number" placeholder="Quantity" class="input" />
                            <input v-model="item.units" type="text" placeholder="Unit" class="input" />
                            <button type="button" @click="removeItem(index)" class="bg-red-600 text-white px-4 py-2 rounded">Remove</button>
                        </div>
                        <button type="button" @click="addItem" class="bg-green-600 text-white px-4 py-2 rounded">Add Item</button>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            Update Purchase Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.input {
    border: 1px solid #d1d5db;
    padding: 0.5rem;
    border-radius: 0.375rem;
}
</style>
