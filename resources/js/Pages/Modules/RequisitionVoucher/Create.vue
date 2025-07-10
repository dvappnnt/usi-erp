<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";

const toast = useToast();

const form = useForm({
    purpose: "",
    date: "",
    requisitioner: "",
    requisitioner_position: "",
    recommending_approval: "",
    recommending_position: "",
    approved_by: "",
    approved_position: "",
    items: [
        { mat_code: '', particular: '', quantity: '' }
    ],
});

// Add Item
const addItem = () => {
    form.items.push({ mat_code: '', particular: '', quantity: '' });
};

// Remove Item
const removeItem = (index) => {
    form.items.splice(index, 1);
};

// Submit form
const submit = () => {
    form.post(route("requisition-vouchers.store"), {
        onSuccess: () => {
            toast.success("Requisition Voucher created successfully!");
            form.reset();
        },
        onError: () => {
            toast.error("There was an error submitting the form.");
        },
    });
};
</script>

<template>
    <AppLayout title="Create Requisition Voucher">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create Requisition Voucher
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">

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

                            <!-- Requisitioner -->
                            <div>
                                <label class="block text-gray-700">Requisitioner</label>
                                <input v-model="form.requisitioner" type="text" class="mt-1 block w-full border-gray-300 rounded" />
                                <span v-if="form.errors.requisitioner" class="text-red-600 text-sm">{{ form.errors.requisitioner }}</span>
                            </div>

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

                            <!-- Dynamic Items -->
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Items</h3>
                                <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-4 gap-4 mb-4">
                                    <input v-model="item.mat_code" type="text" placeholder="Mat Code" class="mt-1 block w-full border-gray-300 rounded" />
                                    <input v-model="item.particular" type="text" placeholder="Particular" class="mt-1 block w-full border-gray-300 rounded" />
                                    <input v-model="item.quantity" type="text" placeholder="Quantity" class="mt-1 block w-full border-gray-300 rounded" />
                                    <button type="button" @click="removeItem(index)" class="bg-red-600 text-white px-4 py-2 rounded">Remove</button>
                                </div>

                                <span v-if="form.errors['items']" class="text-red-600 text-sm">{{ form.errors['items'] }}</span>

                                <button type="button" @click="addItem" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                    Add Item
                                </button>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" :disabled="form.processing">
                                    Save
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
