<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";

// Props from the controller
const props = defineProps({
    voucher: Object,
});

const toast = useToast();

const form = useForm({
    purpose: props.voucher.purpose,
    date: props.voucher.date,
    requisitioner: props.voucher.requisitioner,
    requisitioner_position: props.voucher.requisitioner_position,
    recommending_approval: props.voucher.recommending_approval,
    recommending_position: props.voucher.recommending_position,
    approved_by: props.voucher.approved_by,
    approved_position: props.voucher.approved_position,
    items: props.voucher.items.map(item => ({
        mat_code: item.mat_code,
        particular: item.particular,
        quantity: item.quantity,
    })),
});

const addItem = () => {
    form.items.push({ mat_code: '', particular: '', quantity: '' });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const submit = () => {
    form.put(route("requisition-vouchers.update", props.voucher.id), {
        onSuccess: () => {
            toast.success("Requisition Voucher updated successfully!");
        },
        onError: () => {
            toast.error("There was an error updating the voucher.");
        },
    });
};
</script>

<template>
    <AppLayout title="Edit Requisition Voucher">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Requisition Voucher
                </h2>
            </div>
        </template>

        <div class="max-w-12xl">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Purpose -->
                        <div>
                            <label class="block text-gray-700">Purpose</label>
                            <textarea v-model="form.purpose" class="mt-1 block w-full border-gray-300 rounded"></textarea>
                            <span v-if="form.errors.purpose" class="text-red-600 text-sm">{{ form.errors.purpose }}</span>
                        </div>

                        <!-- Date -->
                        <div>
                            <label class="block text-gray-700">Date</label>
                            <input v-model="form.date" type="date" class="mt-1 block w-full border-gray-300 rounded" />
                            <span v-if="form.errors.date" class="text-red-600 text-sm">{{ form.errors.date }}</span>
                        </div>

                        <!-- Requisitioner -->
                        <div>
                            <label class="block text-gray-700">Requisitioner</label>
                            <input v-model="form.requisitioner" type="text" class="mt-1 block w-full border-gray-300 rounded" />
                            <span v-if="form.errors.requisitioner" class="text-red-600 text-sm">{{ form.errors.requisitioner }}</span>
                        </div>

                        <!-- Requisitioner Position -->
                        <div>
                            <label class="block text-gray-700">Requisitioner Position</label>
                            <input v-model="form.requisitioner_position" type="text" class="mt-1 block w-full border-gray-300 rounded" />
                            <span v-if="form.errors.requisitioner_position" class="text-red-600 text-sm">{{ form.errors.requisitioner_position }}</span>
                        </div>

                        <!-- Recommending Approval -->
                        <div>
                            <label class="block text-gray-700">Recommending Approval</label>
                            <input v-model="form.recommending_approval" type="text" class="mt-1 block w-full border-gray-300 rounded" />
                            <span v-if="form.errors.recommending_approval" class="text-red-600 text-sm">{{ form.errors.recommending_approval }}</span>
                        </div>

                        <!-- Recommending Position -->
                        <div>
                            <label class="block text-gray-700">Recommending Position</label>
                            <input v-model="form.recommending_position" type="text" class="mt-1 block w-full border-gray-300 rounded" />
                            <span v-if="form.errors.recommending_position" class="text-red-600 text-sm">{{ form.errors.recommending_position }}</span>
                        </div>

                        <!-- Approved By -->
                        <div>
                            <label class="block text-gray-700">Approved By</label>
                            <input v-model="form.approved_by" type="text" class="mt-1 block w-full border-gray-300 rounded" />
                            <span v-if="form.errors.approved_by" class="text-red-600 text-sm">{{ form.errors.approved_by }}</span>
                        </div>

                        <!-- Approved Position -->
                        <div>
                            <label class="block text-gray-700">Approved Position</label>
                            <input v-model="form.approved_position" type="text" class="mt-1 block w-full border-gray-300 rounded" />
                            <span v-if="form.errors.approved_position" class="text-red-600 text-sm">{{ form.errors.approved_position }}</span>
                        </div>

                        <!-- Items -->
                        <div>
                            <h3 class="font-semibold mb-4">Items</h3>
                            <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-3 gap-4 mb-4">
                                <input v-model="item.mat_code" type="text" placeholder="Mat Code" class="input" />
                                <input v-model="item.particular" type="text" placeholder="Particular" class="input" />
                                <input v-model="item.quantity" type="text" placeholder="Quantity" class="input" />
                                <button type="button" @click="removeItem(index)" class="bg-red-600 text-white px-4 py-2 rounded">Remove</button>
                                <div v-if="form.errors[`items.${index}.particular`]" class="text-red-600 text-sm col-span-4">
                                    {{ form.errors[`items.${index}.particular`] }}
                                </div>
                            </div>
                            <button type="button" @click="addItem" class="bg-green-600 text-white px-4 py-2 rounded">Add Item</button>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" :disabled="form.processing">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
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
