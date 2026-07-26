<script setup lang="ts">
import IftaLabel from 'primevue/iftalabel'
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
        inputId?: string
        inputName?: string
        inputClass?: HTMLAttributes['class']
        inputStyle?: StyleValue
        placeholder?: string
        required?: boolean
        autofocus?: boolean
        autocomplete?: string
        error?: string
        min?: string | number
        max?: string | number
        step?: string | number
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
        inputId: undefined,
        inputName: undefined,
        inputClass: undefined,
        inputStyle: undefined,
        placeholder: undefined,
        required: false,
        autofocus: false,
        autocomplete: undefined,
        error: undefined,
        min: undefined,
        max: undefined,
        step: undefined,
    }
)

const emit = defineEmits<{
    'update:modelValue': [value: string | number | null | undefined]
}>()

const onUpdate = (value: string | undefined): void => {
    if (props.type === 'number') {
        emit(
            'update:modelValue',
            value === undefined || value === '' ? null : Number(value)
        )

        return
    }

    emit('update:modelValue', value)
}
</script>

<template>
    <div :class="props.class" :id="id" :style="style">
        <IftaLabel>
            <InputText
                :aria-describedby="`${id}-help`"
                :autocomplete="autocomplete"
                :autofocus="autofocus"
                :class="inputClass"
                :fluid="fluid"
                :id="inputId"
                :invalid="Boolean(error)"
                :max="max"
                :min="min"
                :model-value="
                    modelValue == null ? undefined : String(modelValue)
                "
                :name="inputName"
                :placeholder="placeholder ?? label ?? ''"
                :required="required"
                :size="size"
                :style="inputStyle"
                :step="step"
                :type="type"
                :variant="variant"
                @update:model-value="onUpdate"
            />

            <label v-if="label && inputId" :for="inputId">
                {{ label }}
            </label>

            <small
                v-if="error && inputId"
                :id="`${inputId}-help`"
                class="text-red-500"
            >
                {{ error }}
            </small>
        </IftaLabel>
    </div>
</template>
