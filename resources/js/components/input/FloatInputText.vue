<script setup lang="ts">
import FloatLabel from 'primevue/floatlabel'
import InputText from 'primevue/inputtext'
import type { HTMLAttributes } from 'vue'

const props = withDefaults(
    defineProps<{
        modelValue?: string | number | null
        type?: string
        label: string
        class?: HTMLAttributes['class']
        inputClass?: HTMLAttributes['class']
        id: string
        error?: string
        required?: boolean
        autofocus?: boolean
        autocomplete?: string
    }>(),
    {
        modelValue: undefined,
        type: 'text',
        class: '',
        inputClass: '',
        error: undefined,
        required: false,
        autofocus: false,
        autocomplete: '',
    }
)

const emit = defineEmits<{
    'update:modelValue': [value: string | undefined]
}>()
</script>

<template>
    <div :class="props.class">
        <FloatLabel>
            <InputText
                :id="id"
                :type="type"
                :class="inputClass"
                :model-value="
                    modelValue == null ? undefined : String(modelValue)
                "
                @update:model-value="
                    (value) => emit('update:modelValue', value)
                "
                :aria-describedby="`${id}-help`"
                :required="required"
                :autofocus="autofocus"
                :autocomplete="autocomplete"
            />

            <label :for="id">
                {{ label }}
            </label>
        </FloatLabel>

        <small v-show="error" :id="`${id}-help`">
            {{ error }}
        </small>
    </div>
</template>
