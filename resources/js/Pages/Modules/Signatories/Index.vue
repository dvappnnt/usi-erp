<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { useToast } from "vue-toastification";
import HeaderActions from "@/Components/HeaderActions.vue";
import Swal from "sweetalert2";
import { useColors } from "@/Composables/useColors";

defineProps({
    signatories: Object,
});

const modelName = "signatories";
const toast = useToast();
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

const deleteSignatory = (id) => {
    Swal.fire({
        title: "Are you sure?",
        text: "This action cannot be undone!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            useForm().delete(route("signatories.destroy", id), {
                onSuccess: () => toast.success("Signatory deleted successfully!"),
                onError: () => toast.error("Failed to delete signatory."),
            });
        }
    });
};
</script>

<template>
    <AppLayout title="Signatories">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Signatories
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Label</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="signatory in signatories.data" :key="signatory.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ signatory.module }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ signatory.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ signatory.label }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ signatory.position }}</td>
                                <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                                    <Link :href="`/signatories/${signatory.id}/edit`" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                        Edit
                                    </Link>
                                    <button @click="deleteSignatory(signatory.id)" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="signatories.data.length === 0">
                                <td colspan="5" class="text-center py-4 text-gray-500">
                                    No signatories found.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-center space-x-2">
                        <template v-for="(link, index) in signatories.links" :key="index">
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
    </AppLayout>
</template>
