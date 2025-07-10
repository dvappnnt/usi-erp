<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";

const toast = useToast();

const form = useForm({
    module: "",
    name: "",
    label: "",
    position: "",
});

const submit = () => {
    form.post(route("signatories.store"), {
        onSuccess: () => {
            toast.success("Signatory created successfully!");
        },
        onError: () => {
            toast.error("Failed to create signatory.");
        },
    });
};
</script>

<template>
    <AppLayout title="Create Signatory">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Signatory
            </h2>
        </template>

        <div class="max-w-4xl mx-auto mt-6 bg-white p-6 rounded-lg shadow">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Module</label>
                    <input type="text" v-model="form.module" class="w-full border-gray-300 rounded shadow-sm" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" v-model="form.name" class="w-full border-gray-300 rounded shadow-sm" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                    <input type="text" v-model="form.label" class="w-full border-gray-300 rounded shadow-sm" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                    <input type="text" v-model="form.position" class="w-full border-gray-300 rounded shadow-sm" />
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
