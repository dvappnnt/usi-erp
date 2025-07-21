<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { useToast } from "vue-toastification";
import axios from "axios";

defineProps({
    prs: Array,
    suppliers: Array,
});

const toast = useToast();

const form = useForm({
    pr_id: "",
    supplier_id: "",
    date_sent: new Date().toISOString().split("T")[0],
    deadline: "",
    items: [{ description: "", quantity: 1, unit: "pcs" }],
});

const products = ref([]);
const prAutoSelecting = ref(false); // Prevent overlap between PR and supplier watches

watch(
    () => form.pr_id,
    async (id) => {
        if (!id) return;

        try {
            prAutoSelecting.value = true;
            const { data } = await axios.get(`/rfq/fetch-pr/${id}`);
            form.supplier_id = data.supplier.id;

            // Replace items with PR items
            form.items = data.items.map((item) => ({
                description: item.product.name,
                quantity: item.qty,
                unit: item.units,
            }));
        } catch (error) {
            toast.error("Failed to load PR details.");
        } finally {
            prAutoSelecting.value = false;
        }
    }
);

watch(
    () => form.supplier_id,
    async (id) => {
        if (!id || prAutoSelecting.value) return;

        try {
            const { data } = await axios.get(
                `/rfq/fetch-supplier-products/${id}`
            );
            products.value = data.products;
        } catch (error) {
            toast.error("Failed to load products.");
        }
    }
);

const addItem = () => {
    form.items.push({ description: "", quantity: 1, unit: "pcs" });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

// Optional: Auto-fill unit when product is selected (if product has unit info)
const autofillUnit = (index) => {
    const productName = form.items[index].description;
    const product = products.value.find((p) => p.name === productName);
    if (product && product.unit) {
        form.items[index].unit = product.unit;
    }
};

const submit = () => {
    form.post(route("rfqs.store"), {
        onSuccess: () => toast.success("RFQ created successfully."),
        onError: () => toast.error("There was an error creating the RFQ."),
    });
};
</script>

<template>
    <AppLayout title="Create RFQ">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create RFQ
            </h2>
        </template>

        <div class="max-w-5xl mx-auto py-6">
            <div class="bg-white p-6 rounded shadow">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >PR</label
                            >
                            <select
                                v-model="form.pr_id"
                                class="w-full border rounded px-3 py-2"
                            >
                                <option value="">Select PR</option>
                                <option
                                    v-for="pr in prs"
                                    :key="pr.id"
                                    :value="pr.id"
                                >
                                    {{ pr.pr_no }}
                                </option>
                            </select>
                            <div
                                v-if="form.errors.pr_id"
                                class="text-red-500 text-sm"
                            >
                                {{ form.errors.pr_id }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Supplier</label
                            >
                            <select
                                v-model="form.supplier_id"
                                class="w-full border rounded px-3 py-2"
                            >
                                <option value="">Select Supplier</option>
                                <option
                                    v-for="supplier in suppliers"
                                    :key="supplier.id"
                                    :value="supplier.id"
                                >
                                    {{ supplier.name }}
                                </option>
                            </select>
                            <div
                                v-if="form.errors.supplier_id"
                                class="text-red-500 text-sm"
                            >
                                {{ form.errors.supplier_id }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Date Sent</label
                            >
                            <input
                                v-model="form.date_sent"
                                type="date"
                                class="w-full border rounded px-3 py-2"
                            />
                            <div
                                v-if="form.errors.date_sent"
                                class="text-red-500 text-sm"
                            >
                                {{ form.errors.date_sent }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Deadline</label
                            >
                            <input
                                v-model="form.deadline"
                                type="date"
                                class="w-full border rounded px-3 py-2"
                            />
                            <div
                                v-if="form.errors.deadline"
                                class="text-red-500 text-sm"
                            >
                                {{ form.errors.deadline }}
                            </div>
                        </div>
                    </div>

                    <hr class="my-4" />

                    <div>
                        <h3 class="font-semibold mb-2">Items</h3>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2 text-left">
                                        Product Description
                                    </th>
                                    <th class="p-2 text-left w-24">Qty</th>
                                    <th class="p-2 text-left w-32">Unit</th>
                                    <th class="p-2 w-8"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(item, index) in form.items"
                                    :key="index"
                                >
                                    <td class="p-2">
                                        <select
                                            v-model="item.description"
                                            @change="autofillUnit(index)"
                                            class="w-full border rounded px-2 py-1"
                                        >
                                            <option value="">
                                                Select Product
                                            </option>
                                            <option
                                                v-for="product in products"
                                                :key="product.id"
                                                :value="product.name"
                                            >
                                                {{ product.name }}
                                            </option>
                                        </select>

                                        <div
                                            v-if="
                                                form.errors[
                                                    `items.${index}.description`
                                                ]
                                            "
                                            class="text-red-500 text-xs"
                                        >
                                            {{
                                                form.errors[
                                                    `items.${index}.description`
                                                ]
                                            }}
                                        </div>
                                    </td>

                                    <td class="p-2">
                                        <input
                                            v-model="item.quantity"
                                            type="number"
                                            min="1"
                                            class="w-full border rounded px-2 py-1"
                                        />
                                        <div
                                            v-if="
                                                form.errors[
                                                    `items.${index}.quantity`
                                                ]
                                            "
                                            class="text-red-500 text-xs"
                                        >
                                            {{
                                                form.errors[
                                                    `items.${index}.quantity`
                                                ]
                                            }}
                                        </div>
                                    </td>
                                    <td class="p-2">
                                        <input
                                            v-model="item.unit"
                                            type="text"
                                            class="w-full border rounded px-2 py-1"
                                        />
                                        <div
                                            v-if="
                                                form.errors[
                                                    `items.${index}.unit`
                                                ]
                                            "
                                            class="text-red-500 text-xs"
                                        >
                                            {{
                                                form.errors[
                                                    `items.${index}.unit`
                                                ]
                                            }}
                                        </div>
                                    </td>
                                    <td class="p-2 text-center">
                                        <button
                                            type="button"
                                            @click="removeItem(index)"
                                            v-if="form.items.length > 1"
                                            class="text-red-500 hover:text-red-700"
                                        >
                                            ✕
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <button
                            type="button"
                            @click="addItem"
                            class="mt-3 px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded"
                        >
                            + Add Item
                        </button>
                    </div>

                    <div class="pt-6">
                        <button
                            type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded"
                            :disabled="form.processing"
                        >
                            Submit RFQ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
