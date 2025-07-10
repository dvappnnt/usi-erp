<script setup>
import { watch, ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, required: true },
    fields: { type: Array, required: true }, // [{ label: 'Particular', model: 'particular' }]
    modelData: { type: Object, required: true },
});

const emit = defineEmits(['update:show', 'close']);

const close = () => {
    emit('update:show', false);
    emit('close');
};

const displayData = ref({});

watch(() => props.show, (newVal) => {
    if (newVal) {
        displayData.value = { ...props.modelData };
    }
});
</script>

<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">{{ title }}</h2>

            <div class="mt-6 space-y-4">
                <div
                    v-for="field in fields"
                    :key="field.label"
                    class="flex justify-between border-b pb-2"
                >
                    <span class="font-semibold text-gray-700">{{ field.label }}:</span>
                    <span class="text-gray-900">{{ displayData[field.model] || '-' }}</span>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button
                    type="button"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs uppercase tracking-widest text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                    @click="close"
                >
                    Close
                </button>
            </div>
        </div>
    </Modal>
</template>
