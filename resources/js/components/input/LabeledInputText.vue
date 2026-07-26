<script setup lang="ts">
import InputText from 'primevue/inputtext'
import type { HTMLAttributes, StyleValue } from 'vue'

const props = withDefaults(
    defineProps<{
        class?: HTMLAttributes['class']
        style?: StyleValue
        modelValue?: string | number | null
        size?: 'small' | 'large'
        variant?: 'outlined' | 'filled'
        fluid?: boolean
        type?: string
        id?: string
        label?: string
        inputClass?: HTMLAttributes['class']
        inputStyle?: StyleValue
        placeholder?: string
        required?: boolean
        autofocus?: boolean
        autocomplete?: string
        error?: string
    }>(),
    {
        class: '',
        style: '',
        modelValue: undefined,
        size: undefined,
        variant: 'outlined',
        fluid: false,
        type: 'text',
        id: undefined,
        label: undefined,
        inputClass: '',
        inputStyle: '',
        placeholder: undefined,
        required: false,
        autofocus: false,
        autocomplete: undefined,
        error: undefined,
    }
)

const emit = defineEmits<{
    'update:modelValue': [value: string | undefined]
}>()

const onUpdate = (value: string | undefined): void => {
    emit('update:modelValue', value)
}
</script>

<template>
    <div :class="props.class" :style="style">
        <label
            v-if="label && id"
            :for="id"
            class="block ml-1 mb-1 text-sm"
        >
            {{ label }}
        </label>

        <InputText
            :model-value="modelValue == null ? undefined : String(modelValue)"
            :size="size"
            :invalid="!!error"
            :variant="variant"
            :fluid="fluid"
            @update:model-value="onUpdate"
            :id="id"
            :type="type"
            :class="inputClass"
            :style="inputStyle"
            :placeholder="placeholder ?? label ?? ''"
            :required="required"
            :autofocus="autofocus"
            :autocomplete="autocomplete"
            :aria-describedby="`${id}-help`"
        />

        <small v-if="error && id" :id="`${id}-help`">
            {{ error }}
        </small>
    </div>
</template>
